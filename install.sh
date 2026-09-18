#!/bin/bash
set -e

# Source common library
source common.sh

# Load environment variables from .env (extends common load_env)
load_env() {
    if [ -f .env ]; then
        set -a
        source .env
        set +a
    elif [ -f .env.exemple ]; then
        echo "No .env found, copying from .env.exemple"
        cp .env.exemple .env
        if [ -n "$GITHUB_ACTIONS" ]; then
            sed -i 's/FORCE_DB_INIT="false"/FORCE_DB_INIT="true"/' .env
        fi
        set -a
        source .env
        set +a
    else
        echo "Error: .env file not found. Copy .env.exemple to .env and configure it."
        exit 1
    fi
}

# Install PHP
install_php() {
    case $OS in
        arch|manjaro|endeavouros)
            echo "Installing PHP on Arch..."
            sudo pacman -S --noconfirm php
            ;;
        ubuntu|debian)
            echo "Installing PHP on Ubuntu/Debian..."
            sudo apt-get update -y
            sudo apt-get install -y php php-mysql php-curl php-mbstring php-xml php-readline php-zip
            ;;
        termux)
            echo "Installing PHP on Termux..."
            pkg install -y php
            ;;
        *)
            echo "Unsupported OS for PHP install: $OS"
            exit 1
            ;;
    esac
}

# Install MariaDB (skip if SKIP_MARIADB=1)
install_mariadb() {
    if [ "$SKIP_MARIADB" = "1" ]; then
        echo "Skipping MariaDB install (SKIP_MARIADB=1)"
        return
    fi
    case $OS in
        arch|manjaro|endeavouros)
            echo "Installing MariaDB on Arch..."
            sudo pacman -S --noconfirm mariadb
            ;;
        ubuntu|debian)
            echo "Installing MariaDB on Ubuntu/Debian..."
            sudo apt-get update -y
            sudo apt-get install -y mariadb-server
            ;;
        termux)
            echo "Installing MariaDB on Termux..."
            pkg install -y mariadb
            ;;
        *)
            echo "Unsupported OS for MariaDB install: $OS"
            exit 1
            ;;
    esac
}

# Configure MariaDB for PITR (Point-In-Time Recovery) - deploy config before MariaDB starts
configure_mariadb_pitr() {
    echo "Configuring MariaDB for PITR..."
    case $OS in
        arch|manjaro|endeavouros)
            CONFIG_DIR="/etc/mysql/my.cnf.d"
            sudo mkdir -p "$CONFIG_DIR"
            sudo cp PITR-Config.cnf "$CONFIG_DIR/99-pitr.cnf"
            echo "PITR config deployed to $CONFIG_DIR/99-pitr.cnf"
            ;;
        ubuntu|debian)
            CONFIG_DIR="/etc/mysql/mariadb.conf.d"
            sudo mkdir -p "$CONFIG_DIR"
            sudo cp PITR-Config.cnf "$CONFIG_DIR/99-pitr.cnf"
            echo "PITR config deployed to $CONFIG_DIR/99-pitr.cnf"
            ;;
        termux)
            CONFIG_DIR="$PREFIX/etc/my.cnf.d"
            mkdir -p "$CONFIG_DIR"
            cp PITR-Config.cnf "$CONFIG_DIR/99-pitr.cnf"
            echo "PITR config deployed to $CONFIG_DIR/99-pitr.cnf"
            ;;
    esac

    # Ensure backup directory exists at repo root
    BACKUP_DIR="./backup"
    mkdir -p "$BACKUP_DIR"
    echo "Backup directory: $BACKUP_DIR"

    # Link mariaDB PITR to folder
    ln -s /var/lib/mysql/mariadb-bin ./backup/PITR
}

# Configure PHP - copy php.ini to standard location
configure_php() {
    echo "Configuring PHP..."

    case $OS in
        arch|manjaro|endeavouros)
            PHP_INI="/etc/php/php.ini"
            sudo cp php.ini "$PHP_INI"
            echo "php.ini copied to $PHP_INI"
            ;;
        ubuntu|debian)
            # Don't replace system php.ini; add custom settings via conf.d
            PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
            CUSTOM_INI="/etc/php/${PHP_VERSION}/cli/conf.d/99-custom.ini"
            sudo cp php.ini "$CUSTOM_INI"
            echo "Custom php.ini copied to $CUSTOM_INI"
            # Remove extension_dir if present (wrong for Ubuntu)
            sudo sed -i '/^extension_dir/d' "$CUSTOM_INI"
            ;;
        termux)
            PHP_INI="/data/data/com.termux/files/usr/etc/php.ini"
            cp php.ini "$PHP_INI"
            echo "php.ini copied to $PHP_INI"
            ;;
    esac
}

# Initialize MariaDB (skip if SKIP_MARIADB=1)
init_mariadb() {
    if [ "$SKIP_MARIADB" = "1" ]; then
        echo "Skipping MariaDB init (SKIP_MARIADB=1)"
        # Detect CI environment: CI=true (generic) or GITHUB_ACTIONS=true (GitHub Actions)
        # In CI, MariaDB runs as a service container (not systemctl), so wait for it using root password
        if [ -n "$CI" ] || [ -n "$GITHUB_ACTIONS" ]; then
            wait_for_mariadb_ci
        fi
        return
    fi
    echo "Initializing MariaDB..."
    case $OS in
        arch|manjaro|endeavouros)
            sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql 2>/dev/null || true
            sudo systemctl start mariadb
            sudo systemctl enable mariadb
            wait_for_mariadb
            configure_mariadb_user
            ;;
        ubuntu|debian)
            sudo systemctl start mariadb
            sudo systemctl enable mariadb
            wait_for_mariadb
            configure_mariadb_user
            ;;
        termux)
            mysql_install_db 2>/dev/null || true
            mysqld_safe &
            wait_for_mariadb
            configure_mariadb_user
            ;;
    esac
}



# Configure MariaDB user from .env variables
configure_mariadb_user() {
    echo "Configuring MariaDB user: $DB_USER"
    case $OS in
        arch|manjaro|endeavouros|ubuntu|debian)
            sudo mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
            sudo mysql -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USER'@'localhost';"
            sudo mysql -e "FLUSH PRIVILEGES;"
            ;;
        termux)
            mysql -u root -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
            mysql -u root -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USER'@'localhost';"
            mysql -u root -e "FLUSH PRIVILEGES;"
            ;;
    esac
    echo "MariaDB user configured successfully"
}

install_db() {
    if [ -n "$GITHUB_ACTIONS" ] || [ -n "$CI" ] || [ "$LOCAL" = "true" ] || [ "$FIRST_RUN" = "true" ]; then
        echo "installing DataBase"
        php -r '$_SERVER["DOCUMENT_ROOT"] = "./public"; require "./public/src/php/create-db.php";'
        if [ "$FIRST_RUN" = "true" ]; then
            sed -i 's/FIRST_RUN="true"/FIRST_RUN="false"/' .env
        fi
    fi
}

# Main
load_env
detect_os
install_php
install_mariadb
configure_php
configure_mariadb_pitr
init_mariadb
install_db

echo ""
echo "Installation complete!"
echo "  ./start.sh"