#!/bin/bash
# Test: SQL Injection on /register - should be rejected (invalid chars or duplicate)
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_sql_injection_register.sh ==="

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=test' OR '1'='1&password=StrongPass123!&confirm_password=StrongPass123!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Ce nom d'utilisateur est déjà pris\|Le nom d'utilisateur ne peut contenir que des lettres, chiffres et underscores"
exit $?