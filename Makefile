help: ## Show this message
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

clean-code:
	./vendor/bin/php-cs-fixer --diff -v fix src

test: test-code test-qa

test-code: test-phpunit-unit test-phpunit-functional

test-qa: test-phpcs test-phpa test-phpstan test-phpcpd test-phpmd

test-phpunit-unit:
	./vendor/bin/phpunit --group unit

test-phpunit-functional:
	./vendor/bin/phpunit --group functional

test-phpcs:
	./vendor/bin/php-cs-fixer --diff --dry-run -v fix src

test-phpa:
	./vendor/bin/phpa src

test-phpstan:
	./vendor/bin/phpstan analyze src/ -l 5 -c phpstan.neon

test-phpcpd:
	./vendor/bin/phpcpd --exclude Tests --exclude MethodBuilder src/

test-phpmd:
	./vendor/bin/phpmd --exclude Tests/Fixtures src/ text phpmd.xml

test-code-coverage-html:
	./vendor/bin/phpunit --coverage-html=coverage

test-code-coverage-clover:
	./vendor/bin/phpunit --coverage-clover=coverage.xml
