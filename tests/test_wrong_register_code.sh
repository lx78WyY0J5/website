#!/bin/bash
# Test: Wrong register code on /register

echo "=== test_wrong_register_code.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=StrongPass123!&confirm_password=StrongPass123!&code=wrong" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le code de registration est incorrect"
exit $?