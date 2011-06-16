# Makefile for GShoppingContent

version = 1
name = gshoppingcontent-php-$(version)
builddir = build
distdir = dist
zipdir = $(builddir)/$(name)
distzip = $(distdir)/$(name)

doc:
	rm -rf docs
	~/tmp/PhpDocumentor/phpdoc -t docs/ -f GShoppingContent.php -d . -s on -o HTML:Smarty:PHP
	mv docs/media/background.pn{,g}

test:
	phpunit --colors tests/

sdist : doc
	rm -rf build
	mkdir -p dist build $(zipdir)
	cp -R *.php tests/ docs/ tutorials/ LICENSE README $(zipdir)
	cd build && zip -r ../$(distzip) $(name)
