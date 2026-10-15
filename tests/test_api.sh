#!/bin/bash
# Test: API /api returns OK

echo "=== test_api.sh ==="

# Send a request to /api and check that it returns "OK"
resp=$(curl -s "$BASE_URL/api")

if echo "$resp" | grep -q "OK"; then
exit $?