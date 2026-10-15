#!/bin/bash
# Test: Invalid login - wrong password

echo "=== test_invalid_login.sh ==="

# Step 1: GET the login page to fetch the CSRF token
page=$(curl -s -X GET "$BASE_URL/login")
csrf_token=$(echo "$page" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to login with wrong password AND CSRF token
resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "csrf_token=$csrf_token&username=testuser123&password=WrongPass123!" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur ou le mot de passe ne correspond pas"
exit $?