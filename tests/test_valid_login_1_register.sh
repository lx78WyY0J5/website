#!/bin/bash
# Test: Valid register - create user

echo "=== test_valid_login_1_register.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=testuser123&password=StrongPass123ThatPass4test!&confirm_password=StrongPass123ThatPass4test!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?