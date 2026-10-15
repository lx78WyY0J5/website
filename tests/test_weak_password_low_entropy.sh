#!/bin/bash
# Test: Weak password (low entropy) on /register

echo "=== test_weak_password_low_entropy.sh ==="

# Step 1: GET the register page to fetch the CSRF token
page=$(curl -s -X GET "$BASE_URL/register")
csrf_token=$(echo "$page" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with low entropy password AND CSRF token
resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=entropyuser&password=ShortPass123!&confirm_password=ShortPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "L'entropie du mot de passe est trop faible"
exit $?