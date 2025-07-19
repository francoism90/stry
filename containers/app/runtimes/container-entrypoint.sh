#!/usr/bin/env bash

set -e

if [ "$OCTANE_USER" != "root" ] && [ "$OCTANE_USER" != "docker" ]; then
    echo "You should set OCTANE_USER to either 'docker' or 'root'."
    exit 1
fi

if [ ! -z "$UID" ]; then
    usermod -u $UID docker
fi

if [ "$1" != "" ]; then
    exec "$@"
else
    exec $OCTANE_COMMAND
fi
