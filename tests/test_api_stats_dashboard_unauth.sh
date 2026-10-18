#!/bin/bash

echo "=== test_api_stats_dashboard_unauth.sh ==="

# Send a request to /api/stats-dashboard without authentication
resp=$(curl -s "$BASE_URL/api/stats-dashboard")

echo "$resp" | grep -q "Vous devez vous connecter en tant qu'administrateur"
exit $?