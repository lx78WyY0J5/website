# website
## Introduction
- Site web en HTML, CSS, PHP, SQL
- Site permettant de stocker différent contenus et apprendre la création de backend en PHP + SQL
## Arborésence
Crée via le package `tree`
  - `sudo pacman -S tree` : Installation
  - `tree ./` : Créer l'arborésence du dossier
```
./
├── agent.md
├── README.md
└── src
    ├── assets
    │   ├── img
    │   └── svg
    ├── css
    │   └── index.css
    ├── html
    │   └── index.html
    ├── js
    │   └── index.js
    └── php
        └── index.php

9 directories, 6 files

```
## Installation et lancement
### PHP
  - **Arch Linux** : `sudo pacman -S php php-apache php-mysqli php-json php-curl`
  - **Ubuntu** : `???`
- Démarrer le serveur PHP local : `php -S localhost:8000 -t src/html`
### MySQL
#### Installer sous Arch Linux
- `sudo pacman -S mariadb`
#### Installer sous Ubuntu
- `???`
#### Lancer MariaDB
```bash
sudo mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
sudo systemctl start mariadb.service
sudo systemctl enable mariadb.service
```
### Configurer un utilisateur et mot de passe
```bash
mysql -u root -p
CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON *.* TO 'webuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
### Démarrer le serveur MySQL local
```bash
sudo systemctl start mariadb.service
```