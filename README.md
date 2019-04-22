GettextExtractor
================
Tool for extracting gettext phrases from PHP files and Latte templates. Output is generated as a .pot file.

[![Build Status](https://travis-ci.org/voda/gettext-extractor.svg?branch=master)](https://travis-ci.org/voda/gettext-extractor)
[![Latest Stable Version](https://poser.pugx.org/voda/gettext-extractor/v/stable)](https://packagist.org/packages/voda/gettext-extractor)
[![Total Downloads](https://poser.pugx.org/voda/gettext-extractor/downloads)](https://packagist.org/packages/voda/gettext-extractor)
[![License](https://poser.pugx.org/voda/gettext-extractor/license)](https://packagist.org/packages/voda/gettext-extractor)

Installation
------------
To install gettext-extractor install it with [composer](https://getcomposer.org/):
`$ composer require --dev voda/gettext-extractor`

Alternatively you can download a standalone PHAR file from [releases page](https://github.com/voda/gettext-extractor/releases).

Usage
-----
`./vendor/bin/gettext-extractor [options]`

	Options:
	  -h            display this help and exit
	  -oFILE        output file, default output is stdout
	  -lFILE        log file, default is stderr
	  -fFILE        file to extract, can be specified several times
	  -kFUNCTION    add FUNCTION to filters, format is:
	                FILTER:FUNCTION_NAME:SINGULAR,PLURAL,CONTEXT
	                default FILTERs are PHP and Latte
	                for SINGULAR, PLURAL and CONTEXT '0' means not set
	                can be specified several times
	  -mKEY:VALUE   set meta header

e.g.: `./vendor/bin/gettext-extractor -o outup/file.pot -f files/to/extract/`

Supported file types
--------------------
* .php
* .latte (Nette Latte templates)

License
-------
GettextExtractor is licensed under the New BSD License.

Based on code from [Karel Klíma](https://github.com/karelklima/gettext-extractor).
