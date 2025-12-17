#!/bin/bash

# Start immediately
echo "Running initial verification..."
php /var/www/html/scripts/verificar_vencimentos.php

# Loop forever
while true; do
    # Get current time
    current_time=$(date +%H:%M)

    # Check if it is 09:00
    if [ "$current_time" == "09:00" ]; then
        echo "Running scheduled verification..."
        php /var/www/html/scripts/verificar_vencimentos.php
        
        # Sleep for 61 seconds to ensure we don't match 09:00 again today
        sleep 61
    else
        # Sleep for 30 seconds before checking again
        sleep 30
    fi
done
