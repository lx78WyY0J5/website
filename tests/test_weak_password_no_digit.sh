#!/bin/bash
# Test: Weak password (no digit) on /register

echo "=== test_weak_password_no_digit.sh ==="

# Step 1: GET the register page to fetch the CSRF token
page=$(curl -s -X GET "$BASE_URL/register")
csrf_token=$(echo "$page" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with password lacking digit AND CSRF token
resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=validuser&password=ValidPass!&confirm_password=ValidPass!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir un chiffre"
exit $?