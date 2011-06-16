# Makefile for GShoppingContent

version = ALPHA
name = gshoppingcontent-php-$(version)
builddir = build
distdir = dist
zipdir = $(builddir)/$(name)
distzip = $(distdir)/$(name)
docsout = docs/html

doc:
	rm -rf $(docsout)
	~/tmp/PhpDocumentor/phpdoc -t $(docsout) \
        -d . \
        -i tests/ \
        -s on \
        -o HTML:Smarty:PHP
	mv $(docsout)/media/background.pn{,g}

docup : doc
	cat ~/.asp | /usr/local/google_appengine/appcfg.py update docs/

test:
	phpunit --colors tests/

sdist : doc
	rm -rf build
	mkdir -p dist build $(zipdir)
	cp -R *.php tests/ docs/ LICENSE README $(zipdir)
	cd build && zip -r ../$(distzip) $(name)
	rm -rf doc
