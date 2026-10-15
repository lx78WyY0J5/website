# Lecture des règles
- Lancer une commande `tree` pour ne pas manquer de fichier ou dossier
- Lire les fichiers PHP, HTML, CSS, JS pour comprendre le style existant avant d'écrire du nouveau code
- Si le code ne respecte pas les règles ci-dessous, le corriger avant de terminer
- Vérifier une deuxième fois que toutes les modifications sont bonnes, et n'entraine pas d'effet indésirable
- Vérifier les fautes d'orthographes et les signaler dans `soon.md`
- Fichiers `tasks/done.md`, `tasks/now.md`, `tasks/soon.md`, doit être mis à jour en fin de session

# Lisibilité et clarté
- **Pas de fonctions fléchées** (`=>`) : utiliser des fonctions classiques avec `function name()`
- **Pas de conditions en une ligne** (`? :`) : utiliser des `if / else` complets et explicites
- **Conditions `if / else` claires** : toujours utiliser la structure complète avec accolades `{ }`
- Toujours ouvrir et fermer les blocs avec `{ }`, même pour une seule ligne

## Exemple de style attendu (NON accepté) :
```php
// ❌ INTERDIIT - fonction fléchée
const getUser = (id) => { return users[id]; };

// ❌ INTERDIIT - condition ternaire
$result = ($cond) ? $a : $b;

// ❌ INTERDIIT - if sur une ligne
if($cond) doSomething();
```

## Exemple de style accepté :
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

# Stack technique
- **HTML, CSS, JS, PHP, SQL avec MariaDB**
- Aucune librairie externe n'est autorisée
- Le site tourne en local sur `localhost:8000` avec `php -S localhost:8000 -t ./public/`

# Simplicité et réutilisabilité
- Code simple et lisible
- **Éviter les répétitions** : favoriser la création de fonctions pour la réutilisation
- Si une fonctionnalité est utilisée ailleurs, la mettre dans un fichier `src/php/` et l'inclure
- Ne pas copier-coller du code : créer une fonction unique