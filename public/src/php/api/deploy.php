<?php

$owner    = 'lx78WyY0J5';
$repo     = 'website';
$workflow = 'ci.yml';

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
    die("GitHub API error: {$httpCode}");
}

$data = json_decode($response, true);
$run  = $data['workflow_runs'][0] ?? null;

if (!$run) {
    die("No workflow runs found.");
}

if ($run['status'] === 'completed' && $run['conclusion'] === 'success') {
    echo "✅ Last run : " . $run['conclusion'] . "<br>";
    $output = [];
    $exitCode = 0;

    exec('cd .. && git pull origin main 2>&1', $output, $exitCode);
    //exec('cd /var/www/your-site && git pull origin main 2>&1', $output, $exitCode);

    if ($exitCode === 0) {
        echo "🚀 Deployed successfully<hr>";
        echo implode("<br>", $output);
    } else {
        echo "❌ Git pull failed<hr>";
        echo "Exit code : " . $exitCode;
        echo implode("<br>", $output);

        http_response_code(500);
        exit;
    }
} else {
    echo "❌ Last run did not succeed";
    echo "Run status : " . $run['status'];
    echo "Run conclusion : " . $run['conclusion'];
    http_response_code(403);
}