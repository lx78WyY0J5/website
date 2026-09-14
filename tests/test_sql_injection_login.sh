#!/bin/bash
# Test: SQL Injection on /login - should be rejected with "ne correspond pas"

echo "=== test_sql_injection_login.sh ==="

resp=$(curl -s -X POST "$BASE_URL/login" \
  -d "username=admin' OR '1'='1&password=anything" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le nom d'utilisateur ou le mot de passe ne correspond pas"
exit $?