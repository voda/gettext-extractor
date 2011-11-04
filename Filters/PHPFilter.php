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
			$key = $iterator->key();
			if ($token[0] === T_STRING) {
				if ($this->isFunction($iterator)) {
					if (isset($this->functions[$token[1]])) {
						$iterator->seek($key);
						$this->extractFunction($iterator, $data);
					}
				}
			}
			$iterator->next();
		}
		return $data;
	}

	private function extractParameter(ArrayIterator $iterator, array &$data) {
		$param = null;
		$valid = true;
		while ($iterator->valid()) {
			$token = $iterator->current();
			$key = $iterator->key();
			if ($token === ',' || $token === ')') {
				if (!$valid && is_string($param)) {
					$param = null;
				}
				return $param;
			} elseif ($token === '.') {
				$valid = false;
			} elseif (is_array($token) && $token[0] === T_CONSTANT_ENCAPSED_STRING) { //string
				$param = $this->stripQuotes(($this->fixEscaping($token[1])));
			} elseif (is_array($token) && $token[0] === T_STRING) { //constant or function
				if ($this->isFunction($iterator)) {
					$iterator->seek($key);
					$this->extractFunction($iterator, $data);
				}
				$valid = false;
			} elseif ($token === '(') {
				do {
					$iterator->next();
					$token = $iterator->current();
				} while ($token !== ')' && $iterator->valid());
			}
			$iterator->next();
		}
	}

	private function isFunction(ArrayIterator $iterator) {
		$key = $iterator->key();
		$iterator->next();
		while ($iterator->valid()) {
			$token = $iterator->current();
			if ($token === '(') {
				$iterator->seek($key);
				return true;
			} elseif (is_array($token) && $token[0] === T_WHITESPACE) {
				$iterator->next();
				continue;
			} else {
				$iterator->seek($key);
				return false;
			}
		}
		$iterator->seek($key);
		return false;
	}

	private function extractFunction(ArrayIterator $iterator, array &$data) {
		$token = $iterator->current();
		$definition = isset($this->functions[$token[1]]) ? $this->functions[$token[1]] : array();
		$message = array();
		$message[self::LINE] = $token[2];
		$position = 0;
		$iterator->next();
		while ($iterator->valid()) {
			$token = $iterator->current();
			if ($token === '(') {
				$position = 1;
				break;
			}
			$iterator->next();
		}
		$iterator->next();
		while ($iterator->valid()) {
			$param = $this->extractParameter($iterator, $data);
			if (isset($definition[$position]) && is_string($param)) {
				$message[$definition[$position]] = $param;
			}
			while ($iterator->valid()) {
				$token = $iterator->current();
				if ($token === ',') {
					$position++;
					break;
				} elseif ($token === ')') {
					break 2;
				}
				$iterator->next();
			}
			$iterator->next();
		}
		if (count($message) === 1) {
			return;
		}
		foreach ($definition as $type) {
			if (!isset($message[$type])) {
				return;
			}
		}
		$data[] = $message;
	}
}
