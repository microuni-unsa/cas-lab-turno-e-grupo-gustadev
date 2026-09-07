#!/bin/bash
set -e

# Ensure writable permissions for runtime directories
chmod -R 777 /var/www/html/temp \
             /var/www/html/log \
             /var/www/html/updates \
             /var/www/html/upload \
             /var/www/html/imports \
             /var/www/html/src 2>/dev/null || true

exec "$@"
