# Makefile for GShoppingContent

doc:
	rm -rf docs
	~/tmp/PhpDocumentor/phpdoc -t docs/ -f GShoppingContent.php -d . -s on -o HTML:Smarty:PHP
	mv docs/media/background.pn{,g}

test:
	phpunit --colors tests/
