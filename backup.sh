#!/bin/bash
set -e

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

run_backup() {
    BACKUP_DIR="./backup"
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_NAME="mariadb_backup_${TIMESTAMP}"
    BACKUP_PATH="${BACKUP_DIR}/${BACKUP_NAME}"

    mkdir -p "${BACKUP_DIR}"

    echo "Starting backup to ${BACKUP_PATH}..."

    case $OS in
        arch|manjaro|endeavouros|ubuntu|debian)
            sudo mariabackup --backup \
                --user="${DB_USER}" \
                --password="${DB_PASS}" \
                --host="${DB_IP}" \
                --target-dir="${BACKUP_PATH}" \
                --databases="${DB_NAME}"
            ;;
        termux)
            mariabackup --backup \
                --user="${DB_USER}" \
                --password="${DB_PASS}" \
                --host="${DB_IP}" \
                --target-dir="${BACKUP_PATH}" \
                --databases="${DB_NAME}"
            ;;
    esac

    echo "Backup completed. Preparing backup..."

    case $OS in
        arch|manjaro|endeavouros|ubuntu|debian)
            sudo mariabackup --prepare --target-dir="${BACKUP_PATH}"
            ;;
        termux)
            mariabackup --prepare --target-dir="${BACKUP_PATH}"
            ;;
    esac

    echo "Compressing backup..."
    tar -czf "${BACKUP_PATH}.tar.gz" -C "${BACKUP_DIR}" "${BACKUP_NAME}"

    echo "Cleaning up uncompressed backup..."
    rm -rf "${BACKUP_PATH}"

    echo "Backup saved to: ${BACKUP_PATH}.tar.gz"
}

main() {
    load_env
    detect_os
    check_mariabackup
    run_backup
}

main "$@"