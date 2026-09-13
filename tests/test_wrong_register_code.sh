#!/bin/bash
# Test: Wrong register code on /register
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_wrong_register_code.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=StrongPass123!&confirm_password=StrongPass123!&code=wrong" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "Le code de registration est incorrect"
exit $?