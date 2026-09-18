#!/bin/bash
set -e

# Source common library
source common.sh

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

# If sourced do not run, if backup.sh is ran, it will run this ;
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi