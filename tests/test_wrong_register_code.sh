#!/bin/bash
# Test: Wrong register code on /register

echo "=== test_wrong_register_code.sh ==="

# Step 1: GET the register page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/register" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with wrong code AND CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=validuser&password=StrongPass123!&confirm_password=StrongPass123!&code=wrong" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le code de registration est incorrect"
exit $?