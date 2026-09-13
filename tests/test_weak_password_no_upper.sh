#!/bin/bash
# Test: Weak password (no uppercase) on /register
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_weak_password_no_upper.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=validpass123!&confirm_password=validpass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "Le mot de passe doit contenir une lettre majuscule"
exit $?