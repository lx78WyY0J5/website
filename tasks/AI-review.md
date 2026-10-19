# Revue de code — sécurité, bugs & optimisation

> Date : 2026-10-18
> Périmètre : ensemble du dépôt `website/` (PHP, JS, SQL, shell, config).
> Méthode : lecture statique du code + `php -l` (aucune erreur de syntaxe) + vérification des includes.
> ⚠️ Aucun fichier n'a été modifié ni exécuté ; seul ce rapport a été créé.
> Confiance : les failles ci-dessous sont déduites du code, sans exploitation en live.

## Synthèse

| Sévérité | Nombre |
|---|---|
| 🔴 Critique | 3 |
| 🟠 Élevé | 4 |
| 🟡 Moyen | 8 |
| 🔵 Faible / optimisation | 8 |

Le socle est globalement sain : requêtes préparées partout (pas d'injection SQL détectée),
`password_hash`/`password_verify`, CSRF présent sur les formulaires, rate-limiting, logs.
Les problèmes principaux viennent de l'**échappement HTML côté client** (XSS) et de
quelques **incohérences de contrôle**.

---

## 🔴 Critique

### 1. XSS stocké dans le dashboard admin (données visiteur non échappées)
- `public/src/php/api/data.php` (l. 50-312) stocke sans nettoyage des champs **entièrement
  contrôlés par n'importe quel visiteur** : `user_agent`, `oscpu`, `platform`, `path`,
  `languages`, `gpu`, `plugins`, `source`, etc.
- `public/src/php/api/stats-dashboard.php` (l. 20-26) les renvoie bruts.
- `public/src/pages/outils/stats/stats.js` (l. 129-146) les injecte via `innerHTML`.
- Conséquence : POST `/api/data` avec `user_agent: "<img src=x onerror=...>"` → exécution dans
  la session de l'admin qui ouvre `/outils/stats`.
- **Correctif** : échapper à l'affichage (`textContent`/`escapeHtml`) et/ou assainir en entrée.

### 2. Contournement CSRF sur le changement de mot de passe
- `public/src/pages/password/update/update.php` : l. 31-34 met `$can_update = false` si le token
  est invalide, puis l. 40 écrase la valeur : `$can_update = checkPassword(...)`.
- Le résultat de la validation CSRF est donc **perdu** : un POST sans token valide passe.
- **Correctif** : ne jamais réécrire la variable CSRF, ex.
  `$can_update = validateCsrfToken() && checkPassword(...);` en gardant les blocs explicites.

### 3. `create-db.php` accessible publiquement
- `public/index.php` l. 5 : tout fichier existant sous `public/` est servi tel quel
  (`return false`) → `/src/php/create-db.php` est **directement atteignable**.
- `public/src/php/create-db.php` l. 7-13 : le script s'exécute sans admin si `LOCAL`,
  `FIRST_RUN` ou `FORCE_DB_INIT` est vrai. Le `.env` actuel a `LOCAL="true"`.
- Il affiche `DB_NAME`, les erreurs SQL et (re)crée/initialise la base.
- **Correctif** : sortir les scripts PHP du dossier public, ou `.htaccess`/routage bloquant
  `/src/php/`, ou supprimer la branche `LOCAL` côté web.

---

## 🟠 Élevé

### 4. XSS réfléchi multiples (paramètres d'URL)
- **Recherche** : `public/src/includes/header/search/searchbar.js` l. 238 & 254 injectent
  `queryText` (issu de `?search=`) dans `innerHTML`. Un lien « queryable » (ex. GitHub) accepte
  des mots libres → `/…?search=github <img src=x onerror=alert(1)>`.
- **Films** : `public/src/js/film.js` l. 1718-1720 injecte `imdbID` (param `?imdb=`) dans
  `innerHTML` sur `/admin/film`, `/admin/to-watch`, `/admin/series`, `/admin/animes`.
- **Vidéos** : `public/src/js/youtubeEmbed.js` l. 171 + 495 injectent `videoid` (`?videoid=`)
  sur `/admin/music` et `/admin/video`.
- **Correctif** : `textContent` partout où la donnée vient de l'URL ou d'une API externe.

### 5. XSS stocké via le corps des issues GitHub
- `public/src/pages/github/issues/issues.js` l. 175-176 : `parseMarkdown(body)` → `innerHTML`.
- N'importe qui peut ouvrir une issue contenant du HTML brut → XSS sur `/github/issues`.
- **Correctif** : assainir le HTML produit par `parseMarkdown` (allow-list) avant injection.

### 6. Pas de régénération d'ID de session à la connexion
- `public/src/pages/login/login.php` l. 62-64 ne fait pas `session_regenerate_id(true)`.
- `php.ini` l. 90 : `session.use_strict_mode = 0`.
- Risque de **fixation de session**.
- **Correctif** : régénérer l'ID après `password_verify` (et à l'inscription).

### 7. `register.php` : `$_SESSION["id"]` non défini
- `public/src/pages/register/register.php` l. 124 : `$_SESSION["id"] = $id;` alors que `$id`
  n'existe pas → session avec `id` vide/`null`, `loggedin=true`.
