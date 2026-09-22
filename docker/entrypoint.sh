#!/usr/bin/env bash
set -euo pipefail

app_port="${PORT:-10000}"
if ! [[ "$app_port" =~ ^[0-9]+$ ]] || (( app_port < 1 || app_port > 65535 )); then
    echo "PORT must be a valid TCP port." >&2
    exit 1
fi

sed "s/__PORT__/${app_port}/g" /etc/apache2/ports.conf.template > /etc/apache2/ports.conf
sed "s/__PORT__/${app_port}/g" /etc/apache2/sites-available/000-default.conf.template > /etc/apache2/sites-available/000-default.conf
exec "$@"
