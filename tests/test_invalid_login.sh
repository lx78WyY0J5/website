#!/bin/bash
# Test: Invalid login - wrong password
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_invalid_login.sh ==="

resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=testuser123&password=WrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur ou le mot de passe ne correspond pas"
exit $?