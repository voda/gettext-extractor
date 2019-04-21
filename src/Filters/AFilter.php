<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use InvalidArgumentException;
use Vodacek\GettextExtractor\Extractor;

abstract class AFilter {

	/** @var array */
	protected $functions = array();

	/**
	 * Includes a function to parse gettext phrases from
	 *
	 * @param string $functionName
	 * @param int $singular
	 * @param int|null $plural
	 * @param int|null $context
	 * @return self
	 */
	public function addFunction(string $functionName, int $singular = 1, int $plural = null, int $context = null): self {
		if (!is_int($singular) || $singular <= 0) {
			throw new InvalidArgumentException('Invalid argument type or value given for parameter $singular.');
		}
		$function = array(
			Extractor::SINGULAR => $singular
		);
		if ($plural !== null) {
			if (!is_int($plural) || $plural <= 0) {
				throw new InvalidArgumentException('Invalid argument type or value given for parameter $plural.');
			}
			$function[Extractor::PLURAL] = $plural;
		}
		if ($context !== null) {
			if (!is_int($context) || $context <= 0) {
				throw new InvalidArgumentException('Invalid argument type or value given for parameter $context.');
			}
			$function[Extractor::CONTEXT] = $context;
		}
		$this->functions[$functionName][] = $function;
		return $this;
	}

	/**
	 * Excludes a function from the function list
	 *
	 * @param string $functionName
	 * @return self
	 */
	public function removeFunction(string $functionName): self {
		unset($this->functions[$functionName]);
		return $this;
	}

	/**
	 * Excludes all functions from the function list
	 *
	 * @return self
	 */
	public function removeAllFunctions(): self {
		$this->functions = array();
		return $this;
	}
}
