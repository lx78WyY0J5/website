#!/bin/bash
# Test: Weak password (no special char) on /register

echo "=== test_weak_password_no_special.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=ValidPass123&confirm_password=ValidPass123&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir un caractère spécial"
exit $?