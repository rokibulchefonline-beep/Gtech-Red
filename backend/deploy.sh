#!/bin/sh
# Run after each deploy (Plesk > Git > "Enable additional deployment actions", or by hand over SSH).
# Plesk's PHP for the site is used when PHP_BIN is set, e.g. PHP_BIN=/opt/plesk/php/8.4/bin/php
set -e
cd "$(dirname "$0")"
PHP="${PHP_BIN:-php}"
COMPOSER="${COMPOSER_BIN:-composer}"

$PHP $(command -v $COMPOSER) install --no-dev --optimize-autoloader --no-interaction
$PHP artisan migrate --force
$PHP artisan storage:link 2>/dev/null || true
$PHP artisan optimize:clear
$PHP artisan optimize
$PHP artisan view:cache
echo "Deployed."
