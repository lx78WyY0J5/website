<?php
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login");
    echo "<script>window.location.href = '/login';</script>";
    exit;
}

// Load required modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';

// Rate limiting (per user + per IP)
$clientIp = getClientIdentifier();
// Validate userId is numeric
$userId = intval($_SESSION["id"]);
if (!checkRateLimit($pdo, $clientIp, 'profile_picture_upload', 10, 60) || !checkRateLimit($pdo, "user_{$userId}", 'profile_picture_upload', 10, 60)) {
    logSecurityEvent('rate_limit_exceeded', ['endpoint' => 'profile_picture_upload', 'ip' => $clientIp, 'user_id' => $userId]);
    rateLimitExceededResponse('profile_picture_upload');
}

// Define upload directory with user ID subfolder
$uploadsDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/profile_pictures/';
$userUploadDir = $uploadsDir . $userId . '/';

// Create folders recursively if it doesn't exist
if (!is_dir($userUploadDir)) {
    mkdir($userUploadDir, 0755, true);
}

// Initialize variables
$target_dir = $userUploadDir;
$maxFileSize = 5 * 1024 * 1024; // 5MB
$can_upload = true;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if file was uploaded without errors
    if (!isset($_FILES["profile_picture"]) || $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_OK) {
        echo "<p>Aucun fichier n'a été téléchargé ou une erreur s'est produite.</p>";
        $can_upload = false;
    }

    // Vérifier l'extension du fichier
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_extensions, true)) {
        echo "<p>Seules les images JPG, PNG et GIF sont autorisées.</p>";
        $can_upload = false;
    }

    // Vérifier le type de fichier via le contenu
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = $_FILES["profile_picture"]["type"];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $_FILES["profile_picture"]["tmp_name"]);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_types)) {
        echo "<p>Seules les images JPG, PNG et GIF sont autorisées.</p>";
        $can_upload = false;
    }

    // Vérifier la taille du fichier
    if ($_FILES["profile_picture"]["size"] > $maxFileSize) {
        echo "<p>Votre fichier est trop volumineux (maximum 5 Mo).</p>";
        $can_upload = false;
    }

    // Vérifier si le fichier est une image réelle using getimagesize
    if ($can_upload) {
        $image_type = getimagesize($_FILES["profile_picture"]["tmp_name"]);
        if ($image_type === false || !in_array($image_type[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF])) {
            echo "<p>Le fichier n'est pas une image valide.</p>";
            $can_upload = false;
        }
    }

    // Vérifier si le fichier existe uniquement si les validations précédentes ont réussi
    if ($can_upload) {
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        if (file_exists($target_file)) {
            echo "<p>Désolé, un fichier portant ce nom existe déjà.</p>";
            $can_upload = false;
        }
    }

    // Traitement du formulaire soumis
    if (isset($_POST["upload_profile_picture"]) && $can_upload) {
        // Upload the file
        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
            // Save filename and file path to database
            $relativePath = 'uploads/profile_pictures/' . $userId . '/' . basename($_FILES["profile_picture"]["name"]);
            $filePath = $uploadsDir . $userId . '/' . basename($_FILES["profile_picture"]["name"]);
            $sql = "INSERT INTO user_pictures (user_id, filename, file_path) VALUES (:user_id, :filename, :file_path)";
            if ($stmt = $pdo->prepare($sql)) {
                $stmt->bindParam(":user_id", $param_user_id, PDO::PARAM_INT);
                $stmt->bindParam(":filename", $param_filename, PDO::PARAM_STR);
                $stmt->bindParam(":file_path", $param_file_path, PDO::PARAM_STR);

                $param_user_id = $userId;
                $param_filename = basename($_FILES["profile_picture"]["name"]);
                $param_file_path = $relativePath;

                if ($stmt->execute()) {
                    logSecurityEvent('profile_picture_uploaded', ['user_id' => $userId, 'filename' => basename($_FILES["profile_picture"]["name"])]);
                    echo "<p>Votre photo de profil a été téléchargée avec succès !</p>";
                } else {
                    echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard.</p>";
                }
            }
        }
        unset($stmt);
    }
    else {
        echo "<p>Désolé, une erreur s'est produite lors du téléchargement de votre fichier.</p>";
    }
}

// Function to get latest profile picture of a user
function getLatestProfilePictureOfUser($pdo, $userId) {
    $sql = "SELECT file_path FROM user_pictures WHERE user_id = :user_id ORDER BY uploaded_at DESC LIMIT 1";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":user_id", $user_id_param, PDO::PARAM_INT);
        $user_id_param = $userId;
        if ($stmt->execute()) {
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row["file_path"];
            }
        }
        unset($stmt);
    }
    return false;
}

// Function to get all profile pictures of a user
function getAllProfilesPicturesOfUser($pdo, $userId) {
    $sql = "SELECT file_path FROM user_pictures WHERE user_id = :user_id ORDER BY uploaded_at DESC";
    $result = [];
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":user_id", $user_id_param, PDO::PARAM_INT);
        $user_id_param = $userId;
        if ($stmt->execute()) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row["file_path"];
            }
        }
        unset($stmt);
    }
    return $result;
}

// Display the user's profile picture (if exists)
$latestPicture = getLatestProfilePictureOfUser($pdo, $userId);
if ($latestPicture && file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . dirname($latestPicture) . '/' . basename($latestPicture))) {
    echo "<div>";
    echo "<img src='/" . ltrim($latestPicture, '/') . "' alt='Profile picture'>";
    echo "</div>";
}
else {
    $name = $_SERVER['DOCUMENT_ROOT'] . '/' . dirname($latestPicture) . '/' . basename($latestPicture);
    echo "<p>error !!!!</p><br><p>" . $name . "</p>";
}

// Display all pictures if multiple exist
$allPictures = getAllProfilesPicturesOfUser($pdo, $userId);
if (count($allPictures) > 1) {
    echo "<div class='gallery'>";
    foreach ($allPictures as $fp) {
        if (file_exists($fp)) {
            echo "<div><img src='{$fp}' alt='Profile picture'></div>";
        }
    }
    echo "</div>";
}

// Close connection
unset($pdo);
?>