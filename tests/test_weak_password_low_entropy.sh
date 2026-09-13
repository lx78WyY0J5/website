#!/bin/bash
# Test: Weak password (low entropy) on /register
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_weak_password_low_entropy.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=entropyuser&password=ShortPass123!&confirm_password=ShortPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "L'entropie du mot de passe est trop faible"
exit $?