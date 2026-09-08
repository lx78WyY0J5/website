# website
## Introduction
- Site web en HTML, CSS, PHP, SQL
- Site permettant de stocker différent contenus et apprendre la création de backend en PHP + SQL
## Arborésence
Crée via le package `tree`
  - `sudo pacman -S tree` : Installation
  - `tree ./` : Créer l'arborésence du dossier
```
.
├── public
│   ├── index.php
│   └── src
│       ├── assets
│       │   ├── img
│       │   └── svg
│       ├── includes
│       │   ├── footer
│       │   │   ├── footer.css
│       │   │   ├── footer.html
│       │   │   └── footer.js
│       │   └── header
│       │       ├── header.css
│       │       ├── header.html
│       │       └── header.js
│       ├── index
│       │   ├── head.html
│       │   ├── index.css
│       │   ├── index.html
│       │   ├── index.js
│       │   └── index.php
│       ├── js
│       ├── pages
│       │   ├── 404
│       │   │   ├── 404.css
│       │   │   ├── 404.html
│       │   │   ├── 404.js
│       │   │   ├── head.html
│       │   │   └── index.php
│       │   └── admin
│       │       └── contact
│       │           ├── contact.css
│       │           ├── contact.html
│       │           ├── contact.js
│       │           ├── head.html
│       │           └── index.php
│       └── php
│           ├── create-db.php
│           ├── info.php
│           ├── loadEnv.php
│           └── PDO.php
└── README.md

16 directories, 27 files

```
## Installation et lancement
### PHP
#### Installer PHP sur Termux
`pkg install php`
#### Installer PHP sur Arch linux
- `sudo pacman -S php`
- `sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/g' /etc/php/php.ini` pour activer PDO_MySQL
#### Installer PHP sur Ubuntu (UNTESTED)
`sudo apt-get install php`
#### Lancer un serveur PHP local
- `php -S localhost:8000 -t ./public/`
  - `-t` permet de définir le répertoire racine du serveur (le dossier à servir)
    - Seuls les fichiers sous ce dossier sont accessibles via l'URL
    - Le code PHP peut néanmoins `include`/`require` des fichiers en dehors
    - En PHP, ce chemin absolu vers le système de fichiers est accessible via la variable superglobale `$_SERVER['DOCUMENT_ROOT']`
    - Cela permet aux scripts de construire des chemins absolus portables pour inclure des fichiers ou accéder à des ressources, sans avoir à coder en dur le chemin spécifique à chaque hébergeur (par exemple, `/var/www/html` ou `C:\inetpub\wwwroot`)
- Naviguez en suite vers [localhost :8000](http://localhost:8000)
### SQL
#### Installer SQL sous Arch Linux
`sudo pacman -S mariadb`
#### Installer sous Ubuntu (UNTESTED)
`sudo apt-get install mariadb-server`
#### Installer sous Termux
`pkg install mariadb`
#### Lancer MariaDB sous Termux
- Initialiser la base de données avec `mysql_install_db`
- Une fois installé, démarrez le serveur en arrière-plan avec la commande `mysqld_safe &`
  - Vous pouvez ensuite vous connecter au serveur en exécutant `mysql -u root` pour commencer à gérer vos bases de données
#### Lancer MariaDB sous Linux
```bash
sudo mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
sudo systemctl start mariadb.service
sudo systemctl enable mariadb.service
```
#### Configurer un utilisateur et mot de passe
- Lancez `sudo mysql`
- Puis lancez ;
```bash
CREATE USER 'webuser'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON *.* TO 'webuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```
#### Créer la base de données
- Lancez `sudo mysql`
- Puis lancez ;
```bash
CREATE DATABASE mydb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON mydb.* TO 'webuser'@'localhost';
FLUSH PRIVILEGES;
```
#### Créer la table 'users'
- Lancez `sudo mysql`
- Puis lancez ;
```bash
USE mydb;
CREATE TABLE users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```
## Lancer PHP et MariaDB sur Termux
```
cd ./website ;
nohup php -S localhost:8000 -t ./public/ &
nohup mysqld_safe &
```
### Lancer Code-Server et PHP et MariaDB sur Termux
```
cd ./website ;
nohup php -S localhost:8000 -t ./public/ &
nohup mysqld_safe &

nohup code-server --auth none &
```