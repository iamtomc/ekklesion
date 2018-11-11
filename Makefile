coverage:
	vendor/bin/phpunit --coverage-text

cs:
	vendor/bin/php-cs-fixer fix --diff --verbose

main-translation:
	find . -iname '*.php' | xargs xgettext -L PHP -o messages.po

db-validate:
	vendor/bin/doctrine orm:validate-schema

commit: main-translation cs coverage
	git add . && git commit