#!/bin/bash
# Tests d'intégration de la fusion des comptes Discord / classiques.

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
php "$SCRIPT_DIR/test_user_merge.php"
