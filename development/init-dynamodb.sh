#!/usr/bin/env ash

# dynamodb-local is in-memory but the container can outlive a single `make test`
# run, so re-running this script against an already-initialized table must be a
# safe no-op rather than a failure.

if aws dynamodb describe-table --table-name sildisco_local_user-log \
  --endpoint-url http://dynamo:8000 >/dev/null 2>&1; then
  echo "Table sildisco_local_user-log already exists; skipping init."
  exit 0
fi

# Create data table
aws dynamodb create-table --table-name sildisco_local_user-log \
  --attribute-definitions AttributeName=ID,AttributeType=S \
  --key-schema AttributeName=ID,KeyType=HASH \
  --provisioned-throughput ReadCapacityUnits=10,WriteCapacityUnits=10 \
  --endpoint-url http://dynamo:8000


# Enable Time to Live
aws dynamodb update-time-to-live --table-name sildisco_local_user-log \
  --time-to-live-specification "Enabled=true,AttributeName=ExpiresAt" \
  --endpoint-url http://dynamo:8000
