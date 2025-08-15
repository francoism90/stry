#!/usr/bin/env bash

set -e

CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
APP_ENV=${APP_ENV:-'production'}
ARTISAN=${ARTISAN:-"php -d variables_order=EGPCS /app/artisan"}
NPM=${NPM:-"pmpm"}

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

run_octane() {
    log "INFO" "Starting Octane service..."
    ${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080
}

run_horizon() {
    log "INFO" "Starting Horizon service..."
    ${ARTISAN} horizon
}

run_reverb() {
    log "INFO" "Starting Reverb service..."
    ${ARTISAN} reverb:start
}

run_scheduler() {
    log "INFO" "Starting scheduler..."
    ${ARTISAN} schedule:work
}

set_permissions() {
    log "INFO" "Setting permissions..."
    chown -R docker:docker /app
}

prepare_application() {
    log "INFO" "Preparing application..."
    ${ARTISAN} storage:link
    ${ARTISAN} migrate --force --seed
    ${ARTISAN} google-fonts:fetch
    ${ARTISAN} wayfinder:generate
}

build_application() {
    log "INFO" "Building application..."
    ${NPM} build
    ${ARTISAN} optimize
}

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
    app)
        set_permissions
        prepare_application
        build_application
        run_octane
        ;;
    horizon)
        run_horizon
        ;;
    scheduler)
        run_scheduler
        ;;
    reverb)
        run_reverb
        ;;
    *)
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac
