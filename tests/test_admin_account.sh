#!/bin/bash
# Test: Admin account exists and can login

echo "=== test_admin_account.sh ==="

# Step 1: GET the login page to fetch the CSRF token and save cookies
csrf_token=$(curl -s -c /tmp/cookies.txt -L -X GET "$BASE_URL/login" | grep -o 'name="csrf_token" value="[^"]*"' | sed 's/.*value="//' | sed 's/"//')

admin_username=$(grep '^ADMIN_USERNAME=' ".env" | cut -d'=' -f2 | tr -d '"')
admin_password=$(grep '^ADMIN_PASSWORD=' ".env" | cut -d'=' -f2 | tr -d '"')

if [ -z "$admin_username" ] || [ -z "$admin_password" ]; then
    echo "❌ FAIL: ADMIN_USERNAME or ADMIN_PASSWORD not found in .env"
    exit 1
fi

# Step 2: POST to login with CSRF token, using saved cookies
resp=$(curl -s -b /tmp/cookies.txt -X POST "$BASE_URL/login" \
  -d "csrf_token=$csrf_token&username=$admin_username&password=$admin_password" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?