#!/bin/bash
# Test: Short username on /register - should require at least 6 chars

echo "=== test_short_username.sh ==="

# Step 1: GET the register page to fetch the CSRF token
page=$(curl -s -X GET "$BASE_URL/register")
csrf_token=$(echo "$page" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with short username AND CSRF token
resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=ab&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur doit comporter au moins 6 caractères"
exit $?