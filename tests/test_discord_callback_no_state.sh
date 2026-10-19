#!/bin/bash
# Test: Discord callback without state must be rejected

echo "=== test_discord_callback_no_state.sh ==="

resp=$(curl -s "$BASE_URL/discord/callback?code=x")

echo "$resp" | grep -q "state OAuth invalide"
exit $?
