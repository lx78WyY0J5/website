#!/bin/bash
# Test: Rate limiting - send 30 curl requests and check for HTTP 429
# Test name is `test_z` to run in last (alphabetical order)

echo "=== test_z_429.sh ==="

# Send 30 curl requests, grepping after each for HTTP 429
found=0
for i in $(seq 1 30); do
    resp=$(curl -s "$BASE_URL/login")
    # Grep the result right after curl for "HTTP 429"
    if echo "$resp" | grep -q "HTTP 429"; then
        found=1
        break
    fi
done

if [ "$found" -eq 1 ]; then
    echo "✅ PASS: HTTP 429 detected after some requests"
else
    echo "❌ FAIL: HTTP 429 not detected after 30 requests"
fi

exit $?