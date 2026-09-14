#!/bin/bash
# Test: View counter increments and displays in footer
BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

echo "=== test_view_counter.sh ==="

# Send a request to increment the counter
curl -s "$BASE_URL/" > /dev/null

# Fetch the page and check that the counter is NOT 0
resp=$(curl -s "$BASE_URL/")

# Check that "<p>Vues totales du site : <strong>0</strong></p>" is NOT present
# (meaning counter has incremented to at least 1)
if echo "$resp" | grep -q "<p>Vues totales du site : <strong>0</strong></p>"; then
    echo "FAIL: View counter shows 0 (not incrementing)"
    exit 1
fi

# Verify the counter shows a number > 0
if echo "$resp" | grep -qE "<p>Vues totales du site : <strong>[1-9][0-9]*</strong></p>"; then
    exit 0
else
    exit 1
fi