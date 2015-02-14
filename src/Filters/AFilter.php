<?php
/**
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use Vodacek\GettextExtractor\Extractor;

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
	 * @return self
	 */
	public function addFunction($functionName, $singular = 1, $plural = null, $context = null) {
		if (!is_int($singular) || $singular <= 0) {
			throw new \InvalidArgumentException('Invalid argument type or value given for paramater $singular.');
		}
		$function = array(
			Extractor::SINGULAR => $singular
		);
		if ($plural !== null) {
			if (!is_int($plural) || $plural <= 0) {
				throw new \InvalidArgumentException('Invalid argument type or value given for paramater $plural.');
			}
			$function[Extractor::PLURAL] = $plural;
		}
		if ($context !== null) {
			if (!is_int($context) || $context <= 0) {
				throw new \InvalidArgumentException('Invalid argument type or value given for paramater $context.');
			}
			$function[Extractor::CONTEXT] = $context;
		}
		$this->functions[$functionName][] = $function;
		return $this;
	}

	/**
	 * Excludes a function from the function list
	 *
	 * @param $functionName
	 * @return self
	 */
	public function removeFunction($functionName) {
		unset($this->functions[$functionName]);
		return $this;
	}

	/**
	 * Excludes all functions from the function list
	 *
	 * @return self
	 */
	public function removeAllFunctions() {
		$this->functions = array();
		return $this;
	}
}
