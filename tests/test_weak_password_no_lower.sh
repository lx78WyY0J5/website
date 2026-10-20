#!/bin/bash
# Test: Weak password (no lowercase) on /register

echo "=== test_weak_password_no_lower.sh ==="

# Step 1: GET the register page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/register" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with password lacking lowercase AND CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=validuser&password=VALIDPASS123!&confirm_password=VALIDPASS123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir une lettre minuscule"
exit $?