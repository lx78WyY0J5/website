# Agent.md - Rules and Guidelines
- Utiliser le système Français (Jour/mois/année, l'euro, le système métrique, etc)

## Code Style Requirements

### Lecture du ReadMe.md et du code
- Toujours commencer par lire le `README.md` pour comprendre l'arborescence et le contexte du projet
- Lancer une commande `tree` pour ne pas manquer de fichier ou dossier
- Lire les fichiers PHP, HTML, CSS, JS pour comprendre le style existant avant d'écrire du nouveau code
- Si le code ne respecte pas les règles ci-dessous, le corriger avant de terminer
- Vérifier une deuxième fois que toutes les modifications sont bonnes, et n'entraine pas d'effet indésirable
- Vérifier les fautes d'orthographes et les signaler dans `soon.md`

## Gestion des tâches — Dossier `/tasks`

Le projet utilise un dossier `tasks/` à la racine pour suivre l'avancement du travail. Ce dossier contient **trois fichiers Markdown** qui doivent être maintenus à jour **à chaque fin de session** :

### Structure du dossier `tasks/`
```
tasks/
├── done.md   → Tâches terminées et fonctionnelles (historique)
├── now.md    → Tâche en cours OU prochaine tâche immédiate à démarrer
├── soon.md   → Backlog priorisé (ce qui viendra après now.md)
```

### Règles de maintenance (OBLIGATOIRE à chaque fin de session)

1. **`now.md`** : Doit toujours refléter la tâche active. Si une tâche est terminée, la déplacer vers `done.md` et définir la suivante depuis `soon.md`.
2. **`done.md`** : Ajouter chaque tâche complétée avec date, description courte et pointeurs vers les fichiers modifiés.
3. **`soon.md`** : Garder priorisé. Retirer la tâche passée dans `now.md`. Ajouter de nouvelles idées en bas.
4. **`agent.md` (ce fichier)** : Doit aussi être mis à jour à chaque fin de session (arborescence, nouvelles règles, corrections).

### Format attendu dans `now.md`
- **Tâche active** : Nom clair + statut + progression % + plan d'exécution (checklist)
- **Contexte pour l'IA qui reprend** : Structure des fichiers concernés, exemples de code, fichiers à ne pas toucher
- **Prochaine tâche** : Référence vers l'entrée correspondante dans `soon.md`

### Format attendu dans `done.md`
- Sections par domaine (Architecture, Auth, Frontend, Tests, Assets, etc.)
- Pour chaque entrée : description, fichiers concernés, date

### Format attendu dans `soon.md`
- Priorités : 🔴 CRITIQUE / 🟠 IMPORTANT / 🟡 AMÉLIORATIONS / 🟢 NICE TO HAVE
- Chaque entrée : description, exemple concret, pseudo-code / fichiers cibles
- Ordre d'exécution suggéré en tableau à la fin

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

#### Messages en français et formatés en HTML dans le code PHP
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

### ⚠️ RÈGLE ABSOLUE : Demander avant d'agir

**L'IA ne doit JAMAIS** :
- Lancer une commande sans demander confirmation
- Créer un fichier, dossier ou script sans demander
- Modifier un fichier existant sans demander (sauf correction de style évidente)

**L'IA doit TOUJOURS** :
- Proposer l'action et attendre un "oui" explicite
- Expliquer ce que la commande va faire
- Laisser l'utilisateur décider

## Vérification

Avant de considérer une tâche comme terminée :
- [ ] Code conforme aux règles de style (pas de flèche, pas de ? :, if/else clair)
- [ ] Tous les messages utilisateur en français et dans des balises `<p>`
- [ ] Pas de code répété : fonctions créées pour réutilisation
- [ ] Fichiers `tasks/done.md`, `tasks/now.md`, `tasks/soon.md` mis à jour
- [ ] Fichiers `README.md` et `AGENTS.md` mis à jour
- [ ] Site testable sur http://localhost:8000
  - [ ] Si le site n'est pas en ligne, le démarrer via la commande du ReadMe