<?php

/**
 * GettextExtractor
 * 
 * This source file is subject to the New BSD License.
 *
 * @copyright  Copyright (c) 2009 Karel Klíma
 * @license    New BSD License
 * @package    Nette Extras
 */

require_once dirname(__FILE__) . '/iFilter.php';
require_once dirname(__FILE__) . '/AFilter.php';

/**
 * Filter to fetch gettext phrases from PHP functions
 * @author Karel Klíma
 * @copyright  Copyright (c) 2009 Karel Klíma
 */
class PHPFilter extends AFilter implements iFilter {

	public function __construct() {
		$this->addFunction('gettext', 1);
		$this->addFunction('_', 1);
		$this->addFunction('ngettext', 1, 2);
		$this->addFunction('_n', 1, 2);
		$this->addFunction('pgettext', 2, null, 1);
		$this->addFunction('_p', 2, null, 1);
		$this->addFunction('npgettext', 2, 3, 1);
		$this->addFunction('_np', 2, 3, 1);
	}

	/**
	 * Parses given file and returns found gettext phrases
	 *
	 * @param string $file
	 * @return array
	 */
	public function extract($file) {
		$data = array();
		$iterator = new ArrayIterator(token_get_all(file_get_contents($file)));
		while ($iterator->valid()) {
			$token = $iterator->current();
			if ($token[0] === T_STRING && isset($this->functions[$token[1]])) {
				$definition = $this->functions[$token[1]];
				$message = array();
				$message[self::LINE] = $token[2];
				$position = 0;
				$iterator->next();
				while ($iterator->valid()) {
					$token = $iterator->current();
					if ($token === '(') {
						$position = 1;
					} elseif ($token === ',') {
						$position++;
					} elseif ($token === ')') {
						foreach ($definition as $type) {
							if (!isset($message[$type])) {
								break 2;
							}
						}
						$data[] = $message;
						$iterator->next();
						break;
					} elseif (is_array($token)) {
						 if ($token[0] === T_CONSTANT_ENCAPSED_STRING && isset($definition[$position])) {
							$message[$definition[$position]] = $this->stripQuotes(($this->fixEscaping($token[1])));
						} elseif ($token[0] === T_STRING) {
							continue 2;
						}
					}
					$iterator->next();
				}
			}
			$iterator->next();
		}
		return $data;
	}
}
