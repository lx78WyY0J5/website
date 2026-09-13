#!/bin/bash
# Test: Valid login flow - register then login
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_valid_login.sh ==="

# Register a user first
curl -s -X POST "$BASE_URL/register" \
  -d "username=testuser123&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded" > /dev/null

# Then try to login
resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=testuser123&password=StrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?