# website
## Introduction
- Site web en HTML, CSS, PHP, SQL
- Site permettant de stocker différent contenus et apprendre la création de backend en PHP + SQL
## Cloner le repo
- `git clone https://github.com/lx78WyY0J5/website.git`
- Se déplacer dans le repo `cd website`
## Configurer le .env
- copier [./.env.exemple](/.env.exemple)
- vers [./.env](/.env)
- `nano .env`
### .env inexistant
En cas de `.env` inexistant, le `.env.exemple` sera copié vers `.env`
## Installation
[./install.sh](/install.sh)
```
chmod +x install.sh
install.sh
```
## Démarrage
[./start.sh](/start.sh)
```
chmod +x start.sh
start.sh
```
## Tests automatiques
[./.github/workflows/ci.yml](/.github/workflows/ci.yml)
## Index.php & Accueil
### Index.php
- [./public/index.php](/public/index.php)
### Accueil
- [./public/src/index/index.php](/public/src/index/index.php)