<?php

/**
 * GettextExtractor
 *
 * Cool tool for automatic extracting gettext strings for translation
 *
 * Works best with Nette Framework
 *
 * This source file is subject to the New BSD License.
 *
 * @copyright  Copyright (c) 2010 Ondřej Vodáček
 * @license    New BSD License
 * @package    Nette Extras
 */

require_once dirname(__FILE__) . '/iFilter.php';

/**
 * Abstract filter class.
 *
 * @author Ondřej Vodáček
 */
abstract class AFilter {
	
    /** @var array */
    protected $functions = array();

    /**
     * Includes a function to parse gettext phrases from
	 *
     * @param $functionName string
     * @param $singular int
	 * @param $plural int|null
	 * @param $context int|null
     */
	protected  function addFunction($functionName, $singular = 1, $plural = null, $context = null) {
		if (!is_int($singular) || $singular <= 0) {
			throw new InvalidArgumentException('Invalid argument type or value given for paramater $singular.');
		}
        $function = array(
			$singular => iFilter::SINGULAR
		);
		if ($plural !== null) {
			if (!is_int($plural) || $plural <= 0) {
				throw new InvalidArgumentException('Invalid argument type or value given for paramater $plural.');
			}
			$function[$plural] = iFilter::PLURAL;
		}
		if ($context !== null) {
			if (!is_int($context) || $context <= 0) {
				throw new InvalidArgumentException('Invalid argument type or value given for paramater $context.');
			}
			$function[$context] = iFilter::CONTEXT;
		}
		$this->functions[$functionName] = $function;
    }

    /**
     * Excludes a function from the function list
	 *
     * @param $functionName
     */
    protected function removeFunction($functionName) {
        unset($this->functions[$functionName]);
    }

    /**
     * Excludes all functions from the function list
     */
    protected function removeAllFunctions() {
        $this->functions = array();
    }

    /**
     * Removes backslashes from before primes and double primes in primed or double primed strings respectively
	 *
     * @return string
	 * @author Matěj Humpál (https://github.com/finwe)
     */
    protected function fixEscaping($string) {
        $prime = substr($string, 0, 1);
        $string = str_replace('\\' . $prime, $prime, $string);

        return $string;
    }

	/**
	 * Remove single or double quotes from begin and end of the string.
	 *
	 * @param string $string
	 * @return string
	 */
	protected function stripQuotes($string) {
        $prime = substr($string, 0, 1);
		if ($prime === "'" || $prime === '"') {
			if (substr($string, -1, 1) === $prime) {
				$string = substr($string, 1, -1);
			}
		}
		return $string;
	}
    
}
?>
