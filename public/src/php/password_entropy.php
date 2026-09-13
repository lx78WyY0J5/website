<?php

function calculateEntropy(string $password) {
    $L = strlen($password);
    $N = 0;

    if (preg_match('/[a-z]/', $password)) $N += 26;
    if (preg_match('/[A-Z]/', $password)) $N += 26;
    if (preg_match('/[0-9]/', $password)) $N += 10;
    if (preg_match('/[^a-zA-Z0-9]/', $password)) $N += 32;

    if (ctype_alpha($password)) $N = ($password === strtolower($password)) ? 26 : 52;
    if (ctype_digit($password)) $N = 10;

    return $L * log($N, 2);
}

$minEntropy = 90;
function isEntropyStrong(string $password)
{
    return calculateEntropy($password) >= $minEntropy;
}

function getEntropyMessage(string $password)
{
    $entropy = calculateEntropy($password);
    $rounded = round($entropy, 2);

    if ($entropy >= $minEntropy) {
        return "<p>L'entropie du mot de passe est de {$rounded} bits (min : {$minEntropy})</p>";
    }

    return "<p>L'entropie du mot de passe est de {$rounded} bits</p><p>L'entropie du mot de passe est trop faible<br>Elle doit être au moins de {$minEntropy} bits</p>";
}

?>