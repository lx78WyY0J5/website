#!/bin/bash
set -e

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
            sudo cp php.ini "$PHP_INI"
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
            mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';"
            mysql -e "GRANT ALL PRIVILEGES ON *.* TO '$DB_USER'@'localhost';"
            mysql -e "FLUSH PRIVILEGES;"
            ;;
    esac
    echo "MariaDB user configured successfully"
}

install_db() {
    if [ -n "$GITHUB_ACTIONS" ] || [ -n "$CI" ] || [ "$LOCAL" = "true" ]; then
        echo "installing DataBase"
        php -r '$_SERVER["DOCUMENT_ROOT"] = "./public"; require "src/php/create-db.php";'
    fi
}

# Main
load_env
detect_os
install_php
install_mariadb
configure_php
init_mariadb
install_db

echo ""
echo "Installation complete!"
echo "  ./start.sh"