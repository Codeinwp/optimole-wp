#!/usr/bin/env bash
set -euo pipefail

readonly DB_RETRY_DELAY=5
readonly DB_MAX_ATTEMPTS=24
readonly QA_URL='http://testing.optimole.com'

eval "$(ssh-agent -s)"
install -d -m 700 "$HOME/.ssh"
printf '%s\n' "$SSH_KEY" > "$HOME/.ssh/key"
chmod 600 "$HOME/.ssh/key"

rsync --delete -rc --force --exclude-from="$GITHUB_WORKSPACE/.distignore" -e "ssh -i $HOME/.ssh/key -o StrictHostKeyChecking=no -p $SSH_PORT" "$GITHUB_WORKSPACE/dist/optimole-wp/" $SSH_USERNAME@$SSH_HOST:$SSH_PATH
ssh -i "$HOME/.ssh/key" -o StrictHostKeyChecking=no -p "$SSH_PORT" "$SSH_USERNAME@$SSH_HOST" "DB_RETRY_DELAY='$DB_RETRY_DELAY' DB_MAX_ATTEMPTS='$DB_MAX_ATTEMPTS' QA_URL='$QA_URL' bash -s" <<'REMOTE_COMMAND'
set -euo pipefail

cd /var/www
docker-compose up -d db wordpress

attempt=1
until docker-compose run --rm cli wp db check --url="$QA_URL"; do

	if [ "$attempt" -ge "$DB_MAX_ATTEMPTS" ]; then
		echo "The QA database did not become ready after $DB_MAX_ATTEMPTS attempts." >&2
		exit 1
	fi

	echo "QA database is not ready (attempt $attempt/$DB_MAX_ATTEMPTS); retrying in $DB_RETRY_DELAY seconds..." >&2
	attempt=$((attempt + 1))
	sleep "$DB_RETRY_DELAY"
done

docker-compose run --rm cli wp elementor flush_css --url="$QA_URL"
REMOTE_COMMAND
