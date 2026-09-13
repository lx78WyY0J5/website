#!/bin/bash
# Runs all test scripts in tests/
set -e

PASS=0
FAIL=0

for test in tests/test_*.sh; do
  name=$(basename "$test" .sh)
  echo "=== $name ==="
  if bash "$test"; then
    echo "✅ PASS: $name"
    ((PASS++))
  else
    echo "❌ FAIL: $name"
    ((FAIL++))
  fi
  echo
done

echo "==================="
echo "Results: $PASS passed, $FAIL failed"
exit $FAIL