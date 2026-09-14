#!/bin/bash
# Test: Missing register code on /register

echo "=== test_missing_register_code.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=StrongPass123!&confirm_password=StrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Veuillez saisir le code de registration"
exit $?