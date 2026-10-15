#!/bin/bash
# Test: Missing register code on /register

echo "=== test_missing_register_code.sh ==="

# Step 1: GET the register page to fetch the CSRF token
page=$(curl -s -X GET "$BASE_URL/register")
csrf_token=$(echo "$page" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with missing code AND CSRF token
resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=validuser&password=StrongPass123!&confirm_password=StrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Veuillez saisir le code de registration"
exit $?