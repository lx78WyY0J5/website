#!/bin/bash
# Test: Valid register - create user
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_valid_login_1_register.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=testuser123&password=StrongPass123ThatPass4test!&confirm_password=StrongPass123ThatPass4test!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?