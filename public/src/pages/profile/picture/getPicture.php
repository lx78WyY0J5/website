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

// Function to get the Discord profile picture of a user (fichier téléchargé
// lors du login Discord, nommé par discord_id). Teste les formats dans l'ordre :
// png, jpg, jpeg, gif, webp. Retourne le chemin ("uploads/...") ou false
function getDiscordProfilePictureOfUser($pdo, $userId) {
    // Le discord_id du compte : sans compte Discord lié, pas d'image
    $sql = "SELECT discord_id FROM users WHERE id = :user_id";
    $discord_id = null;
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":user_id", $user_id_param, PDO::PARAM_INT);
        $user_id_param = $userId;
        if ($stmt->execute()) {
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $discord_id = $row["discord_id"];
            }
        }
        unset($stmt);
    }

    if (empty($discord_id)) {
        return false;
    }

    // Chercher le fichier existant, format par format
    foreach (["png", "jpg", "jpeg", "gif", "webp"] as $extension) {
        $path = "uploads/profile_pictures/discord_pictures/" . $discord_id . "." . $extension;
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/" . $path)) {
            return $path;
        }
    }

    return false;
}

?>