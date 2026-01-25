#!/bin/bash
set -e

CONFIG_PATH="/var/www/html/app/config/config.local.neon"
EXAMPLE_PATH="/var/www/html/app/config/config.local.neon.example"

if [ ! -f "$CONFIG_PATH" ] && [ -f "$EXAMPLE_PATH" ]; then
    echo "→ Creating default config.local.neon from example..."
    cp "$EXAMPLE_PATH" "$CONFIG_PATH"
fi

echo "Launching Apache..."

exec apache2-foreground

