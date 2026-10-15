#!/bin/bash
# Test: Invalid login - wrong password

echo "=== test_invalid_login.sh ==="

# Step 1: GET the login page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/login" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to login with wrong password AND CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/login" \
  -d "csrf_token=$csrf_token&username=testuser123&password=WrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur ou le mot de passe ne correspond pas"
exit $?