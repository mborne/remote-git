PHP_CS_RULES=@Symfony,-ordered_imports,-phpdoc_summary,-global_namespace_import,-no_superfluous_phpdoc_tags
PHP_MD_RULES=cleancode,codesize,controversial,design,naming,unusedcode

.PHONY: test
test: check-style check-rules
	@echo "-- Running phpunit... See output/index.html "
	XDEBUG_MODE=coverage vendor/bin/phpunit -c phpunit.xml.dist \
		--log-junit output/junit-report.xml \
		--coverage-clover output/clover.xml \
		--coverage-html output/coverage

.PHONY: check-rules
check-rules: phpstan phpmd

phpmd:
	@echo "-- Checking coding rules using phpmd (see @SuppressWarning to bypass control)"
	vendor/bin/phpmd src text $(PHP_MD_RULES)

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
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES) -v --dry-run --diff --using-cache=no
	vendor/bin/php-cs-fixer fix tests --rules $(PHP_CS_RULES) -v --dry-run --diff --using-cache=no

vendor:
	composer install
