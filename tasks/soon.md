# User-Agent, IP, route, etc logging
- Anti Bot
- Each request log User-Agent, CPU, GPU, VideoDriver, TimeZone, browserLang etc
- Add Console error & warning
- Add /api/logs endpoint, on each post on this endpoint, store to BDD data
  - `window.onerror` → POST `/api/js-error` (log dans `security.log` ou fichier dédié)
- Pour ça il faudra créer une route /api/ car les routes classiques vont faire que PHP retourne du HTML, /api/ ne doit retourner que la réponse (JSON ?)

# Template `<head>` unifié avec métas SEO/social
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

# `robots.txt` + `sitemap.xml` + `CNAME` à la racine `public/`
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

# Durcir `php.ini` (prod)
- **Description** : Config par défaut insécurisée.
- **Modifications** :
```ini
expose_php = Off
session.cookie_httponly = 1
session.cookie_samesite = Strict
session.cookie_secure = 1     ; seulement si HTTPS en prod
```
- **Fichier** : `php.ini` (lignes 14, 98, 99, 100)

# Headers de sécurité HTTP (`.htaccess` pour Apache / config Nginx)
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


# Endpoint RSS XML réel (`/rss.rss` ou `/feed.xml`)
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

# Sitemap XML dynamique
- **Description** : L'ancien site avait `sitemap.xml` statique. Avec 100+ pages dynamiques, génération auto nécessaire.
- **Solution** : Route `/sitemap.xml` dans `index.php` → handler PHP qui scanne `public/src/pages/` + pages statiques connues → génère XML avec `<lastmod>` depuis git ou BDD.


### Page /github/statistiques, ajouter le repo website
- **Ajouter le repo `website`** dans la section « Références » de la page `/github/statistiques` ainsi que sur le dépôt GitHub Altherneum. Le repo est actuellement hébergé sur mon profil personnel.

# `manifest.json` (PWA)
- **Description** : Permet installation "Add to Home Screen", splash screen, offline basique.
- **Fichier** : `public/manifest.json` (nouveau)
- **Contenu** :
```json
{
  "name": "doc.Altherneum.fr",
  "short_name": "Altherneum",
  "description": "Site personnel - Notes, projets, veille tech",
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

# `security.txt` (RFC 9116)
- **Description** : Standard pour contact sécurité (bug bounty, disclosure).
- **Fichier** : `public/.well-known/security.txt` (nouveau, créer dossier)
- **Contenu** :
```
Contact: mailto:contact@altherneum.fr
Expires: 2026-12-31T23:59:59.000Z
Preferred-Languages: fr, en
Canonical: https://doc.altherneum.fr/.well-known/security.txt
```

# `humans.txt` (fun, tradition)
- **Description** : Crédits humains (dev, design, tools).
- **Fichier** : `public/humans.txt` (nouveau)

# Health check endpoint (`/health`)
- **Description** : Monitoring uptime (Uptime Kuma, Prometheus, etc.).
- **Fichier** : `public/src/pages/health/index.php` (nouveau)
- **Réponse** : JSON `{ "status": "ok", "db": "connected", "time": "..." }` + HTTP 200/503.

# Service Worker basique (`sw.js`) + cache offline
- **Description** : Cache statique (CSS, JS, fonts, favicons) pour chargement instantané repeat visits.
- **Fichiers** : `public/sw.js` (nouveau) + registration dans `head.template.php` ou `index.php`.

# Données structurées JSON-LD (SEO rich snippets)
- **Description** : `WebSite`, `Person`, `Article` (pour cours), `SoftwareApplication` (pour outils/jeux).
- **Intégration** : Dans `head.template.php` ou par page selon type.

# Rate-limit sur endpoints publics non-auth (contact, outils, jeux)
- **Description** : Actuellement rate-limit seulement sur auth + profile picture. Les pages publiques (outils, jeux, cours) sont ouvertes.
- **Action** : Ajouter `checkRateLimit($pdo, $clientIp, 'public_page', 100, 1)` dans `index.php` avant include page.

# Préconnect / DNS-prefetch pour assets externes
- **Description** : Si CDN, polices Google, APIs tierces → `<link rel="preconnect" href="...">` dans head.

# Backup automatique BDD (cron)
- **Description** : `mysqldump` journalier → compression → stockage externe (S3, Drive, etc.).

# Internationalisation (i18n) FR/EN
- **Description** : Fichiers `lang/fr.php`, `lang/en.php` + détection `Accept-Language` ou param `?lang=en`.