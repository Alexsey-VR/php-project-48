install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

test:
	XDEBUG_MODE=coverage composer exec --verbose vendor/bin/phpunit tests -- --coverage-text

test-sonar:
	XDEBUG_MODE=coverage composer exec vendor/bin/phpunit tests -- --coverage-clover coverage.xml

test-dev:
	XDEBUG_MODE=coverage composer exec --verbose vendor/bin/phpunit tests -- --coverage-html ./reports
