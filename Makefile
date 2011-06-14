# Makefile for GShoppingContent

doc:
	rm -rf docs/html
	~/tmp/PhpDocumentor/phpdoc -t docs/html -f GShoppingContent.php -d . -d docs -s on -o HTML:Smarty:PHP
	mv docs/html/media/background.pn{,g}

test:
	phpunit --colors tests/
