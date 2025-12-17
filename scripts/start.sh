#!/bin/bash

# Update database schema on startup
echo "Updating database schema..."
php /var/www/html/update_database.php

# Start Scheduler in background
/bin/bash /var/www/html/scripts/scheduler.sh &

# Start Apache in foreground (original command)
exec apache2-foreground
