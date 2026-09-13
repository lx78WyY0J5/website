#!/bin/bash
# Test: SQL Injection on /login - should be rejected with "ne correspond pas"
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_sql_injection_login.sh ==="

resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=admin' OR '1'='1&password=anything" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "Response: $resp"

echo "$resp" | grep -q "Le nom d'utilisateur ou le mot de passe ne correspond pas"
exit $?