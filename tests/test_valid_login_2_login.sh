#!/bin/bash
# Test: Valid login - after user created
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_valid_login_2_login.sh ==="

resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=testuser123&password=StrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?