# website
## DEPRECATED DOC !
La documentation n'est plus à jour !
- La création de DB peut être faite via script
  - Un create-db .sql
  - ou le fichier PHP pour les env de test (ci - cd)
## Introduction
- Site web en HTML, CSS, PHP, SQL
- Site permettant de stocker différent contenus et apprendre la création de backend en PHP + SQL
## Arborésence
Crée via le package `tree`
  - `sudo pacman -S tree` : Installation
  - `tree ./` : Créer l'arborésence du dossier
```
./repo/website/
├── agent.md
├── install.sh
├── logs
│   ├── php.log
│   └── security.log
├── php.ini
├── public
│   ├── favicon.ico
│   ├── index.php
│   ├── src
│   │   ├── assets
│   │   │   ├── favicon
│   │   │   │   ├── circular
│   │   │   │   │   ├── circular-android-icon-192x192.png
│   │   │   │   │   ├── circular-apple-icon-152x152.png
│   │   │   │   │   ├── circular-apple-icon-180x180.png
│   │   │   │   │   ├── circular-favicon-16x16.png
│   │   │   │   │   ├── circular-favicon-192x192.png
│   │   │   │   │   ├── circular-favicon-32x32.png
│   │   │   │   │   ├── circular-favicon-96x96.png
│   │   │   │   │   ├── circular-favicon.ico
│   │   │   │   │   ├── circular-ms-icon-144x144.png
│   │   │   │   │   ├── circular-ms-icon-150x150.png
│   │   │   │   │   ├── circular-ms-icon-310x310.png
│   │   │   │   │   └── circular-ms-icon-70x70.png
│   │   │   │   ├── rounded
│   │   │   │   │   ├── rounded-android-icon-192x192.png
│   │   │   │   │   ├── rounded-apple-icon-152x152.png
│   │   │   │   │   ├── rounded-apple-icon-180x180.png
│   │   │   │   │   ├── rounded-favicon-16x16.png
│   │   │   │   │   ├── rounded-favicon-192x192.png
│   │   │   │   │   ├── rounded-favicon-32x32.png
│   │   │   │   │   ├── rounded-favicon-96x96.png
│   │   │   │   │   ├── rounded-favicon.ico
│   │   │   │   │   ├── rounded-ms-icon-144x144.png
│   │   │   │   │   ├── rounded-ms-icon-150x150.png
│   │   │   │   │   ├── rounded-ms-icon-310x310.png
│   │   │   │   │   └── rounded-ms-icon-70x70.png
│   │   │   │   └── square
│   │   │   │       ├── square-android-icon-192x192.png
│   │   │   │       ├── square-apple-icon-152x152.png
│   │   │   │       ├── square-apple-icon-180x180.png
│   │   │   │       ├── square-favicon-16x16.png
│   │   │   │       ├── square-favicon-192x192.png
│   │   │   │       ├── square-favicon-32x32.png
│   │   │   │       ├── square-favicon-96x96.png
│   │   │   │       ├── square-favicon.ico
│   │   │   │       ├── square-ms-icon-144x144.png
│   │   │   │       ├── square-ms-icon-150x150.png
│   │   │   │       ├── square-ms-icon-310x310.png
│   │   │   │       └── square-ms-icon-70x70.png
│   │   │   ├── font
│   │   │   │   ├── Pacifico-Regular.ttf
│   │   │   │   ├── Roboto-Regular.ttf
│   │   │   │   └── VarelaRound-Regular.ttf
│   │   │   ├── fruits
│   │   │   │   ├── banana.png
│   │   │   │   ├── black-berry-dark.png
│   │   │   │   ├── black-berry-light.png
│   │   │   │   ├── black-cherry.png
│   │   │   │   ├── coconut.png
│   │   │   │   ├── green-apple.png
│   │   │   │   ├── green-grape.png
│   │   │   │   ├── lemon.png
│   │   │   │   ├── lime.png
│   │   │   │   ├── orange.png
│   │   │   │   ├── peach.png
│   │   │   │   ├── pear.png
│   │   │   │   ├── plum.png
│   │   │   │   ├── raspberry.png
│   │   │   │   ├── red-apple.png
│   │   │   │   ├── red-cherry.png
│   │   │   │   ├── red-grape.png
│   │   │   │   ├── star-fruit.png
│   │   │   │   ├── strawberry.png
│   │   │   │   └── watermelon.png
│   │   │   ├── gif
│   │   │   │   ├── banner.gif
│   │   │   │   ├── dev.gif
│   │   │   │   ├── logo.gif
│   │   │   │   ├── mc-banner.gif
│   │   │   │   ├── portal.gif
│   │   │   │   ├── pp.gif
│   │   │   │   ├── small.gif
│   │   │   │   ├── small-round-25.gif
│   │   │   │   ├── small-round-50.gif
│   │   │   │   └── travolta-lost.gif
│   │   │   ├── image
│   │   │   │   ├── background-games.jpg
│   │   │   │   ├── film
│   │   │   │   │   ├── tt0109830.jpg
│   │   │   │   │   ├── tt0110413.jpg
│   │   │   │   │   ├── tt0110912.jpg
│   │   │   │   │   ├── tt0213149.jpg
│   │   │   │   │   ├── tt0281364.jpg
│   │   │   │   │   ├── tt0434409.jpg
│   │   │   │   │   ├── tt0478087.jpg
│   │   │   │   │   ├── tt0482571.jpg
│   │   │   │   │   ├── tt0988045.jpg
│   │   │   │   │   ├── tt1241317.jpg
│   │   │   │   │   ├── tt1515091.jpg
│   │   │   │   │   ├── tt15398776.jpg
│   │   │   │   │   ├── tt1670345.jpg
│   │   │   │   │   ├── tt1677720.jpg
│   │   │   │   │   ├── tt2084970.jpg
│   │   │   │   │   ├── tt2293138.jpg
│   │   │   │   │   ├── tt3110958.jpg
│   │   │   │   │   ├── tt8323668.jpg
│   │   │   │   │   └── tt9783600.jpg
│   │   │   │   ├── noise-color.png
│   │   │   │   ├── pp.png
│   │   │   │   ├── ShopTitans.webp
│   │   │   │   ├── small.png
│   │   │   │   └── vigenere.png
│   │   │   ├── svg
│   │   │   │   ├── acn.svg
│   │   │   │   ├── ada.svg
│   │   │   │   ├── administrator.svg
│   │   │   │   ├── ai-ml.svg
│   │   │   │   ├── app-gear.svg
│   │   │   │   ├── bitcoin.svg
│   │   │   │   ├── bomb.svg
│   │   │   │   ├── book.svg
│   │   │   │   ├── cables-cable.svg
│   │   │   │   ├── chart-column.svg
│   │   │   │   ├── chart-gear.svg
│   │   │   │   ├── chip-motherboard.svg
│   │   │   │   ├── close.svg
│   │   │   │   ├── computer-configuration.svg
│   │   │   │   ├── console.svg
│   │   │   │   ├── contact.svg
│   │   │   │   ├── cookie.svg
│   │   │   │   ├── copy.svg
│   │   │   │   ├── cpu.svg
│   │   │   │   ├── cross.svg
│   │   │   │   ├── cube.svg
│   │   │   │   ├── developpement.svg
│   │   │   │   ├── dj-turntable-vinyl.svg
│   │   │   │   ├── donation.svg
│   │   │   │   ├── download.svg
│   │   │   │   ├── down.svg
│   │   │   │   ├── draw.svg
│   │   │   │   ├── edit.svg
│   │   │   │   ├── ethereum.svg
│   │   │   │   ├── ethernet.svg
│   │   │   │   ├── file-wired.svg
│   │   │   │   ├── film.svg
│   │   │   │   ├── flag-fr.svg
│   │   │   │   ├── ftp.svg
│   │   │   │   ├── game.svg
│   │   │   │   ├── gpu.svg
│   │   │   │   ├── heart-color.svg
│   │   │   │   ├── heart.svg
│   │   │   │   ├── help-question.svg
│   │   │   │   ├── home.svg
│   │   │   │   ├── install-file.svg
│   │   │   │   ├── internet-cable.svg
│   │   │   │   ├── ip.svg
│   │   │   │   ├── issue-closed.svg
│   │   │   │   ├── issue-reopened.svg
│   │   │   │   ├── issue.svg
│   │   │   │   ├── languages
│   │   │   │   │   ├── css.svg
│   │   │   │   │   ├── dotnet.svg
│   │   │   │   │   ├── html.svg
│   │   │   │   │   ├── java.svg
│   │   │   │   │   ├── js.svg
│   │   │   │   │   ├── markdown.svg
│   │   │   │   │   ├── pdf.svg
│   │   │   │   │   ├── php.svg
│   │   │   │   │   ├── powershell.svg
│   │   │   │   │   ├── rss.svg
│   │   │   │   │   └── sql.svg
│   │   │   │   ├── language.svg
│   │   │   │   ├── langue.svg
│   │   │   │   ├── light.svg
│   │   │   │   ├── link-broken.svg
│   │   │   │   ├── link.svg
│   │   │   │   ├── linux.svg
│   │   │   │   ├── loading.svg
│   │   │   │   ├── magnifier-plus.svg
│   │   │   │   ├── mail.svg
│   │   │   │   ├── maintenance.svg
│   │   │   │   ├── map-pin.svg
│   │   │   │   ├── matrix.svg
│   │   │   │   ├── menu.svg
│   │   │   │   ├── moon.svg
│   │   │   │   ├── music.svg
│   │   │   │   ├── music-white.svg
│   │   │   │   ├── network.svg
│   │   │   │   ├── new.svg
│   │   │   │   ├── note.svg
│   │   │   │   ├── old-man.svg
│   │   │   │   ├── password.svg
│   │   │   │   ├── people.svg
│   │   │   │   ├── picture.svg
│   │   │   │   ├── playlist.svg
│   │   │   │   ├── power-bank.svg
│   │   │   │   ├── ram-storage.svg
│   │   │   │   ├── school.svg
│   │   │   │   ├── scroll-text.svg
│   │   │   │   ├── security.svg
│   │   │   │   ├── server.svg
│   │   │   │   ├── settings.svg
│   │   │   │   ├── star-half.svg
│   │   │   │   ├── star.svg
│   │   │   │   ├── storage.svg
│   │   │   │   ├── sun.svg
│   │   │   │   ├── svg.svg
│   │   │   │   ├── telephone.svg
│   │   │   │   ├── thief.svg
│   │   │   │   ├── thumbs-down.svg
│   │   │   │   ├── tools.svg
│   │   │   │   ├── trademark
│   │   │   │   │   ├── apache.svg
│   │   │   │   │   ├── battlenet.svg
│   │   │   │   │   ├── cisco.svg
│   │   │   │   │   ├── discord.svg
│   │   │   │   │   ├── docker-16.svg
│   │   │   │   │   ├── docker.svg
│   │   │   │   │   ├── epic-games.svg
│   │   │   │   │   ├── github.svg
│   │   │   │   │   ├── git.svg
│   │   │   │   │   ├── google-drive.svg
│   │   │   │   │   ├── google-play.svg
│   │   │   │   │   ├── instagram.svg
│   │   │   │   │   ├── minecraft.svg
│   │   │   │   │   ├── organisation.svg
│   │   │   │   │   ├── repo.svg
│   │   │   │   │   ├── steam.svg
│   │   │   │   │   ├── tails.svg
│   │   │   │   │   ├── tor.svg
│   │   │   │   │   ├── twitch.svg
│   │   │   │   │   ├── twitter.svg
│   │   │   │   │   ├── vscode.svg
│   │   │   │   │   ├── windows.svg
│   │   │   │   │   ├── youtube-music.svg
│   │   │   │   │   └── youtube.svg
│   │   │   │   ├── tv2.svg
│   │   │   │   ├── tv.svg
│   │   │   │   ├── update.svg
│   │   │   │   ├── up.svg
│   │   │   │   ├── usb-flash-drive.svg
│   │   │   │   ├── usdt.svg
│   │   │   │   ├── verify.svg
│   │   │   │   ├── virus.svg
│   │   │   │   ├── warning.svg
│   │   │   │   ├── worker-running-with-suitcase.svg
│   │   │   │   ├── writer-write-blogger-work-at-desk.svg
│   │   │   │   ├── xmr.svg
│   │   │   │   ├── xrp.svg
│   │   │   │   └── zip.svg
│   │   │   └── txt
│   │   │       ├── books
│   │   │       ├── frames
│   │   │       │   ├── earth.json
│   │   │       │   ├── parrot.json
│   │   │       │   └── test.txt
│   │   │       └── GPG
│   │   │           └── pub.asc
│   │   ├── css
│   │   │   ├── article.css
│   │   │   ├── font.css
│   │   │   ├── scrollbar.css
│   │   │   ├── scrollPercentage.css
│   │   │   ├── style.css
│   │   │   └── theme.css
│   │   ├── includes
│   │   │   ├── anchor.css
│   │   │   ├── anchor.php
│   │   │   ├── footer
│   │   │   │   ├── footer.css
│   │   │   │   ├── footer.js
│   │   │   │   └── footer.php
│   │   │   └── header
│   │   │       ├── header.css
│   │   │       ├── header.js
│   │   │       ├── header.php
│   │   │       └── search
│   │   │           ├── navlink.css
│   │   │           ├── navlink.php
│   │   │           ├── searchbar.css
│   │   │           ├── searchbar.html
│   │   │           ├── searchbar.js
│   │   │           └── searchbar-list.js
│   │   ├── index
│   │   │   ├── head.html
│   │   │   ├── index.css
│   │   │   ├── index.html
│   │   │   ├── index.js
│   │   │   ├── index.php
│   │   │   └── welcome-user.css
│   │   ├── js
│   │   │   ├── gather.js
│   │   │   ├── scrollPercentage.js
│   │   │   └── theme.js
│   │   ├── pages
│   │   │   ├── 404
│   │   │   │   ├── 404.css
│   │   │   │   ├── 404-custom.html
│   │   │   │   ├── 404.html
│   │   │   │   ├── 404.js
│   │   │   │   ├── head.html
│   │   │   │   └── index.php
│   │   │   ├── admin
│   │   │   │   ├── contact
│   │   │   │   │   ├── contact.css
│   │   │   │   │   ├── contact.html
│   │   │   │   │   ├── contact.js
│   │   │   │   │   ├── head.html
│   │   │   │   │   └── index.php
│   │   │   │   └── donation
│   │   │   │       ├── donation.css
│   │   │   │       ├── donation.html
│   │   │   │       ├── donation.js
│   │   │   │       ├── head.html
│   │   │   │       └── index.php
│   │   │   ├── info
│   │   │   │   ├── head.html
│   │   │   │   ├── index.php
│   │   │   │   ├── info.css
│   │   │   │   ├── info.html
│   │   │   │   ├── info.js
│   │   │   │   └── info.php
│   │   │   ├── login
│   │   │   │   ├── head.html
│   │   │   │   ├── index.php
│   │   │   │   ├── login.css
│   │   │   │   ├── login.html
│   │   │   │   ├── login.js
│   │   │   │   └── login.php
│   │   │   ├── logout
│   │   │   │   ├── head.html
│   │   │   │   ├── index.php
│   │   │   │   ├── logout.css
│   │   │   │   ├── logout.html
│   │   │   │   ├── logout.js
│   │   │   │   └── logout.php
│   │   │   ├── password
│   │   │   │   └── update
│   │   │   │       ├── head.html
│   │   │   │       ├── index.php
│   │   │   │       ├── update.css
│   │   │   │       ├── update.html
│   │   │   │       ├── update.js
│   │   │   │       └── update.php
│   │   │   ├── profile
│   │   │   │   └── picture
│   │   │   │       ├── head.html
│   │   │   │       ├── index.php
│   │   │   │       ├── picture.css
│   │   │   │       ├── picture.html
│   │   │   │       ├── picture.js
│   │   │   │       └── picture.php
│   │   │   └── register
│   │   │       ├── head.html
│   │   │       ├── index.php
│   │   │       ├── register.css
│   │   │       ├── register.html
│   │   │       ├── register.js
│   │   │       └── register.php
│   │   ├── php
│   │   │   ├── create-db.php
│   │   │   ├── loadEnv.php
│   │   │   ├── logging.php
│   │   │   ├── password_entropy.php
│   │   │   ├── password_validation.php
│   │   │   ├── PDO.php
│   │   │   ├── rate_limiter.php
│   │   │   └── view_counter.php
│   │   └── sql
│   │       └── init-db.sql
│   └── uploads
│       └── profile_pictures
│           ├── 20
│           │   └── pp.gif
│           └── 21
├── README.md
└── tests
    ├── run-all-tests.sh
    ├── test_invalid_login.sh
    ├── test_missing_register_code.sh
    ├── test_short_username.sh
    ├── test_sql_injection_login.sh
    ├── test_sql_injection_register.sh
    ├── test_valid_login_1_register.sh
    ├── test_valid_login_2_login.sh
    ├── test_view_counter.sh
    ├── test_weak_password_low_entropy.sh
    ├── test_weak_password_no_digit.sh
    ├── test_weak_password_no_lower.sh
    ├── test_weak_password_no_special.sh
    ├── test_weak_password_no_upper.sh
    ├── test_weak_password_short.sh
    └── test_wrong_register_code.sh

48 directories, 348 files

```
## Installation et lancement automatique
[install.sh](/install.sh)
## Installation et lancement
### PHP
#### Installer PHP sur Termux
`pkg install php`
#### Installer PHP sur Arch linux
- `sudo pacman -S php`
- `sudo sed -i 's/;extension=pdo_mysql/extension=pdo_mysql/g' /etc/php/php.ini` pour activer PDO_MySQL
- `sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 10M/g' /etc/php/php.ini` pour la limite de taille de fichier
- `sudo sed -i 's/post_max_size = 8M/post_max_size = 10M/g' /etc/php/php.ini` pour la limite de taille d'upload
#### Installer PHP sur Ubuntu (UNTESTED)
`sudo apt-get install php`
#### Lancer un serveur PHP local
- Pour lancer le serveur PHP ; `php -S localhost:8000 -t ./public/`
- Ou avec les logs : `php -S localhost:8000 -t ./repo/website/public/ &>> ./repo/website/logs/php.log`
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
##### Initialiser MariaDB sous Termux
Initialiser la base de données avec `mysql_install_db`
##### Lancer le service sous Termux
- Une fois installé, démarrez le serveur en arrière-plan avec la commande `mysqld_safe &`
  - Vous pouvez ensuite vous connecter au serveur en exécutant `mysql -u root` pour commencer à gérer vos bases de données
#### Lancer MariaDB sous Linux
##### Initialiser MariaDB sous linux
Initialiser la base de données avec `sudo mysql_install_db --user=mysql --basedir=/usr --datadir=/var/lib/mysql`
##### Lancer le service MariaDB sous Linux
```bash
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
#### Créer la BDD
- Télécharger le fichier `.sql` : [init-db.sql](/public/src/sql/init-db.sql)
- Lancez `sudo mysql -u root -p < /path/to/your/file.sql`
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