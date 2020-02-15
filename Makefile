PHP_CS_RULES=@Symfony,-ordered_imports,-phpdoc_summary

.PHONY: test
test: check-style check-rules
	@echo "-- Running phpunit... See output/index.html "
	vendor/bin/phpunit -c phpunit.xml.dist \
		--log-junit output/junit-report.xml \
		--coverage-clover output/clover.xml \
		--coverage-html output/coverage

.PHONY: check-rules
check-rules: vendor
	@echo "-- Checking coding rules using phpmd (see @SuppressWarning to bypass control)"
	vendor/bin/phpmd src text cleancode,codesize,controversial,design,naming,unusedcode


.PHONY: fix-style
fix-style: vendor
	@echo "-- Fixing coding style using php-cs-fixer..."
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES)
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES)


.PHONY: check-style
check-style: vendor
	@echo "-- Checking coding style using php-cs-fixer (run 'make fix-style' if it fails)"
	vendor/bin/php-cs-fixer fix src --rules $(PHP_CS_RULES) -v --dry-run --diff --using-cache=no
	vendor/bin/php-cs-fixer fix tests --rules $(PHP_CS_RULES) -v --dry-run --diff --using-cache=no

# to ensure that vendors are downloaded...
vendor: composer.phar
	php composer.phar install

composer.phar:
	curl -s https://getcomposer.org/installer | php
	chmod +x composer.phar
