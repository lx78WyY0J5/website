#!/bin/bash
# Test: Valid register - create user

echo "=== test_valid_login_1_register.sh ==="

# Step 1: GET the register page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/register" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=testuser123&password=StrongPass123ThatPass4test!&confirm_password=StrongPass123ThatPass4test!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?