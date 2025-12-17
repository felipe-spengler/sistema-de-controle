#!/bin/bash

# Function to check database connection
wait_for_db() {
    echo "Waiting for database connection..."
    max_tries=30
    count=0
    
    while [ $count -lt $max_tries ]; do
        # Try to connect to MySQL server
        php -r "
            require '/var/www/html/config/env_loader.php';
            \$host = getenv('DB_HOST') ?: 'localhost';
            \$user = getenv('DB_USER') ?: 'root';
            \$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
            
            try {
                \$pdo = new PDO(\"mysql:host=\$host\", \$user, \$pass);
                exit(0);
            } catch (PDOException \$e) {
                exit(1);
            }
        "
        
        if [ $? -eq 0 ]; then
            echo "Database connected!"
            return 0
        fi
        
        echo "Waiting for database... ($((count+1))/$max_tries)"
        sleep 2
        count=$((count+1))
    done
    
    echo "Error: Could not connect to database after $max_tries attempts."
    exit 1
}

# Wait for DB to be ready
wait_for_db

# Update database schema on startup
echo "Updating database schema..."
php /var/www/html/setup.php

# Start Scheduler in background
/bin/bash /usr/local/bin/scheduler.sh &

# Start Apache in foreground (original command)
exec apache2-foreground
