# Lecture des règles

- Lancer une commande `tree` pour ne pas manquer de fichier ou dossier
- Lire les fichiers PHP, HTML, CSS, JS pour comprendre le style existant avant d'écrire du nouveau code
- Si le code ne respecte pas les règles ci-dessous, le corriger avant de terminer
- Vérifier une deuxième fois que toutes les modifications sont bonnes, et n'entraine pas d'effet indésirable
- **Pas de fonctions fléchées** (`=>`) : utiliser des fonctions classiques avec `function name()`
- **Pas de conditions en une ligne** (`? :`) : utiliser des `if / else` complets et explicites
- **Conditions `if / else` claires** : toujours utiliser la structure complète avec accolades `{ }`
- Toujours ouvrir et fermer les blocs avec `{ }`, même pour une seule ligne
- **Éviter les répétitions** : favoriser la création de fonctions pour la réutilisation
- Code simple et lisible
- Si une fonctionnalité est utilisée ailleurs, la mettre dans un fichier `./public/src/php/` et l'inclure
- **HTML, CSS, JS, PHP, SQL avec MariaDB**
- Aucune librairie externe n'est autorisée
- Le site tourne en local sur `localhost:8000`
  - Installé via le script `./install.sh` (Installation de PHP, SQL, .env, création de la base de donnée, ...)
  - Puis lancé via le script `./start.sh` (Lancement de PHP, SQL, et du site)
  - En cas de test en local ayant modifié la base de donnée ; supprimer la base de donnée via `sudo mysql` puis `USE <DB_NAME>; DROP TABLE <TABLE_NAME>;`, et relancer le script d'installation puis de lancement
- Pour créer du code, suis comment les anciens exemples sont crées, consulte le `tree` et les fichiers car il y aura des chances que ça existe déjà