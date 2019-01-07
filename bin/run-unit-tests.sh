#!/usr/bin/env bash
#composer global require "phpunit/phpunit=4.8.*|5.7.*"
#bash bin/install-wp-tests.sh wordpress_test root '' localhost $WP_VERSION
phpunit --version
php -v
NO_COVERAGE=$([ -z $CC_TEST_REPORTER_ID ] && echo " --no-coverage" || echo "")

phpunit  --configuration phpunit.xml --coverage-text $NO_COVERAGE || exit 1
WP_MULTISITE=1 phpunit  --configuration phpunit.xml --coverage-text $NO_COVERAGE || exit 1
