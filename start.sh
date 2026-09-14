#!/bin/bash
set -e

# Detect OS
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS=$ID
    elif [ -f /data/data/com.termux/files/usr/bin/bash ]; then
        OS="termux"
    else
        echo "Unsupported OS"
        exit 1
    fi
    echo "Detected OS: $OS"
}

# Start MariaDB if not running
start_mariadb() {
    if [ "$SKIP_MARIADB" = "1" ]; then
        echo "Skipping MariaDB start (SKIP_MARIADB=1)"
        # Detect CI environment: CI=true (generic) or GITHUB_ACTIONS=true (GitHub Actions)
        # In CI, MariaDB runs as a service container (not systemctl), so wait for it using root password
        if [ -n "$CI" ] || [ -n "$GITHUB_ACTIONS" ]; then
            wait_for_mariadb_ci
        fi
        return
    fi
    
    case $OS in
        arch|manjaro|endeavouros|ubuntu|debian)
            if ! sudo systemctl is-active --quiet mariadb; then
                echo "Starting MariaDB..."
                sudo systemctl start mariadb
                wait_for_mariadb
            else
                echo "MariaDB is already running"
            fi
            ;;
        termux)
            if ! mysqladmin ping --silent 2>/dev/null; then
                echo "Starting MariaDB..."
                mysqld_safe &
                wait_for_mariadb
            else
                echo "MariaDB is already running"
            fi
            ;;
    esac
}

# Wait for MariaDB in CI (service container with root password)
wait_for_mariadb_ci() {
    echo "Waiting for MariaDB (CI mode)..."
    local max_attempts=30
    local attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if mysql -h 127.0.0.1 -u root -p"${MARIADB_ROOT_PASSWORD:-rootpassword}" -e "SELECT 1" > /dev/null 2>&1; then
            echo "MariaDB is ready"
            return 0
        fi
        attempt=$((attempt + 1))
        sleep 1
    done
    echo "Error: MariaDB failed to start within $max_attempts seconds"
    exit 1
}

# Wait for MariaDB to be ready
wait_for_mariadb() {
    echo "Waiting for MariaDB to be ready..."
    local max_attempts=30
    local attempt=0
    case $OS in
        arch|manjaro|endeavouros|ubuntu|debian)
            while [ $attempt -lt $max_attempts ]; do
                if sudo mysqladmin ping --silent 2>/dev/null; then
                    echo "MariaDB is ready"
                    return 0
                fi
                attempt=$((attempt + 1))
                sleep 1
            done
            ;;
        termux)
            while [ $attempt -lt $max_attempts ]; do
                if mysqladmin ping --silent 2>/dev/null; then
                    echo "MariaDB is ready"
                    return 0
                fi
                attempt=$((attempt + 1))
                sleep 1
            done
            ;;
    esac
    echo "Error: MariaDB failed to start within $max_attempts seconds"
    exit 1
}

# Start PHP server
start_php() {
    echo "Starting PHP server on 127.0.0.1:8000..."
    cd public
    php -S 127.0.0.1:8000 -t . &
    PHP_PID=$!
    echo $PHP_PID > /tmp/php-pid.txt
    
    # Give server a moment to bind
    sleep 0.5
    
    # Wait for PHP server to be ready
    echo "Waiting for PHP server to be ready..."
    local max_attempts=30
    local attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if curl -s http://127.0.0.1:8000/ > /dev/null 2>&1; then
            echo "PHP server ready"
            break
        fi
        attempt=$((attempt + 1))
        sleep 1
    done
    
    if [ $attempt -eq $max_attempts ]; then
        echo "Error: PHP server failed to start within $max_attempts seconds"
        kill $PHP_PID 2>/dev/null || true
        exit 1
    fi
    
    # In CI: step completes, PHP server stays running in background for tests
    # In local: block here (wait) so server runs in foreground until Ctrl+C
    if [ -n "$CI" ] || [ -n "$GITHUB_ACTIONS" ]; then
        echo "CI mode: PHP server ready in background (PID: $PHP_PID)"
        exit 0
    else
        echo "Local mode: PHP server running in foreground (PID: $PHP_PID)"
        wait $PHP_PID
    fi
}

# Main
load_env
detect_os
start_mariadb
start_php