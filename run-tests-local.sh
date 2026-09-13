#!/bin/bash
# Run tests locally (Arch Linux) - replicates CI/CD
set -e

echo "=== Local CI/CD Test Runner ==="

# Check if running on Arch
if ! command -v pacman &> /dev/null; then
    echo "This script is for Arch Linux. For other distros, adapt package manager commands."
    exit 1
fi

# Install dependencies if needed
if ! command -v php &> /dev/null; then
    echo "Installing PHP..."
    sudo pacman -S --noconfirm php php-gd php-intl
fi

if ! command -v mariadb &> /dev/null; then
    echo "Installing MariaDB..."
    sudo pacman -S --noconfirm mariadb
    sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql 2>/dev/null || true
    sudo systemctl start mariadb
    sudo systemctl enable mariadb
fi

# Configure PHP
PHP_INI="/etc/php/php.ini"
if [ -f php.ini ]; then
    echo "Copying php.ini..."
    sudo cp php.ini "$PHP_INI"
fi

# Start MariaDB if not running
if ! systemctl is-active --quiet mariadb; then
    echo "Starting MariaDB..."
    sudo systemctl start mariadb
fi

# Wait for MariaDB
echo "Waiting for MariaDB..."
for i in {1..30}; do
    mysqladmin ping -h 127.0.0.1 -u root --silent 2>/dev/null && break
    sleep 1
done

# Setup database
echo "Setting up database..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS mydb;"
mysql -u root -e "CREATE USER IF NOT EXISTS 'webuser'@'localhost' IDENTIFIED BY 'strongpassword';"
mysql -u root -e "GRANT ALL PRIVILEGES ON mydb.* TO 'webuser'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# Run init script
FORCE_DB_INIT=true php public/src/php/create-db.php

# Start PHP server
echo "Starting PHP server..."
cd public
php -S 127.0.0.1:8000 -t . > /tmp/php-server.log 2>&1 &
PHP_PID=$!
cd ..
sleep 3

# Run tests
echo "Running tests..."
BASE_URL=http://127.0.0.1:8000 bash tests/run-all-tests.sh

# Cleanup
kill $PHP_PID 2>/dev/null || true
echo "Done!"