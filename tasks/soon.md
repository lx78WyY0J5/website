# 🔜 Soon — À faire (priorisé)

> Dernière mise à jour : 2025-10-14
> Chaque entrée : **description**, **exemple concret**, **pseudo-code / fichiers cibles**.

---

## 🔴 CRITIQUE — Sécurité & SEO fondamentaux

### 1. Protection CSRF sur tous les formulaires POST
- **Description** : Aucun token CSRF n'existe. Tout formulaire state-changing (login, register, password update, profile picture, logout) est vulnérable.
- **Exemple d'attaque** : Page malveillante → `<form action="https://doc.altherneum.fr/login" method="POST">` → auto-submit → victim connectée sans le savoir.
- **Fichiers cibles** :
  - **Nouveau** : `public/src/php/csrf.php` (génération/validation token)
  - **Modifier** : `public/src/pages/login/login.php`, `register/register.php`, `password/update/update.php`, `profile/picture/picture.php`, `logout/logout.php` (ajouter `requireCsrfToken()` en tout début)
  - **Modifier** : `public/src/pages/login/login.html`, `register/register.html`, `password/update/update.html`, `profile/picture/picture.html` (ajouter `<?= csrfField() ?>` dans `<form>` → renommer en `.php` ou include PHP)
- **Pseudo-code** (`csrf.php`) :
```php
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function validateCsrfToken(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}
function requireCsrfToken(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validateCsrfToken($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        logSecurityEvent('csrf_validation_failed', ['ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
        echo "<p>Jeton CSRF invalide. Rafraîchissez la page.</p>";
        exit;
    }
}
```

### 2. Template `<head>` unifié avec métas SEO/social
- **Description** : 70+ fichiers `head.php` ne contiennent que `<meta charset>` + `<title>`. Manquent : viewport, OG, Twitter Cards, canonical, description, favicons links, theme-color.
- **Exemple** : Page partagée sur Discord/Twitter → pas d'image, pas de description, titre générique.
- **Fichiers cibles** :
  - **Nouveau** : `public/src/includes/partials/head.template.php` (template avec variables `$title`, `$description`, `$canonical`, `$ogImage`)
  - **Modifier** : Tous les `head.php` → `require_once` du template + définir variables
  - **Nouveau** : `public/manifest.json` (PWA)
- **Pseudo-code** (`head.template.php`) :
```php
<?php
$title       = $title       ?? 'doc.Altherneum.fr';
$description = $description ?? 'Site personnel - Tutoriels, projets, veille tech';
$canonical   = $canonical   ?? 'https://doc.altherneum.fr' . $_SERVER['REQUEST_URI'];
$ogImage     = $ogImage     ?? 'https://doc.altherneum.fr/src/assets/gif/small.gif';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($description) ?>">
    <meta name="theme-color" content="#1a1a2e">
    <link rel="canonical" href="<?= htmlspecialchars($canonical) ?>">
    <!-- Favicons (utiliser assets existants) -->
    <link rel="icon" type="image/x-icon" href="/src/assets/favicon/rounded/rounded-favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/src/assets/favicon/rounded/rounded-favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/src/assets/favicon/rounded/rounded-apple-icon-180x180.png">
    <link rel="manifest" href="/manifest.json">
    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonical) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="doc.Altherneum.fr">
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">
    <title><?= htmlspecialchars($title) ?></title>
</head>
```

### 3. `robots.txt` + `sitemap.xml` + `CNAME` à la racine `public/`
- **Description** : Absents. Indispensables pour SEO (crawling, indexation, domaine custom GitHub Pages).
- **Exemple** : Google ne sait pas quoi crawler, pas de sitemap → indexation lente/incomplète.
- **Fichiers cibles** (nouveaux dans `public/`) :
  - `robots.txt` :
    ```
    User-agent: *
    Allow: /
    Sitemap: https://doc.altherneum.fr/sitemap.xml
    ```
  - `CNAME` :
    ```
    doc.altherneum.fr
    ```
  - `sitemap.xml` : **Génération dynamique recommandée** (script PHP cron ou à la volée via route `/sitemap.xml` dans `index.php`)

