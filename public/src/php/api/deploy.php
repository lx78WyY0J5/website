<?php

$owner    = 'lx78WyY0J5';
$repo     = 'website';
$workflow = 'ci.yml';
$branch   = 'main';

// Racine du dépôt website, aussi bien dans le conteneur (/var/www/html) que
// dans un checkout local (…/website).
$repoPath = dirname(__DIR__, 4);

$url = "https://api.github.com/repos/{$owner}/{$repo}/actions/workflows/{$workflow}/runs?per_page=1";

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Accept: application/vnd.github+json',
        'User-Agent: DeployScript/1.0',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    http_response_code(502);
    echo "GitHub API error: " . htmlspecialchars((string) $httpCode);
    exit;
}

$data = json_decode($response, true);
$run  = null;
if (isset($data['workflow_runs'][0])) {
    $run = $data['workflow_runs'][0];
}

if ($run === null) {
    http_response_code(502);
    echo "No workflow runs found.";
    exit;
}

$runBranch = '';
if (isset($run['head_branch'])) {
    $runBranch = (string) $run['head_branch'];
}

// Ne pas déployer le résultat d'une PR ou d'une autre branche.
if ($runBranch !== $branch) {
    http_response_code(403);
    echo "❌ Dernière run hors " . htmlspecialchars($branch);
    echo "<br>Dernière run pour : " . htmlspecialchars($runBranch);
    exit;
}

$runStatus = '';
if (isset($run['status'])) {
    $runStatus = (string) $run['status'];
}

$runConclusion = '';
if (isset($run['conclusion'])) {
    $runConclusion = (string) $run['conclusion'];
}

if ($runStatus === 'completed' && $runConclusion === 'success') {
    echo "✅ Last run : " . htmlspecialchars($runConclusion) . "<br>";

    $output = [];
    $exitCode = 0;

    // Prépare la commande de mise à jour exécutée par l'utilisateur apache.
    // - cd <repo> : place Git à la racine du dépôt ; escapeshellarg protège
    //   les espaces et les caractères spéciaux du chemin.
    // - HOME=/tmp : donne à Git un dossier personnel accessible en écriture.
    // - GIT_TERMINAL_PROMPT=0 : empêche Git d'attendre un identifiant.
    // - timeout 60 : interrompt la commande si elle reste bloquée (code 124).
    // - safe.directory : autorise Git à utiliser le dépôt monté même si son
    //   propriétaire ne correspond pas à l'utilisateur courant.
    // - pull --ff-only : avance seulement en fast-forward, sans commit de merge.
    // - origin main : récupère la branche main du dépôt distant.
    // - 2>&1 : renvoie aussi les erreurs dans la sortie lue par exec().
    $command = 'cd ' . escapeshellarg($repoPath) . ' && '
        . 'HOME=/tmp GIT_TERMINAL_PROMPT=0 timeout 60 '
        . 'git -c safe.directory=' . escapeshellarg($repoPath)
        . ' pull --ff-only origin ' . escapeshellarg($branch) . ' 2>&1';

    // Exécute la commande et récupère sa sortie et son code de retour.
    exec($command, $output, $exitCode);

    if ($exitCode === 0) {
        echo "🚀 Deployed successfully<hr>";
        echo nl2br(htmlspecialchars(implode("\n", $output)));
    } else {
        http_response_code(500);
        echo "❌ Git pull failed<hr>";
        echo "Exit code : " . htmlspecialchars((string) $exitCode) . "<br>";
        echo nl2br(htmlspecialchars(implode("\n", $output)));
        exit;
    }
} else {
    http_response_code(403);
    echo "❌ Last run did not succeed";
    echo "<br>Run status : " . htmlspecialchars($runStatus);
    echo "<br>Run conclusion : " . htmlspecialchars($runConclusion);
}
