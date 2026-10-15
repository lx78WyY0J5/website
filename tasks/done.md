# ✅ Fait — Fonctionnalités implémentées et opérationnelles

> Dernière mise à jour : 2025-10-14
> Ce fichier liste ce qui **fonctionne déjà** dans le repo `website/` avec pointeurs vers le code.

---

## 🏗️ Architecture & Backend

### 1. Routage frontal unique (`public/index.php`)
- **Description** : Point d'entrée unique, démarre la session, charge les middlewares (logging, rate-limit, view-counter), route selon `$_SERVER['REQUEST_URI']` vers `src/pages/<uri>/index.php` ou 404.
- **Fichier** : `public/index.php` (lignes 1–81)
- **Détails** :
  - `session_start()` + `ob_start()` pour capture sortie
  - Rate-limit global par IP (20 req/min anonyme, 50 connecté)
  - Inclusion dynamique du `head.php` de chaque page
  - Tracking vues (page + site + user) via `view_counter.php`
  - **Route API** : Gestion des routes `/api/` insensibles à la casse via `str_starts_with` et `str_lower` ; routage basé sur fichiers : vérifie l'existence de `src/php/api/<route>.php` et le charge, retourne 404 si introuvable. Routes implémentées : `/api/` (retourne la date) et `/api/uptime` (retourne le temps de fonctionnement du serveur).

### 2. Connexion BDD + PDO (`public/src/php/PDO.php`)
- **Description** : Singleton PDO avec chargement `.env` (`loadEnv.php`), erreurs en exception, mode `ATTR_EMULATE_PREPARES = false`.
- **Fichier** : `public/src/php/PDO.php`
- **Utilisé par** : Tous les handlers PHP (`login.php`, `register.php`, `update.php`, `picture.php`, etc.)

### 3. Système de taux limite (`public/src/php/rate_limiter.php`)
- **Description** : Rate-limit par identifiant (IP ou `user_<id>`) + endpoint + fenêtre glissante (fenêtre 1 min par défaut). Stockage en BDD table `rate_limits`.
- **Fichier** : `public/src/php/rate_limiter.php`
- **Fonctions** : `checkRateLimit($pdo, $identifier, $endpoint, $maxAttempts, $windowMinutes)`, `rateLimitExceededResponse($endpoint)`, `getClientIdentifier()`

### 4. Journalisation sécurité (`public/src/php/logging.php`)
- **Description** : Écriture dans `logs/security.log` (JSON lines) : timestamp, event_type, details (IP, user, endpoint, etc.).
- **Fichier** : `public/src/php/logging.php`
- **Événements** : `login_success`, `login_failed_password`, `registration`, `rate_limit_exceeded`, `visit`, `password_changed`, `profile_picture_uploaded`, `csrf_validation_failed` (à venir)

### 5. Compteur de vues (`public/src/php/view_counter.php`)
- **Description** : Incrémente `page_views` (table `views`) par page + utilisateur (si connecté) + total site. Fournit stats pour footer.
- **Fichier** : `public/src/php/view_counter.php`
- **Fonctions** : `trackView($pdo, $uri, $userId)`, `getViewStats($pdo, $uri, $userId)`

### 6. Validation mot de passe (`public/src/php/password_validation.php` + `password_entropy.php`)
- **Description** : Règles communes (longueur, maj/min/chiffre/special, entropie, confirmation). Réutilisé par `register.php` et `update.php`.
- **Fichiers** : `public/src/php/password_validation.php`, `public/src/php/password_entropy.php`
- **Fonction** : `checkPassword($password, $confirm_password)`

---

## 🔐 Authentification & Sessions

### 7. Inscription (`public/src/pages/register/`)
- **Fichiers** : `register.php` (logique), `register.html` (formulaire), `register.js` (UX), `head.php` (métas)
- **Flux** : POST → validation (format, unicité, code invitation `REGISTER_CODE`, force MDP) → hash `password_hash()` → INSERT → session auto-login → redirect `/`
- **Rate-limit** : 5 req/15 min par IP

### 8. Connexion (`public/src/pages/login/`)
- **Fichiers** : `login.php`, `login.html`, `login.js`, `head.php`
- **Flux** : POST → SELECT user → `password_verify()` → session (`loggedin`, `id`, `username`, `is_admin` si username = `ADMIN_USERNAME`) → redirect `/`
- **Rate-limit** : 10 req/15 min par IP

### 9. Déconnexion (`public/src/pages/logout/`)
- **Fichiers** : `logout.php`, `logout.html`, `logout.js`, `head.php`
- **Action** : `$_SESSION = []` + `session_destroy()` + redirect `/`

### 10. Changement mot de passe (`public/src/pages/password/update/`)
- **Fichiers** : `update.php`, `update.html`, `update.js`, `head.php`
- **Flux** : Vérif ancien MDP (`password_verify`) → validation nouveau (réutilise `checkPassword`) → UPDATE hash → `session_destroy()` + redirect `/login` après 5s
- **Rate-limit** : 5 req/15 min par IP + par user

### 11. Photo de profil (`public/src/pages/profile/picture/`)
- **Fichiers** : `picture.php`, `picture.html`, `picture.js`, `head.php`, `getPicture.php`
- **Upload** : Multipart, validation extension + MIME (finfo) + `getimagesize` + taille ≤ 5 Mo → stockage `uploads/profile_pictures/<userId>/` → INSERT `user_pictures`
- **Rate-limit** : 20 req/60 min par IP + par user
---
### 11b. Protection CSRF (`public/src/php/csrf.php` + formulaires)
- **Description** : Protection CSRF (Cross-Site Request Forgery) implémentée sur tous les formulaires POST du site. Empêche les requêtes non autorisées en validant un token unique stocké en session.
- **Fichiers** : `public/src/php/csrf.php`
- **Fonctionnement** : 
  - Génération d'un token unique via `bin2hex(random_bytes(32))` stocké en session
  - Champ caché inclus dans chaque formulaire
  - Validation côté serveur avec `validateCsrfToken()` utilisant `hash_equals()` pour la sécurité
  - Tout bloqué si le token est manquant ou invalide
