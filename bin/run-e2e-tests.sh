#!/usr/bin/env bash
composer install --no-dev
npm ci

npm run-script build
npm run-script dist

eval "$(ssh-agent -s)"
mkdir $HOME/.ssh
echo "$SSH_KEY" > "$HOME/.ssh/key"
chmod 600 "$HOME/.ssh/key"

rsync --delete -rc --force --exclude-from="$GITHUB_WORKSPACE/.distignore" -e "ssh -i $HOME/.ssh/key -o StrictHostKeyChecking=no -p $SSH_PORT" "$GITHUB_WORKSPACE/dist/optimole-wp/" $SSH_USERNAME@$SSH_HOST:$SSH_PATH
ssh -i $HOME/.ssh/key -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USERNAME@$SSH_HOST "cd /var/www && docker-compose run --rm cli wp elementor flush_css --url=http://testing.optimole.com"
