# website
## Introduction
- Site web en HTML, CSS, PHP, SQL
- Site permettant de stocker différent contenus et apprendre la création de backend en PHP + SQL
## Cloner le repo
- `git clone https://github.com/lx78WyY0J5/website.git`
- Se déplacer dans le repo `cd website`
## Configurer le .env
- copier [./.env.exemple](/.env.exemple) vers [./.env](/.env)
  - `cp ./.env.exemple ./.env`
- `nano .env`
### .env inexistant
En cas de `.env` inexistant, le `.env.exemple` sera copié vers `.env`

## Installation et lancement
### Installation
[./install.sh](/install.sh)
```
chmod +x install.sh
install.sh
```
### Démarrage
[./start.sh](/start.sh)
```
chmod +x start.sh
start.sh
```
### Changer le mot de passe administrateur
- Se connecter `/login` (voire le `.env`)
  - `administrator`
  - `SuperUser123@World!`
- Aller sur `/password/update`
  - Changer le mot de passe

## Tests automatiques
[./.github/workflows/ci.yml](/.github/workflows/ci.yml)

## Comment le site marche
### Index.php
- [./public/index.php](/public/index.php)
### Pour créer une nouvelle page
- `./public/src/pages/<page_name>` Ce dossier contient les pages à afficher
  - Exemple l'URL `/admin/contact` sera dans le dossier `public/src/pages/admin/contact`
  - Ces dossiers auront plusieurs fichiers servant le CSS, HTML, JS, meta data, etc. Pensez à copier donc une autre page pour comprendre la logique
### Pour créer une nouvelle API
- `./public/src/php/api/` Ce dossier contient les fichiers PHP servant de route
### Accueil
- [./public/src/index/index.php](/public/src/index/index.php)