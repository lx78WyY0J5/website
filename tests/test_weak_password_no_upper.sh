#!/bin/bash
# Test: Weak password (no uppercase) on /register

echo "=== test_weak_password_no_upper.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=validpass123!&confirm_password=validpass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir une lettre majuscule"
exit $?