#!/bin/bash

# Common library for shared functions across scripts
# Source this file: source common.sh

# Load environment variables from .env
load_env() {
    if [ -f .env ]; then
        set -a
        source .env
        set +a
    elif [ -f .env.exemple ]; then
        echo "No .env found, copying from .env.exemple"
        cp .env.exemple .env
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

# Check if mariabackup is available
check_mariabackup() {
    case $OS in
        arch|manjaro|endeavouros)
            if ! command -v mariabackup &> /dev/null; then
                echo "mariabackup not found. Install with: sudo pacman -S mariadb"
                exit 1
            fi
            ;;
        ubuntu|debian)
            if ! command -v mariabackup &> /dev/null; then
                echo "mariabackup not found. Install with: sudo apt-get install mariadb-backup"
                exit 1
            fi
            ;;
        termux)
            if ! command -v mariabackup &> /dev/null; then
                echo "mariabackup not found. Install with: pkg install mariadb"
                exit 1
            fi
            ;;
        *)
            echo "Unsupported OS for mariabackup: $OS"
            exit 1
            ;;
    esac
}