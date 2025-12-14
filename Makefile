PHP_CS_RULES=@Symfony,-global_namespace_import

.PHONY: test
test: check-style check-rules phpunit

.PHONY: phpunit
phpunit:
	@echo "-- Running phpunit... See output/coverage/index.html "
	XDEBUG_MODE=coverage vendor/bin/phpunit -c phpunit.xml.dist \
		--log-junit output/junit-report.xml \
		--coverage-clover output/clover.xml \
		--coverage-html output/coverage

.PHONY: check-rules
check-rules: phpstan

phpstan:
	vendor/bin/phpstan analyse -c phpstan.neon --error-format=raw

.PHONY: fix-style
fix-style: vendor
	@echo "-- Fixing coding style using php-cs-fixer..."
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES)
	vendor/bin/php-cs-fixer fix tests --rules $(PHP_CS_RULES)


.PHONY: check-style
check-style: vendor
	@echo "-- Checking coding style using php-cs-fixer (run 'make fix-style' if it fails)"
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES) -v --dry-run --diff
	vendor/bin/php-cs-fixer fix tests --rules $(PHP_CS_RULES) -v --dry-run --diff

vendor:
	composer install