- Impacts : `intval($_SESSION["id"]) = 0` (upload dans `uploads/profile_pictures/0/`),
  warnings à l'accueil, logique utilisateur incohérente.
- **Correctif** : récupérer `$pdo->lastInsertId()` après l'INSERT.

---

## 🟡 Moyen

1. **Fuite d'informations BDD** : `public/src/php/PDO.php` l. 16 affiche `$e->getMessage()` ;
   idem `create-db.php` (plusieurs `echo`), `data.php` l. 462, `stats-dashboard.php` l. 16.
   → masquer les messages détaillés en production.
2. **`<head>` avant `<!DOCTYPE html>`** : `public/index.php` inclut les `head.php` (l. 74/80)
   avant d'écrire le document (l. 104). HTML invalide, `<head>`/`<body>` implicites, SEO dégradé.
   La 404 n'inclut aucun `head.php`. → restructurer le template.
3. **`delete` de rate-limit non indexé** : `public/src/php/rate_limiter.php` l. 9 fait un
   `DELETE … WHERE requested_at < …` à **chaque requête**, sans index sur `requested_at`
   (l'index est composite `identifier, endpoint, requested_at`). → ajouter un index.
4. **`stats-dashboard.php`** : `session_start()` l. 2 alors que la session est déjà ouverte,
   et recréation inutile d'une connexion PDO (l. 12-17) au lieu de réutiliser `$pdo`.
5. **Placeholders nommés dupliqués** : `data.php` réutilise les mêmes `:param` dans l'INSERT et
   l'`ON DUPLICATE KEY UPDATE`. Ne fonctionne que grâce aux requêtes émulées ; fragile.
6. **`exec()`** : `public/src/php/api/cpu.php` l. 6-7 exécute une commande shell (`top`).
   Pas d'injection (commande constante) mais surface inutile + info système exposée.
   `info.php` utilise aussi `shell_exec` (admin seulement).
7. **Crypto faible** : `public/src/pages/outils/rsa/rsa.js` l. 22 génère les clés avec
   `Math.random()` → clés RSA prévisibles. À réserver à un usage pédagogique explicite.
8. **`install.sh`** : crée l'utilisateur DB avec `GRANT ALL PRIVILEGES ON *.*` (l. 205/210) et
   passe les mots de passe en argument shell (visibles dans l'historique/`ps`).

---

## 🔵 Faible / optimisation

1. **Logs non bornés** : `logging.php` écrit sans rotation ni taille max ; `rate_limiter` et
   l'API journalisent beaucoup (`api_visit`, `visit`, `snitching`). → rotation/cap.
2. **Charge des scripts** : `public/src/includes/header/header.php` l. 15-20 charge `music.js`,
   `video.js` et `film.js` (gros fichiers de données) et appelle `mergeYouTubeTitles` 3× **sur
   toutes les pages**. → ne charger que sur les pages concernées.
3. **Compteur de vues coûteux** : `view_counter.php` fait 4 écritures + jusqu'à 7 lectures par
   page. → regroupement / mise en cache par session.
4. **Instabilité routage API** : `public/index.php` l. 42, `str_replace('api','',$lowerUri)`
   remplace **toutes** les occurrences. → `substr`/`preg_replace('/^api/')`.
5. **Mot de passe tronqué** : `login.php` l. 37 applique `trim()` au mot de passe ; un espace
   légitime devient impossible. → ne pas trimmer les mots de passe.
6. **`$token;` mort** dans `csrf.php` l. 19 ; `$file_type` inutilisé dans `picture.php` l. 55.
7. **Page Settings cassée** : `public/src/pages/settings/index.php` l. 3 inclut
   `settings/settings.php` **qui n'existe pas** → warning + script absent.
8. **`php.ini`** : `expose_php = On` (l. 14) et cookies de session non durcis (l. 98-100).
   Le `Secure` forcé dans `index.php` empêche aussi le login en `http://localhost` (connu).

---

## Points positifs

- Requêtes **100 % préparées** (`PDO` + `bindParam`) → pas d'injection SQL trouvée.
- Mots de passe hachés (`password_hash`), entropie minimale exigée.
- CSRF présent sur login/register/logout/upload/mot de passe (malgré le bug #2).
- Rate limiting par IP et par utilisateur, `REMOTE_ADDR` non falsifiable (pas de
  `X-Forwarded-For`).
- Upload de photo : double validation extension + `finfo` + `getimagesize`, `basename()`.
- Réponses API inconnues → 404 ; chemins de pages validés par `file_exists`.

## Priorités conseillées

1. Corriger les XSS (#1, #4, #5) — impact le plus fort.
2. Corriger le contournement CSRF (#2) et sortir `create-db.php` du web (#3).
3. `session_regenerate_id` (#6) et `lastInsertId` à l'inscription (#7).
4. Masquer les erreurs BDD (#8-1) et restructurer le `<head>` (#8-2).
