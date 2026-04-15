#!/bin/bash
set -e

echo "🔧 Fixing SuiteCRM permissions..."

chown -R www-data:www-data \
  custom \
  modules \
  cache \
  upload \
  themes \
  data \
  logs || true
  
chmod -R 775 cache custom modules upload themes data logs
chmod 775 config_override.php

echo "🚀 Starting Apache..."
exec apache2-foreground
