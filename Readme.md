GettextExtractor
================
Cool tool for extracting gettext phrases from PHP files and templates.

Dependencies
------------
* [nikic/PHP-Parser](https://github.com/nikic/PHP-Parser/)

Dependencies are installed with [composer](http://getcomposer.org/). You can use these commands:

`$ curl -s http://getcomposer.org/installer | php`  
`$ php composer.phar install`
	

Usage
-----
`php gettext-extractor.php [options]`

	Options:
	  -h            display this help and exit
	  -oFILE        output file, default output is stdout
	  -lFILE        log file, default is stderr
	  -fFILE        file to extract, can be specified several times
	  -kFUNCTION    add FUNCTION to filters, format is:
	                FILTER:FUNCTION_NAME:SINGULAR,PLURAL,CONTEXT
	                default FILTERs are PHP and NetteLatte
	                for SINGULAR, PLURAL and CONTEXT '0' means not set
	                can be specified several times
	  -mKEY:VALUE   set meta header

e.g.: `php gettext-extractor -o outup/file.pot -f files/to/extract/`

Supported file types
--------------------
* .php
* .latte (Nette Latte templates)

License
-------
GettextExtractor is licensed under the New BSD License.

Copyright
---------
* 2009 Karel Klima
* 2010 Ondřej Vodáček
