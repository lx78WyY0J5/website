#!/bin/bash
# Test: View counter increments and displays in footer

echo "=== test_view_counter.sh ==="

# Send a request to increment the counter
curl -s "$BASE_URL/" > /dev/null

# Fetch the page and check that the counter is NOT 0
resp=$(curl -s "$BASE_URL/")

if echo "$resp" | grep -q "<p>Vues totales du site : <strong>0</strong></p>"; then
    exit 1
fi

# Verify the counter shows a number > 0
if echo "$resp" | grep -qE "<p>Vues totales du site : <strong>[1-9][0-9]*</strong></p>"; then
    exit 0
else
    exit 1
fi