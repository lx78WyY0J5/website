#!/bin/bash
# Runs all test scripts in tests/

export BASE_URL="${BASE_URL:-http://127.0.0.1:8000}"

PASS=0
FAIL=0

for test in tests/test_*.sh; do
  if bash "$test"; then
    echo "✅ PASS: $(basename "$test" .sh)"
    ((PASS++))
  else
    echo "❌ FAIL: $(basename "$test" .sh)"
    ((FAIL++))
  fi
  echo
done

echo "==================="
echo "Results: $PASS passed, $FAIL failed"
exit $FAIL