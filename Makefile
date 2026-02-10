.PHONY: install qa cs csf phpstan tests tests-watch coverage

install:
	composer update

qa: phpstan cs

cs:
	vendor/bin/codesniffer src tests

csf:
	vendor/bin/codefixer src tests

phpstan:
	vendor/bin/phpstan analyse -l max -c phpstan.neon src

tests:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases/$(FILE)
	# example make tests FILE=model/RepositoryTest.phpt

tests-watch:
	vendor/bin/tester -s -p php --colors 1 -C tests/Cases/$(FILE) -w tests -w src
	# example make tests-watch FILE=model/RepositoryTest.phpt

coverage:
ifdef GITHUB_ACTION
	vendor/bin/tester -s -p phpdbg --colors 1 -C --coverage coverage.xml --coverage-src src tests/Cases
else
	vendor/bin/tester -s -p php --colors 1 -C --coverage coverage.html --coverage-src src tests/Cases
endif

