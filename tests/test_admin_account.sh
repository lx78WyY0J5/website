#!/bin/bash
# Test: Admin account exists and can login

echo "=== test_admin_account.sh ==="

admin_username=$(grep '^ADMIN_USERNAME=' ".env" | cut -d'=' -f2 | tr -d '"')
admin_password=$(grep '^ADMIN_PASSWORD=' ".env" | cut -d'=' -f2 | tr -d '"')

if [ -z "$admin_username" ] || [ -z "$admin_password" ]; then
    echo "❌ FAIL: ADMIN_USERNAME or ADMIN_PASSWORD not found in .env"
    exit 1
fi

resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=$admin_username&password=$admin_password" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "location: /\|window.location.href = '/'"
exit $?