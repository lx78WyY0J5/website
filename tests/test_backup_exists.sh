#!/bin/bash
# Test: Check if backup was created at startup

echo "=== test_backup_exists.sh ==="

BACKUP_DIR="./backup"

if [ ! -d "$BACKUP_DIR" ]; then
    echo "FAIL: Backup directory $BACKUP_DIR does not exist"
    exit 1
fi

BACKUP_FILES=$(find "$BACKUP_DIR" -name "mariadb_backup_*.tar.gz" -type f 2>/dev/null | wc -l)

if [ "$BACKUP_FILES" -eq 0 ]; then
    echo "FAIL: No backup files found in $BACKUP_DIR"
    exit 1
fi

LATEST_BACKUP=$(find "$BACKUP_DIR" -name "mariadb_backup_*.tar.gz" -type f -printf "%T@ %p\n" 2>/dev/null | sort -n | tail -1 | cut -d' ' -f2-)

if [ -z "$LATEST_BACKUP" ]; then
    echo "FAIL: Could not determine latest backup"
    exit 1
fi

if [ ! -f "$LATEST_BACKUP" ]; then
    echo "FAIL: Latest backup file does not exist: $LATEST_BACKUP"
    exit 1
fi

FILE_SIZE=$(stat -c%s "$LATEST_BACKUP" 2>/dev/null || stat -f%z "$LATEST_BACKUP" 2>/dev/null)

if [ "$FILE_SIZE" -eq 0 ]; then
    echo "FAIL: Backup file is empty: $LATEST_BACKUP"
    exit 1
fi

echo "PASS: Backup exists at $LATEST_BACKUP (size: $FILE_SIZE bytes)"
exit 0