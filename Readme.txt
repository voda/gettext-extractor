GettextExtractor v2
===================
Author: Karel Klima, Ondřej Vodáček
Copyright: 2009 Karel Klima
           2010 Ondřej Vodáček
License: New BSD License

Cool tool for extracting gettext phrases from PHP files and templates.

Works great with Nette Framework!


Usage gettext-extractor.php [options]

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
