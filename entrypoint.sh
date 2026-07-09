#!/usr/bin/env bash
set -e

APACHE_UID="${APACHE_UID:-1000}"
APACHE_GID="${APACHE_GID:-1000}"

mkdir -p \
  /var/www/html/cache/tmp \
  /var/www/html/cache/modules \
  /var/www/html/custom \
  /var/www/html/modules \
  /var/www/html/upload \
  /var/www/html/upload/import

chown -R "${APACHE_UID}:${APACHE_GID}" \
  /var/www/html/cache \
  /var/www/html/custom \
  /var/www/html/modules \
  /var/www/html/upload || true

exec apache2-foreground
