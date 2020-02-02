help: ## list available targets (this page)
	@awk 'BEGIN {FS = ":.*?## "} /^[0-9a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

check: test phpstan ## run PHPUnit & PHPStan

test: ## run tests with PHPUnit
	./vendor/bin/phpunit

phpstan: ## run static analysis with PHPStan
	./vendor/bin/phpstan analyse

build: ## build .phar file
	box compile

