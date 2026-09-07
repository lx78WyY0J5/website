# Agent

## Purpose
The agent is responsible for automating file operations within the repository.

### Key Behaviour
- Whenever a task creates or deletes files, **the agent must update the project tree** by running `tree ./` and append the output to the end of the task log.
- The agent should preserve existing content and only add the new tree representation.
- If the task involves moving or renaming files, treat it as a creation/deletion pair and also run `tree ./`.

## Usage Example
```bash
# Create a new PHP file
agent create src/php/new.php
# The agent will output the updated tree after creating the file
```

## Result example
This example contain the whole chapter seen in the markdown file inside a code block (and the result of the command `tree ./` inside code block )
```
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
```
---

**Note:** This file is used by the automation tooling; do not edit manually unless you understand its effect on task logs.
