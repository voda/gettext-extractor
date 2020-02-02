check: test phpstan

test:
	./vendor/bin/phpunit

phpstan:
	./vendor/bin/phpstan analyse

build:
	box compile

