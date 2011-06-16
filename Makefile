# Makefile for GShoppingContent

version = ALPHA
name = gshoppingcontent-php-$(version)
builddir = build
distdir = dist
zipdir = $(builddir)/$(name)
distzip = $(distdir)/$(name)
docsout = docs/html

doc:
	rm -rf docs/html
	~/tmp/PhpDocumentor/phpdoc -t $(docsout) \
        -f GShoppingContent.php \
        -d .,docs \
        -i tests/ \
        -s on \
        -o HTML:Smarty:PHP
	mv docs/media/background.pn{,g}

test:
	phpunit --colors tests/

sdist : doc
	rm -rf build
	mkdir -p dist build $(zipdir)
	cp -R *.php tests/ docs/ tutorials/ LICENSE README $(zipdir)
	cd build && zip -r ../$(distzip) $(name)
