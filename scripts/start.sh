#!/bin/bash

# Update database schema on startup
echo "Updating database schema..."
php /var/www/html/setup.php

# Start Scheduler in background
/bin/bash /usr/local/bin/scheduler.sh &

# Start Apache in foreground (original command)
exec apache2-foreground
