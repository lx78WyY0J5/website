#!/bin/bash
# Test: Short username on /register - should require at least 6 chars

echo "=== test_short_username.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=ab&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur doit comporter au moins 6 caractères"
exit $?