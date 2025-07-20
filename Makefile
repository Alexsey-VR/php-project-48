install:
	composer install

validate:
	composer validate

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin

test:
	XDEBUG_MODE=coverage composer exec --verbose phpunit tests -- --coverage-html ./reports
