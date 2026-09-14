#!/bin/bash
set -e

# Load environment variables from .env
load_env() {
    if [ -f .env ]; then
        set -a
        source .env
        set +a
    else
        echo "Error: .env file not found. Copy .env.exemple to .env and configure it."
        exit 1
    fi
}

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
        echo "Skipping MariaDB (SKIP_MARIADB=1)"
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
    echo "Starting PHP server on localhost:8000..."
    cd public
    php -S localhost:8000 -t .
}

# Main
load_env
detect_os
start_mariadb
start_php