#!/bin/bash
# Test: Weak password (no lowercase) on /register

echo "=== test_weak_password_no_lower.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=VALIDPASS123!&confirm_password=VALIDPASS123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir une lettre minuscule"
exit $?