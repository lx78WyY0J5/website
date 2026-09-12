# Agent.md - Rules and Guidelines

## Code Style Requirements

### Lecture du ReadMe.md et du code
1. Toujours commencer par lire le `README.md` pour comprendre l'arborescence et le contexte du projet
2. Lire les fichiers PHP, HTML, CSS, JS pour comprendre le style existant avant d'écrire du nouveau code
3. Si le code ne respecte pas les règles ci-dessous, le corriger avant de terminer

### Style de code requis

#### Lisibilité et clarté
- **Pas de fonctions fléchées** (`=>`) : utiliser des fonctions classiques avec `function name()`
- **Pas de conditions en une ligne** (`? :`) : utiliser des `if / else` complets et explicites
- **Conditions `if / else` claires** : toujours utiliser la structure complète avec accolades `{ }`
- Toujours ouvrir et fermer les blocs avec `{ }`, même pour une seule ligne

#### Exemple de style attendu (NON accepté) :
```php
// ❌ INTERDIIT - fonction fléchée
const getUser = (id) => { return users[id]; };

// ❌ INTERDIIT - condition ternaire
$result = ($cond) ? $a : $b;

// ❌ INTERDIIT - if sur une ligne
if($cond) doSomething();
```

#### Exemple de style accepté :
```php
// ✅ ACCEPTÉ - fonction classique
function getUser($id) {
    global $users;
    return $users[$id];
}

// ✅ ACCEPTÉ - if/else complet
if ($cond) {
    doSomething();
}
else {
    doSomethingElse();
}
```

#### Messages en français et formatés en HTML
- Tous **les commentaires dans le code doivent être en français**
- Tous **les messages affichés à l'utilisateur doivent être en français**
  - Exemple : `<p>Veuillez saisir un mot de passe</p>` (et non "Please enter password")
- **Les messages ne doivent pas se terminer par un point** (`.`)
  - Exemple correct : `"Connecté avec succès"`
  - Exemple incorrect : `"Connecté avec succès."`
- **Chaque message envoyé à l'utilisateur doit être entouré de balises `<p>`**
  - Exemple correct : `echo "<p>Message pour l'utilisateur</p>";`
  - Exemple incorrect : `echo "Message pour l'utilisateur";`

#### Stack technique
- **HTML, CSS, JS, PHP, SQL avec MariaDB**
- Aucune librairie externe n'est autorisée
- Le site tourne en local sur `localhost:8000` avec `php -S localhost:8000 -t ./public/`

#### Simplicité et réutilisabilité
- Code simple et lisible
- **Éviter les répétitions** : favoriser la création de fonctions pour la réutilisation
- Si une fonctionnalité est utilisée ailleurs, la mettre dans un fichier `src/php/` et l'inclure
- Ne pas copier-coller du code : créer une fonction unique

## Arborescence du projet (à jour via commande tree)

```
.
├── logs
│   └── security.log
├── public
│   ├── favicon.ico
│   ├── index.php
│   └── src
│       ├── assets
│       │   ├── font
│       │   │   ├── Pacifico-Regular.ttf
│       │   │   ├── Roboto-Regular.ttf
│       │   │   └── VarelaRound-Regular.ttf
│       │   ├── img
│       │   └── svg
│       ├── css
│       │   ├── article.css
│       │   ├── font.css
│       │   ├── theme.css
│       │   └── style.css
│       ├── includes
│       │   ├── footer
│       │   │   ├── footer.css
│       │   │   ├── footer.php
│       │   │   └── footer.js
│       │   └── header
│       │   ├── header.css
│       │   ├── header.html
│       │   ├── header.js
│       │   └── header.php
│       ├── index
│       │   ├── head.html
│       │   ├── index.css
│       │   ├── index.html
│       │   ├── index.js
│       │   └── index.php
│       ├── pages
│       │   ├── 404
│       │   │   ├── 404.css
│       │   │   ├── 404.html
│       │   │   ├── 404.js
│       │   │   └── head.html
│       │   ├── admin
│       │   │   └── contact
│       │   │   ├── contact.css
│       │   │   ├── contact.html
│       │   │   ├── contact.js
│       │   │   └── head.html
│       │   │   └── index.php
│       │   ├── login
│       │   │   ├── head.html
│       │   │   ├── index.php
│       │   │   ├── login.css
│       │   │   ├── login.html
│       │   │   ├── login.js
│       │   │   └── login.php
│       │   ├── logout
│       │   │   ├── head.html
│       │   │   ├── index.php
│       │   │   ├── logout.css
│       │   │   ├── logout.html
│       │   │   └── logout.php
│       │   ├── password
│       │   │   └── update
│       │   │   ├── head.html
│       │   │   ├── index.php
│       │   │   ├── update.css
│       │   │   ├── update.html
│       │   │   ├── update.js
│       │   │   └── update.php
│       │   └── register
│       │       ├── head.html
│       │       ├── index.php
│       │       ├── register.css
│       │       ├── register.html
│       │       ├── register.js
│       │       └── register.php
│       └── php
           ├── create-db.php
           ├── info.php
           ├── loadEnv.php
           ├── logging.php
           ├── password_entropy.php
           ├── password_validation.php
           └── PDO.php
└── README.md
```

## Mise à jour du README.md

Après avoir créé ou modifié des fichiers :
1. Exécuter la commande `tree ./` dans le terminal
2. Mettre à jour le tableau dans ce fichier `agent.md` avec la nouvelle arborescence
3. Mettre à jour la section "Arborésence" du `README.md`

## Vérification

Avant de considérer une tâche comme terminée :
- [ ] Code conforme aux règles de style (pas de flèche, pas de ? :, if/else clair)
- [ ] Tous les messages utilisateur en français et dans des balises `<p>`
- [ ] Pas de code répété : fonctions créées pour réutilisation
- [ ] README.md et agent.md mis à jour avec la dernière arborescence
- [ ] Site testable sur localhost:8000
  - [ ] Le site n'est pas en ligne, le démarrer via la commande du ReadMe