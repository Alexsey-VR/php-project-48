install:
	composer install

update:
	composer update

validate:
	composer validate

analyze:
	composer exec -v phpstan analyse -- -c vendor/phpstan/phpstan/conf/phpstan.neon --level 10 --ansi src/

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src/ bin/

test:
	XDEBUG_MODE=coverage composer exec --verbose vendor/bin/phpunit tests -- --coverage-text

test-sonar:
	XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-clover=coverage.xml tests

test-dev:
	XDEBUG_MODE=coverage composer exec --verbose vendor/bin/phpunit tests -- --coverage-html ./reports
