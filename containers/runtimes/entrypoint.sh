#!/usr/bin/env bash

set -e

CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
APP_ENV=${APP_ENV:-'production'}
ARTISAN=${ARTISAN:-"php -d variables_order=EGPCS /app/artisan"}
NPM=${NPM:-"pmpm"}
OCTANE_COMMAND="${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080"

if [ "${APP_ENV}" = "development" ]; then
    ENV OCTANE_COMMAND="${OCTANE_COMMAND} --watch"
fi

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

prepare_application() {
    log "INFO" "Preparing application..."
    ${ARTISAN} storage:link
}

prepare_application

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
    app)
        log "INFO" "Starting Octane service..."
        ${OCTANE_COMMAND}
        ;;
    horizon)
        log "INFO" "Starting Horizon service..."
        ${ARTISAN} horizon
        ;;
    scheduler)
        log "INFO" "Starting scheduler..."
        ${ARTISAN} schedule:work
        ;;
    reverb)
        log "INFO" "Starting Reverb service..."
        ${ARTISAN} reverb:start
        ;;
    *)
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac
