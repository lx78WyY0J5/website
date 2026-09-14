<?php

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

?>