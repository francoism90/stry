#!/usr/bin/env bash
set -e

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CORS_POLICY="${ROOT_DIR}/containers/runtimes/policies/cors.json"

# Source .env if env vars are not already set...
if [ -z "${AWS_ENDPOINT}" ] && [ -f "${ROOT_DIR}/.env" ]; then
    # shellcheck source=/dev/null
    source "${ROOT_DIR}/.env"
fi

# AWS CLI expects AWS_ENDPOINT_URL...
export AWS_ENDPOINT_URL="${AWS_ENDPOINT}"

BUCKETS=(assets conversions segments secrets)

: "${AWS_ENDPOINT_URL:?Required: AWS_ENDPOINT}"
: "${AWS_ACCESS_KEY_ID:?Required: AWS_ACCESS_KEY_ID}"
: "${AWS_SECRET_ACCESS_KEY:?Required: AWS_SECRET_ACCESS_KEY}"

echo "Applying CORS policy to buckets..."

for bucket in "${BUCKETS[@]}"; do
    echo "  -> $bucket"
    aws s3api put-bucket-cors \
        --bucket "$bucket" \
        --cors-configuration "file://${CORS_POLICY}"
done

echo "Done."
