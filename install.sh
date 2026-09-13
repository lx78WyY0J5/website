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
    
    # Determine php.ini destination
    case $OS in
        arch|manjaro|endeavouros)
            PHP_INI="/etc/php/php.ini"
            ;;
        ubuntu|debian)
            PHP_INI="/etc/php/8.*/cli/php.ini"
            # Find actual version
            for f in /etc/php/*/cli/php.ini; do
                [ -f "$f" ] && PHP_INI="$f" && break
            done
            ;;
        termux)
            PHP_INI="/data/data/com.termux/files/usr/etc/php.ini"
            ;;
    esac
    
    sudo cp php.ini "$PHP_INI"
    echo "php.ini copied to $PHP_INI"
}

# Initialize MariaDB (skip if SKIP_MARIADB=1)
init_mariadb() {
    if [ "$SKIP_MARIADB" = "1" ]; then
        echo "Skipping MariaDB init (SKIP_MARIADB=1)"
        return
    fi
    echo "Initializing MariaDB..."
    case $OS in
        arch|manjaro|endeavouros)
            sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql 2>/dev/null || true
            sudo systemctl start mariadb
            sudo systemctl enable mariadb
            ;;
        ubuntu|debian)
            sudo systemctl start mariadb
            sudo systemctl enable mariadb
            ;;
        termux)
            mysql_install_db 2>/dev/null || true
            mysqld_safe &
            sleep 3
            ;;
    esac
}

# Main
detect_os
install_php
install_mariadb
configure_php
init_mariadb

echo ""
echo "Installation complete!"
echo "Configure your .env file (copy .env.exemple to .env and edit)"
echo "Then create the database (requires admin login or FORCE_DB_INIT=true):"
echo "  php public/src/php/create-db.php"
echo ""
echo "Then start the server:"
echo "  cd public && php -S localhost:8000 -t ."