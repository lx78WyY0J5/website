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
    │   ├── img
    │   └── svg
    ├── css
    ├── html
    ├── index
    │   ├── index.css
    │   ├── index.html
    │   ├── index.js
    │   └── index.php
    ├── js
    └── php

10 directories, 6 files

```
## Installation et lancement
### Installer PHP
#### Installer PHP sur Arch linux
`sudo pacman -S php php-apache php-mysqli php-json php-curl`
#### Installer PHP sur Ubuntu
`sudo apt-get install php php-apache php-mysql php-json php-curl`
#### Lancer un serveur PHP local
- `php -S localhost:8000 -t src/index`
  - `-t` permet de définir le répertoire racine du serveur (le dossier à servir)
### SQL
#### Installer SQL sous Arch Linux
`sudo pacman -S mariadb`
#### Installer sous Ubuntu
`sudo apt-get install mariadb-server`
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
`sudo systemctl start mariadb.service`