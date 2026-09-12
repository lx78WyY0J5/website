<?php
// Check if the user is logged in, otherwise redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: /login");
    echo "<script>window.location.href = '/login';</script>";
    exit;
}

//load requiered modules
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/loadEnv.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/PDO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/logging.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/src/php/rate_limiter.php';

// Rate limiting (per user + per IP)
$clientIp = getClientIdentifier();
$userId = $_SESSION["id"];
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

// Define variables and initialize with empty values
$target_dir = $userUploadDir;
$target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
$can_upload = true;
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$maxFileSize = 5 * 1024 * 1024; // 5MB

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["profile_picture"]["tmp_name"]);
    if($check !== false) {
        // echo "<p>File is an image - " . $check["mime"] . ".</p>";
        $can_upload = true;
    } else {
        echo "<p>File is not an image.</p>";
        $can_upload = false;
    }
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "<p>Sorry, file already exists.</p>";
    $can_upload = false;
}

// Check file size
if ($_FILES["profile_picture"]["size"] > $maxFileSize) {
    echo "<p>Sorry, your file is too large (max 5MB).</p>";
    $can_upload = false;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "<p>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</p>";
    $can_upload = false;
}

// Check if $can_upload is set to 0 by an error
if ($can_upload == false) {
    echo "<p>Sorry, your file was not uploaded.</p>";
} else {
    // Upload the file
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        // Save filename and file path to database
        $filePath = $target_file;
        $sql = "INSERT INTO user_pictures (user_id, filename, file_path) VALUES (:user_id, :filename, :file_path)";
        if($stmt = $pdo->prepare($sql)){
            $stmt->bindParam(":user_id", $param_user_id, PDO::PARAM_INT);
            $stmt->bindParam(":filename", $param_filename, PDO::PARAM_STR);
            $stmt->bindParam(":file_path", $param_file_path, PDO::PARAM_STR);

            $param_user_id = $userId;
            $param_filename = basename($_FILES["profile_picture"]["name"]);
            $param_file_path = $filePath;

            if($stmt->execute()){
                logSecurityEvent('profile_picture_uploaded', ['user_id' => $userId, 'filename' => basename($_FILES["profile_picture"]["name"])]);
                echo "<p>Votre photo de profil a été téléchargée avec succès !</p>";
            } else {
                echo "<p>Oups! Une erreur s'est produite. Veuillez réessayer plus tard</p>";
            }
        }
        unset($stmt);
    } else {
        echo "<p>Désolé, une erreur s'est produite lors du téléchargement de votre fichier.</p>";
    }
}

// Function to get latest profile picture of a user
// Returns the file path string or false if no picture exists
function getLatestProfilePictureOfUser($pdo, $userId) {
    $sql = "SELECT file_path FROM user_pictures WHERE user_id = :user_id ORDER BY uploaded_at DESC LIMIT 1";
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":user_id", $user_id_param, PDO::PARAM_INT);
        $user_id_param = $userId;
        
        if($stmt->execute()){
            if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                return $row["file_path"];
            }
        }
        unset($stmt);
    }
    return false;
}

// Function to get all profile pictures of a user
// Returns an array of file path strings
function getAllProfilesPicturesOfUser($pdo, $userId) {
    $sql = "SELECT file_path FROM user_pictures WHERE user_id = :user_id ORDER BY uploaded_at DESC";
    $result = [];
    
    if($stmt = $pdo->prepare($sql)){
        $stmt->bindParam(":user_id", $user_id_param, PDO::PARAM_INT);
        $user_id_param = $userId;
        
        if($stmt->execute()){
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $result[] = $row["file_path"];
            }
        }
        unset($stmt);
    }
    return $result;
}

// Display the user's profile picture (if exists)
$latestPicture = getLatestProfilePictureOfUser($pdo, $userId);
if($latestPicture && file_exists($latestPicture)){
    echo "<div>";
    echo "<img src='{$latestPicture}' alt='Profile picture'>";
    echo "</div>";
}

// Display all pictures if multiple exist
$allPictures = getAllProfilesPicturesOfUser($pdo, $userId);
if(count($allPictures) > 1){
    echo "<div class='gallery'>";
    foreach($allPictures as $fp){
        if(file_exists($fp)){
            echo "<div><img src='{$fp}' alt='Profile picture'></div>";
        }
    }
    echo "</div>";
}

// Close connection
unset($pdo);
?>