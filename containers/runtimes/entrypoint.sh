#!/usr/bin/env bash

set -e

CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
APP_ENV=${APP_ENV:-'production'}
ARTISAN=${ARTISAN:-"php -d variables_order=EGPCS /app/artisan"}
COMPOSER=${COMPOSER:-"composer"}
NPM=${NPM:-"pnpm"}
OCTANE="${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080"

if [ "${APP_ENV}" = "development" ]; then
    OCTANE="${OCTANE} --watch"
fi

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

prepare_application() {
    log "INFO" "Preparing application..."
    ${COMPOSER} install --prefer-dist --no-dev --optimize-autoloader --no-interaction
    ${ARTISAN} storage:link
    ${ARTISAN} migrate --seed --force
    ${ARTISAN} wayfinder:generate
    ${ARTISAN} google-fonts:fetch
    ${NPM} install
    ${NPM} build
    ${ARTISAN} optimize
    ${ARTISAN} scout:sync-index-settings
}

if [[ "${CONTAINER_ROLE}" = "app" && "${APP_ENV}" = "production" ]]; then
    prepare_application
fi

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
    app)
        log "INFO" "Starting Octane service..."
        exec ${OCTANE}
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
