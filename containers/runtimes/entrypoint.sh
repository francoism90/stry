#!/usr/bin/env bash

set -e

CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
APP_ENV=${APP_ENV:-'production'}
ARTISAN=${ARTISAN:-"php -d variables_order=EGPCS /app/artisan"}
NPM=${NPM:-"pmpm"}
OCTANE_COMMAND="${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080"

if [ "${APP_ENV}" = "development" ]; then
    OCTANE_COMMAND="${OCTANE_COMMAND} --watch"
fi

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

build_application() {
    log "INFO" "Building application..."
    ${ARTISAN} storage:link
    ${ARTISAN} migrate --seed
    ${ARTISAN} wayfinder:generate
    ${ARTISAN} google-fonts:fetch
    ${NPM} build
}

if [ "${CONTAINER_ROLE}" = "app" && "${APP_ENV}" = "production" ]; then
    build_application
fi

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
    app)
        log "INFO" "Starting Octane service..."
        exec ${OCTANE_COMMAND}
        ;;
    horizon)
        log "INFO" "Starting Horizon service..."
        exec ${ARTISAN} horizon
        ;;
    scheduler)
        log "INFO" "Starting scheduler..."
        exec ${ARTISAN} schedule:work
        ;;
    reverb)
        log "INFO" "Starting Reverb service..."
        exec ${ARTISAN} reverb:start
        ;;
    *)
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac
