#!/usr/bin/env bash
set -e

ROOT_DIR="/app"
CORS_POLICY="${ROOT_DIR}/containers/runtimes/policies/cors.json"
ASSETS_POLICY="${ROOT_DIR}/containers/runtimes/policies/assets.json"

# Source .env if env vars are not already set...
if [ -z "${AWS_ENDPOINT}" ] && [ -f "${ROOT_DIR}/.env" ]; then
    # shellcheck source=/dev/null
    source "${ROOT_DIR}/.env"
fi

# AWS CLI expects AWS_ENDPOINT_URL...
export AWS_ENDPOINT_URL="${AWS_ENDPOINT}"

: "${AWS_ENDPOINT_URL:?Required: AWS_ENDPOINT}"
: "${AWS_ACCESS_KEY_ID:?Required: AWS_ACCESS_KEY_ID}"
: "${AWS_SECRET_ACCESS_KEY:?Required: AWS_SECRET_ACCESS_KEY}"

BUCKETS=(local assets conversions segments secrets)
PUBLIC_BUCKETS=(assets conversions segments secrets)

# Create buckets...
echo "Creating buckets..."
for bucket in "${BUCKETS[@]}"; do
    echo "  -> ${bucket}"
    aws s3api create-bucket --bucket "${bucket}" 2>/dev/null || true
done

# Apply CORS policy...
echo "Applying CORS policy..."
for bucket in "${PUBLIC_BUCKETS[@]}"; do
    echo "  -> ${bucket}"
    aws s3api put-bucket-cors \
        --bucket "${bucket}" \
        --cors-configuration "file://${CORS_POLICY}"
done

# Allow anonymous read on assets (fonts, icons)...
echo "Applying anonymous read policy to assets..."
aws s3api put-bucket-policy --bucket assets --policy "file://${ASSETS_POLICY}"

echo "Done."
