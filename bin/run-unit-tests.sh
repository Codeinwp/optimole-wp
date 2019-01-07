#!/usr/bin/env bash
composer global require "phpunit/phpunit=4.8.*|5.7.*"
bash bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
phpunit --version
php -v
PHPUNIT_FILE=$([ -z $CC_TEST_REPORTER_ID ] && echo " phpunit.xml" || echo "phpunit-cc.xml --coverage-text")

phpunit --configuration $PHPUNIT_FILE || exit 1
WP_MULTISITE=1 phpunit  --configuration $PHPUNIT_FILE || exit 1
