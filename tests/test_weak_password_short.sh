#!/bin/bash
# Test: Weak password (too short) on /register
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_weak_password_short.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=Short1!&confirm_password=Short1!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit comporter au moins 12 caractères"
exit $?