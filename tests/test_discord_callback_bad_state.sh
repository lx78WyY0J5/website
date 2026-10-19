#!/bin/bash
# Test: Discord callback with wrong state must be rejected

echo "=== test_discord_callback_bad_state.sh ==="

resp=$(curl -s "$BASE_URL/discord/callback?code=x&state=badstate123")

echo "$resp" | grep -q "state OAuth invalide"
exit $?
