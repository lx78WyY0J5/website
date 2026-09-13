#!/bin/bash
# Test: Weak password (no digit) on /register
set -e
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

resp=$(curl -s -X POST "$BASE_URL/register" \
  -d "username=validuser&password=ValidPass!&confirm_password=ValidPass!&code=123456" \
  -H "Content-Type: application/x-www-form-urlencoded")

echo "$resp" | grep -q "Le mot de passe doit contenir un chiffre"