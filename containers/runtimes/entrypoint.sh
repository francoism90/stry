#!/usr/bin/env bash
set -e

CONTAINER_ENV=${CONTAINER_ENV:-'production'}
CONTAINER_ROLE=${CONTAINER_ROLE:-'app'}
OCTANE="${ARTISAN} octane:start --server=swoole --host=0.0.0.0 --port=8080"

if [ "${CONTAINER_ENV}" = "development" ]; then
    OCTANE="${OCTANE} --watch"
fi

log() {
    local type="$1"
    local message="$2"
    echo "[$type] $message"
}

prepare_application() {
    log "INFO" "Preparing application..."

    if [ "${CONTAINER_ROLE}" = "app" ]; then
        log "DEBUG" "Migrating application..."
        ${ARTISAN} migrate --seed --force
        ${ARTISAN} scout:sync-index-settings
        ${ARTISAN} google-fonts:fetch
    fi

    ${ARTISAN} optimize
}

if [ "${CONTAINER_ENV}" = "production" ]; then
    prepare_application
fi

log "INFO" "Container role: ${CONTAINER_ROLE}"
case ${CONTAINER_ROLE} in
    app)
        log "INFO" "Starting Octane..."
        exec ${OCTANE}
        ;;
    ssr)
        log "INFO" "Starting SSR..."
        exec ${ARTISAN} inertia:start-ssr
        ;;
    horizon)
        log "INFO" "Starting Horizon..."
        exec ${ARTISAN} horizon
        ;;
    scheduler)
        log "INFO" "Starting Scheduler..."
        exec ${ARTISAN} schedule:work
        ;;
    reverb)
        log "INFO" "Starting Reverb..."
        exec ${ARTISAN} reverb:start
        ;;
    *)
        log "ERROR" "Unknown container role: ${CONTAINER_ROLE}"
        exit 1
        ;;
esac