### 4. Durcir `php.ini` (prod)
- **Description** : Config par défaut insécurisée.
- **Modifications** :
```ini
expose_php = Off
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.cookie_secure = 1     ; seulement si HTTPS en prod
```
- **Fichier** : `php.ini` (lignes 14, 98, 99, 100)

### 5. Headers de sécurité HTTP (`.htaccess` pour Apache / config Nginx)
- **Description** : Aucun header CSP, HSTS, X-Frame-Options, etc.
- **Fichier cible** : `public/.htaccess` (nouveau)
- **Contenu minimal** :
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]

Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Permissions-Policy "geolocation=(), microphone=(), camera=()"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self'; connect-src 'self'; frame-ancestors 'self';"

<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/* "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>

<FilesMatch "^\.|composer\.json|composer\.lock|\.env|install\.sh|start\.sh|php\.ini|README\.md|agent\.md">
    Require all denied
</FilesMatch>
```

### 6. Corriger `agent.md` (référence fantôme `init-db.sql`)
- **Description** : L'`agent.md` (ligne ~444) liste `public/src/sql/init-db.sql` dans l'arborescence, mais ce fichier **n'existe pas**. Le schéma BDD est créé par `public/src/php/create-db.php` (lignes 40-170).
- **Impact** : IA qui lit l'agent.md croit qu'un fichier SQL existe → confusion, arborescence fausse.
- **Action** : Supprimer la ligne `init-db.sql` de l'arborescence dans `agent.md` + mettre à jour le `tree` output.
- **Fichier cible** : `agent.md` (racine repo)
- **Lignes concernées** : ~444 (dans la section "Arborescence du projet")

---

## 🟠 IMPORTANT — Fonctionnalités manquantes (existaient sur l'ancien site)

### 7. Endpoint RSS XML réel (`/rss.rss` ou `/feed.xml`)
- **Description** : Page `/outils/rss` existe (JS client-side qui affiche le XML dans un textarea), mais **pas de fichier XML servis par le serveur**. L'ancien site avait `rss.rss` statique à la racine.
- **Exemple** : Lecteur RSS (Feedly, Thunderbird) → `https://doc.altherneum.fr/rss.rss` → 404.
- **Solution** : Créer `public/src/pages/rss/feed.php` qui génère le XML (réutiliser logique `rss.js` côté serveur) + route dans `index.php` (`/rss.rss` ou `/feed.xml` → ce handler).
- **Pseudo-code** (`feed.php`) :
```php
<?php
header('Content-Type: application/rss+xml; charset=UTF-8');
$baseUrl = 'https://doc.altherneum.fr';
$items = [
    ['title' => '...', 'link' => $baseUrl . '/cours/html', 'pubDate' => '...', 'description' => '...'],
    // ... récupérer depuis BDD ou fichier JSON partagé
];
echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '  <channel>';
echo "    <title>doc.Altherneum.fr</title>";
echo "    <link>$baseUrl</link>";
echo "    <atom:link href=\"$baseUrl/rss.rss\" rel=\"self\" type=\"application/rss+xml\" />";
foreach ($items as $item) {
    echo "    <item>";
    echo "      <title><![CDATA[{$item['title']}]]></title>";
    echo "      <link>{$item['link']}</link>";
    echo "      <guid isPermaLink=\"true\">{$item['link']}</guid>";
    echo "      <pubDate>{$item['pubDate']}</pubDate>";
    echo "      <description><![CDATA[{$item['description']}]]></description>";
    echo "    </item>";
}
echo '  </channel></rss>';
```

### 8. Sitemap XML dynamique
- **Description** : L'ancien site avait `sitemap.xml` statique. Avec 100+ pages dynamiques, génération auto nécessaire.
- **Solution** : Route `/sitemap.xml` dans `index.php` → handler PHP qui scanne `public/src/pages/` + pages statiques connues → génère XML avec `<lastmod>` depuis git ou BDD.

---

## 🟡 AMÉLIORATIONS — UX / Qualité / Observabilité

### 9. `manifest.json` (PWA)
- **Description** : Permet installation "Add to Home Screen", splash screen, offline basique.
- **Fichier** : `public/manifest.json` (nouveau)
- **Contenu** :
```json
{
  "name": "doc.Altherneum.fr",
  "short_name": "Altherneum",
  "description": "Site personnel - Tutoriels, projets, veille tech",
  "start_url": "/",
  "display": "standalone",
  "background_color": "#1a1a2e",
  "theme_color": "#1a1a2e",
  "icons": [
    { "src": "/src/assets/favicon/rounded/rounded-favicon-192x192.png", "sizes": "192x192", "type": "image/png" },
    { "src": "/src/assets/favicon/rounded/rounded-favicon-512x512.png", "sizes": "512x512", "type": "image/png" }
  ]
}
```
- **Note** : Il faut créer le 512×512 (manquant dans assets).

### 10. `security.txt` (RFC 9116)
- **Description** : Standard pour contact sécurité (bug bounty, disclosure).
- **Fichier** : `public/.well-known/security.txt` (nouveau, créer dossier)
- **Contenu** :
```
Contact: mailto:contact@altherneum.fr
Expires: 2026-12-31T23:59:59.000Z
Preferred-Languages: fr, en
Canonical: https://doc.altherneum.fr/.well-known/security.txt
```

### 11. `humans.txt` (fun, tradition)
- **Description** : Crédits humains (dev, design, tools).
- **Fichier** : `public/humans.txt` (nouveau)

### 12. Health check endpoint (`/health`)
- **Description** : Monitoring uptime (Uptime Kuma, Prometheus, etc.).
- **Fichier** : `public/src/pages/health/index.php` (nouveau)
- **Réponse** : JSON `{ "status": "ok", "db": "connected", "time": "..." }` + HTTP 200/503.

### 13. Service Worker basique (`sw.js`) + cache offline
- **Description** : Cache statique (CSS, JS, fonts, favicons) pour chargement instantané repeat visits.
- **Fichiers** : `public/sw.js` (nouveau) + registration dans `head.template.php` ou `index.php`.

### 14. Données structurées JSON-LD (SEO rich snippets)
- **Description** : `WebSite`, `Person`, `Article` (pour cours), `SoftwareApplication` (pour outils/jeux).
- **Intégration** : Dans `head.template.php` ou par page selon type.

### 15. Rate-limit sur endpoints publics non-auth (contact, outils, jeux)
- **Description** : Actuellement rate-limit seulement sur auth + profile picture. Les pages publiques (outils, jeux, cours) sont ouvertes.
- **Action** : Ajouter `checkRateLimit($pdo, $clientIp, 'public_page', 100, 1)` dans `index.php` avant include page.

---

## 🟢 NICE TO HAVE

### 16. Préconnect / DNS-prefetch pour assets externes
- **Description** : Si CDN, polices Google, APIs tierces → `<link rel="preconnect" href="...">` dans head.

### 17. Error tracking JS côté client
- **Description** : `window.onerror` → POST `/api/js-error` (log dans `security.log` ou fichier dédié).

### 18. Backup automatique BDD (cron)
- **Description** : `mysqldump` journalier → compression → stockage externe (S3, Drive, etc.).

### 19. Mise à jour auto des prix crypto (donation)
- **Description** : Actuellement `gather.js` fetch prix côté client. Cacher via cron serveur + BDD → éviter rate-limit API + charger instantané.

### 20. Internationalisation (i18n) FR/EN
- **Description** : Fichiers `lang/fr.php`, `lang/en.php` + détection `Accept-Language` ou param `?lang=en`.

---

## 📋 Ordre d'exécution suggéré

| Phase | Tâches | Temps estimé |
|-------|--------|--------------|
| **1 - Sécurité** | 1 (CSRF), 4 (php.ini), 5 (.htaccess), 6 (fix agent.md) | 1-2h |
| **2 - SEO/Base** | 2 (head template), 3 (robots/sitemap/CNAME), 14 (JSON-LD) | 2-3h |
| **3 - Fonctionnel** | 7 (RSS XML), 8 (sitemap dyn), 9 (manifest), 10 (security.txt), 12 (health) | 2-3h |
| **4 - Qualite** | 11 (humans), 13 (SW), 15 (rate-limit public), 16 (preconnect) | 1-2h |
| **5 - Bonus** | 17 (JS error tracking), 18 (backup), 19 (crypto cache), 20 (i18n) | Selon besoin |