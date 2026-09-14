#!/bin/bash
# Test: Weak password (low entropy) on /register

echo "=== test_weak_password_low_entropy.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=entropyuser&password=ShortPass123!&confirm_password=ShortPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "L'entropie du mot de passe est trop faible"
exit $?