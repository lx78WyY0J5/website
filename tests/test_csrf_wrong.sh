#!/bin/bash
# Test: Wrong CSRF token on /register - should show "Erreur de sécurité : token CSRF invalide"

echo "=== test_csrf_wrong.sh ==="

# Use default BASE_URL if not set
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

# Step 1: GET the register page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/register" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

# Step 2: POST to register with WRONG CSRF token (should fail)
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/register" \
  -d "csrf_token=wrongtoken123&username=validuser&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

# Check for CSRF error message
if echo "$resp" | grep -q "Erreur de sécurité : token CSRF invalide"; then
    echo "✅ PASS: CSRF error detected with wrong token"
    exit 0
else
    echo "❌ FAIL: CSRF error NOT detected with wrong token"
    exit 1
fi