- **Utilisé par** : Formulaires de connexion, inscription, téléchargement de photo de profil, changement de mot de passe, login, logout
- Page sans POST/PUT/UPDATE/DELETE/GET : Utilise un formulaire invisible et le fait envoyer via JS (exemple le /Logout)

---

## 🎨 Frontend & UI

### 12. Système de thèmes (`public/src/js/theme.js` + `public/src/css/theme.css`)
- **Thèmes** : `dark` (défaut système), `light`, `day` (bleu clair)
- **Persistance** : `localStorage.theme` + `localStorage.TransparencyPower` + `localStorage.BackgroundGride`
- **Application** : Classe sur `<html>` (ex: `<html class="dark">`) → variables CSS `--background-color-R/G/B` + RGBA dynamique
- **Toggle** : Bouton dans header (icône sun/light/moon selon thème)

### 13. Header & Recherche (`public/src/includes/header/`)
- **Composants** : `header.php` (nav + searchbar), `searchbar.html/.js/.css`, `navlink.php` (liste liens dynamiques), `header.js` (toggle mobile, raccourcis clavier)
- **Recherche** : Filtrage client-side sur `navlink.php` (titres + URLs), navigation clavier (↑↓/Entrée)

### 14. Footer (`public/src/includes/footer/`)
- **Contenu** : Liens sociaux (Discord, GitHub, Minecraft, Email, YouTube, Twitch, Steam, Instagram, Twitter), stats de vue (site/page/user), temps de rendu + poids page (via `ob_get_length()`), section admin (liens locaux) si `is_admin`

### 15. Pourcentage de scroll (`public/src/js/scrollPercentage.js` + `public/src/css/scrollPercentage.css`)
- **Description** : Barre de progression fine en haut de page, calcul `scrollTop / (scrollHeight - clientHeight) * 100`

### 16. Pages de contenu (exemples)
- **Accueil** : `public/src/index/index.php` (welcome user si connecté + `index.html` statique)
- **Info serveur** : `public/src/pages/info/info.php` (PHP version, memory, disk, DB status)
- **Admin** : Contact (liens sociaux + clé GPG), Donation (adresses crypto + prix temps réel via `gather.js`), Hardware/Software/Video/Animes/Series/Film/Note/To-Watch/Music
- **Outils** : Base64, Caesar, Vigenere, RSA, Donut 3D, Frames, Matrice, Ping, Logger, Console, Cube, Noise, Question-mark, CrashMyBrowser
- **Jeux** : Minesweeper, Slot, Snake, Cookie-clicker, Idle, ShopTitans, Fruits
- **Cours** : Linux (bash, ssh, lvm, network, etc.), Web (HTML, CSS, JS, PHP, SQL, Docker, GPO, DNS, Active Directory, etc.), GitHub (stats, issues, bot, plugin, readme, license, code_of_conduct, contributeur, support, server)

---

## 🧪 Tests & CI

### 17. Suite de tests (`tests/`)
- **Lancement** : `tests/run-all-tests.sh` (bash, curl + PHP CLI)
- **Couverture** :
  - Auth : login valide, login invalide, injection SQL login/register, username court, MDP faible (6 variants), code inscription manquant/invalide
  - Sécurité : rate-limit, view-counter
- **CI** : `.github/workflows/ci.yml` → `install.sh` → `start.sh` (MariaDB + PHP server) → `run-all-tests.sh`

### 18. Scripts de déploiement local
- **`install.sh`** : Crée `.env` depuis `.env.exemple`, init BDD (`create-db.php` + `init-db.sql`), permissions `uploads/`
- **`start.sh`** : Détecte OS (Arch/Ubuntu/Debian/Termux), démarre MariaDB (systemd ou `mysqld_safe`), lance `php -S 127.0.0.1:8000 -t public/` en arrière-plan (CI) ou foreground (local)

---

## 📦 Assets & Config

### 19. Favicons multi-format (`public/src/assets/favicon/`)
- **3 styles** : `circular/`, `rounded/`, `square/`
- **Tailles chacun** : 16, 32, 96, 192, 144, 150, 310, 70, 180, 152 + `.ico`
- **Racine** : `public/favicon.ico` (WebP 48×48)

### 20. Polices locales (`public/src/assets/font/`)
- `VarelaRound-Regular.ttf`, `Roboto-Regular.ttf`, `Pacifico-Regular.ttf` → chargées via `font.css` (`@font-face`)

### 21. Configuration PHP (`php.ini`)
- `memory_limit = 128M`, `max_execution_time = 30`, `post_max_size = 10M`, `upload_max_filesize = 10M`
- Extensions : `curl`, `mbstring`, `pdo_mysql`, `readline`, `xml`, `zip`
- **⚠️ À durcir** : `expose_php = On`, `session.cookie_httponly =`, `session.cookie_samesite =`, `session.cookie_secure =`

---

## 📁 Structure des pages (pattern constant)

Chaque page sous `public/src/pages/<catégorie>/<nom>/` suit :
```
index.php      → require head.php + include <nom>.html (+ logique POST si besoin)
head.php       → echo '<head><meta charset="UTF-8"><title>Titre</title></head>'
<nom>.html     → <form> + contenu statique + <script src="<nom>.js">
<nom>.js       → UX client-side (validation légère, animations)
<nom>.css      → styles scoped
```
Exemple : `public/src/pages/login/` (4 fichiers + head.php)