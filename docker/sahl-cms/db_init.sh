#!/bin/bash
set -e

echo "Starting application entrypoint..."

if [ -n "$DB_HOST" ]; then
  echo "Waiting for database ($DB_HOST:$DB_PORT)..."

  export PGPASSWORD="$DB_PASSWORD"

  until pg_isready \
    -h "$DB_HOST" \
    -p "${DB_PORT:-5432}" \
    -U "$DB_USER" \
    -d "$DB_NAME"; do
    sleep 1
  done

  echo "Database is ready."
fi

if [ -d "/var/www/html/db" ]; then
  echo "🗃  Executing SQL migrations..."
  for f in /var/www/html/db/update*.sql; do
    if [ -f "$f" ]; then
      echo "▶️  Running DB $f ..."
      psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "$f" || {
        echo "Error executing $f"
        exit 1
      }
    fi
  done
  echo "All SQL migrations executed successfully."
else
  echo "ℹNo /db directory found, skipping SQL updates."
fi

echo "Launching Apache..."
