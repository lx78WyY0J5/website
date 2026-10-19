<?php
//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/password_validation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/discord_oauth.php';

// Define variables and initialize with empty values
$username = $password = $confirm_password = "";
$can_register = true;
$action = "create";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Rate limiting (per IP + per user)
    checkRateLimitByIP($pdo, 'discord_register', 5, 15);
    checkRateLimitByUser($pdo, (int)$_SESSION["id"], 'discord_register', 5, 15);

    // Validate CSRF token (commun aux deux actions)
    if(!validateCsrfToken()) {
        echo "<p>Erreur de sécurité : token CSRF invalide</p>";
        $can_register = false;
    }

    // Action du formulaire : créer un compte classique, OU lier un compte classique existant
    if(isset($_POST["action"]) && is_string($_POST["action"]) && $_POST["action"] === "link"){
        $action = "link";
    }

    // =====================================================================
    // LIER un compte classique DÉJÀ EXISTANT à ce compte Discord
    // =====================================================================
    if($action === "link"){

        $link_username = "";
        $link_password = "";
        if (isset($_POST["username"]) && is_string($_POST["username"])) {
            $link_username = trim($_POST["username"]);
        }
        if (isset($_POST["password"]) && is_string($_POST["password"])) {
            $link_password = $_POST["password"];
        }

        if(empty($link_username) || empty($link_password)){
            echo "<p>Veuillez saisir le username et le mot de passe du compte classique</p>";
            $can_register = false;
        }

        // Relecture du compte ciblé (id, hash, état de liaison)
        if($can_register === true){
            $target = false;
            try {
                $stmt = $pdo->prepare("SELECT id, password, discord_id FROM users WHERE username = :username");
                $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
                $param_username = $link_username;

                if($stmt->execute()){
                    $target = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                else {
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                    logSecurityEvent('discord_register_link_error', ['user_id' => $_SESSION['id']]);
                    $can_register = false;
                }

                // Close statement
                unset($stmt);
            }
            catch(PDOException $e) {
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                logSecurityEvent('discord_register_link_error', ['user_id' => $_SESSION['id']]);
                $can_register = false;
            }
        }

        // Preuve de possession : message générique (comme login) pour éviter
        // l'énumération (compte absent, password NULL, ou hash différent)
        if($can_register === true){
            if($target === false || empty($target["password"])){
                echo "<p>Username ou mot de passe du compte classique incorrect</p>";
                logSecurityEvent('discord_register_link_denied', ['user_id' => $_SESSION['id']]);
                $can_register = false;
            }
        }
        if($can_register === true){
            if(password_verify($link_password, $target["password"])){
                // possession prouvée
            }
            else{
                echo "<p>Username ou mot de passe du compte classique incorrect</p>";
                logSecurityEvent('discord_register_link_denied', ['user_id' => $_SESSION['id']]);
                $can_register = false;
            }
        }

        // Un compte classique ne peut être lié qu'à UN seul Discord
        if($can_register === true){
            if(!empty($target["discord_id"])){
                echo "<p>Ce compte classique est déjà lié à un compte Discord</p>";
                logSecurityEvent('discord_register_link_conflict', ['user_id' => $_SESSION['id'], 'target_id' => (int)$target['id']]);
                $can_register = false;
            }
        }

        if($can_register === true){
            $targetId = (int)$target["id"];
            $ghostId = (int)$_SESSION["id"];

            // La ligne fantôme Discord-only de cette session porte le discord_id
            // (UNIQUE) : la supprimer AVANT d'écrire sur la ligne ciblée
            try {
                discordDeleteUser($pdo, $ghostId);
                logSecurityEvent('discord_register_ghost_merged', ["ghost_id" => $ghostId, "user_id" => $targetId]);
            }
            catch(PDOException $e) {
                logSecurityEvent('discord_register_ghost_delete_error', ["ghost_id" => $ghostId]);
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                $can_register = false;
            }

            if($can_register === true){
                // TOCTOU traité par la garde SQL : AND discord_id IS NULL.
                // S'il échoue, la ligne fantôme est déjà supprimée -> la session
                // restante n'est plus valide (discord_id lié à un autre compte)
                try {
                    if(discordLinkToUnlinkedUser($pdo, $targetId, $_SESSION['discord_id']) === 1){
                        $can_register = true;
                    }
                    else {
                        logSecurityEvent('discord_register_link_conflict', ['target_id' => $targetId]);
                        echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
                        $can_register = false;
                    }
                }
                catch(PDOException $e) {
                    logSecurityEvent('discord_register_link_conflict', ['target_id' => $targetId]);
                    echo "<p>Ce compte Discord est déjà lié à un autre compte</p>";
                    $can_register = false;
                }

                if($can_register === true){
                    // Basculer la session sur le compte classique revendiqué
                    $_SESSION["id"] = $targetId;
                    $_SESSION["username"] = $link_username;

                    // même logique is_admin que login.php
                    $adminUsername = getenv("ADMIN_USERNAME");
                    if (empty($adminUsername) || !isset($adminUsername)) {
                        $adminUsername = "administrator";
                    }
                    if(strcasecmp($link_username, $adminUsername) === 0){
                        $_SESSION["is_admin"] = true;
                    }
                    else{
                        $_SESSION["is_admin"] = false;
                    }

                    // Admin Discord : le discord_id de la session peut être configuré admin (.env)
                    if (discordIsAdminId($_SESSION["discord_id"])) {
                        $_SESSION["is_admin"] = true;
                    }

                    echo "<script>document.forms[0].style = 'display: none;';</script>";
                    logSecurityEvent('discord_register_link_success', ["user_id" => $targetId, "ghost_id" => $ghostId, "username" => $link_username]);
                    header("location: /");
                    echo "<script>window.location.href = '/';</script>";
                }
                else{
                    // La ligne fantôme est déjà supprimée : terminer la session
                    $_SESSION["loggedin"] = false;
                    unset($_SESSION["id"]);
                    unset($_SESSION["username"]);
                    unset($_SESSION["discord_id"]);
                    unset($_SESSION["discord_username"]);
                    unset($_SESSION["is_admin"]);
                    header("location: /login");
                    echo "<script>window.location.href = '/login';</script>";
                }
            }
        }
    }

    // =====================================================================
    // CRÉER un compte classique (rattacher username/password à la ligne
    // Discord-only de la session)
    // =====================================================================
    else{

        // Validate username (same rules as register.php)
        if(!isset($_POST["username"]) || !is_string($_POST["username"]) || empty(trim($_POST["username"]))){
            echo "<p>Veuillez saisir un nom d'utilisateur</p>";
            $can_register = false;
        }
        else {
            $username = trim($_POST["username"]);

            if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)){
                echo "<p>Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores</p>";
                $can_register = false;
            }

            if(strlen($username) < 6){
                echo "<p>Le nom d'utilisateur doit comporter au moins 6 caractères</p>";
                $can_register = false;
            }

            if(strlen($username) > 24){
                echo "<p>Le nom d'utilisateur doit comporter moins de 24 caractères</p>";
                $can_register = false;
            }

            $adminUsername = getenv("ADMIN_USERNAME");
            if (empty($adminUsername) || !isset($adminUsername)) {
                $adminUsername = "administrator";
            }
            if(strcasecmp($username, $adminUsername) === 0){
                echo "<p>Ce nom d'utilisateur est réservé</p>";
                $can_register = false;
            }
        }

        // Validate password using shared validation
        if (isset($_POST["password"]) && is_string($_POST["password"])) {
            $password = $_POST["password"];
        }
        if (isset($_POST["confirm_password"]) && is_string($_POST["confirm_password"])) {
            $confirm_password = $_POST["confirm_password"];
        }
        if (!checkPassword($password, $confirm_password)) {
            $can_register = false;
        }

        // Check that the username is still available if no error before
        if($can_register === true){
            // Prepare a select statement
            try {
                $sql = "SELECT id FROM users WHERE username = :username";

                if($stmt = $pdo->prepare($sql)){
                    // Bind variables to the prepared statement as parameters
                    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

                    // Set parameters
                    $param_username = $username;

                    // Attempt to execute the prepared statement
                    if($stmt->execute()){
                        if($stmt->rowCount() == 1){
                            echo "<p>Ce nom d'utilisateur est déjà pris</p>";
                            $can_register = false;
                        }
                    }
                    else {
                        echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                        $can_register = false;
                    }

                    // Close statement
                    unset($stmt);
                }
            }
            catch(PDOException $e) {
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                $can_register = false;
            }
        }

        // Update the existing Discord-only row (NO new INSERT)
        if($can_register === true){
            $can_register = false;
            try {
                // Prepare an update statement. La condition WHERE garantit que la
                // ligne est bien une ligne Discord-only attendue
                $sql = "UPDATE users SET username = :username, password = :password
                    WHERE id = :id AND discord_id IS NOT NULL AND username IS NULL";

                if($stmt = $pdo->prepare($sql)){
                    // Bind variables to the prepared statement as parameters
                    $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
                    $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $param_id, PDO::PARAM_INT);

                    // Set parameters
                    $param_username = $username;
                    $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                    $param_id = (int)$_SESSION["id"];

                    // Attempt to execute the prepared statement
                    if($stmt->execute()){
                        // Vérifier rowCount() === 1 : la ligne a déjà un username,
                        // ou l'id de session est invalide
                        if($stmt->rowCount() === 1){
                            $can_register = true;
                        }
                        else {
                            echo "<p>Impossible de rattacher le compte : la ligne a déjà un compte classique, ou la session n'est plus valide</p>";
                            logSecurityEvent('discord_register_failed', ['user_id' => $_SESSION['id']]);
                        }
                    }
                    else {
                        echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                    }

                    // Close statement
                    unset($stmt);
                }
            }
            catch(PDOException $e) {
                if ($e->getCode() === "23000") {
                    // TOCTOU sur l'unicité : un autre utilisateur a pris ce username
                    // entre le SELECT et l'UPDATE -> même message que register.php
                    echo "<p>Ce nom d'utilisateur est déjà pris</p>";
                }
                else {
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
                }
                logSecurityEvent('discord_register_error', ['user_id' => $_SESSION['id']]);
            }

            // Check input errors before updating the session
            if($can_register === true){
                // Store data in session variables
                $_SESSION["username"] = $username;

                // même logique is_admin que login.php (username non vide ici)
                $adminUsername = getenv("ADMIN_USERNAME");
                if (empty($adminUsername) || !isset($adminUsername)) {
                    $adminUsername = "administrator";
                }
                if(strcasecmp($username, $adminUsername) === 0){
                    $_SESSION["is_admin"] = true;
                }
                else {
                    $_SESSION["is_admin"] = false;
                }

                // Admin Discord : le discord_id de la session peut être configuré admin (.env)
                if (discordIsAdminId($_SESSION["discord_id"])) {
                    $_SESSION["is_admin"] = true;
                }

                echo "<script>document.forms[0].style = 'display: none;';</script>";
                logSecurityEvent('discord_register_success', ['user_id' => $_SESSION['id'], 'username' => $username]);
                header("location: /");
                echo "<script>window.location.href = '/';</script>";
            }
        }
    }

    // Close connection
    unset($pdo);
}
?>
