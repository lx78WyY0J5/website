#!/bin/bash
# Test: Weak password (no digit) on /register

echo "=== test_weak_password_no_digit.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=ValidPass!&confirm_password=ValidPass!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir un chiffre"
exit $?