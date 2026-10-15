#!/bin/bash
# Test: SQL Injection on /register - should be rejected (invalid chars or duplicate)

echo "=== test_sql_injection_register.sh ==="

# Step 1: GET the register page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/register" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with SQL injection AND CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/register" \
  -d "csrf_token=$csrf_token&username=test' OR '1'='1&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Ce nom d'utilisateur est déjà pris\|Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores"
exit $?