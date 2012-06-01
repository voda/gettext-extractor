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
 * @copyright Copyright (c) 2009 Karel Klíma
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 * @package Nette Extras
 */

/**
 * Filter to parse curly brackets syntax in Nette Framework templates
 * @author Karel Klíma
 * @author Ondřej Vodáček
 */
class GettextExtractor_Filters_NetteLatteFilter extends GettextExtractor_Filters_AFilter implements GettextExtractor_Filters_IFilter {

	/** @internal single & double quoted PHP string, from Nette\Templates\LatteFilter */
	const RE_STRING = '\'(?:\\\\.|[^\'\\\\])*\'|"(?:\\\\.|[^"\\\\])*"';

	/** @internal PHP identifier, from Nette\Templates\LatteFilter */
	const RE_IDENTIFIER = '[_a-zA-Z\x7F-\xFF][_a-zA-Z0-9\x7F-\xFF]*';

	const RE_ARGS = '\(.*?\)';
	const RE_FUNCTION = '__IDENTIFIER____ARGS__(?:->__IDENTIFIER____ARGS__)*'; // Function can return object, so fluent interface is applicable
	const RE_KEY = '\[.*?\]';
	const RE_VARIABLE = '\$__IDENTIFIER__(?:__KEY__)*(?:__ARGS__)?(?:->__FUNCTION__)?'; // It's possible to access multidimensional array, variable functions and objects' fluent interface
	const RE_STATIC = '__IDENTIFIER__(?:::(?:__IDENTIFIER__|__FUNCTION__|__VARIABLE__))?';

	const RE_MODIFIER = '\\s*\|[^|}]+';

	const RE_NUMBER = '\d+';

	/** @link http://doc.nette.org/cs/rozsireni-lattefilter */
	const RE_TAG = '\{(__MACRO__)\s*(__PARAM__)((?:,\s*__PARAM__)+)?(?:__MODIFIER__)*\}';


	protected static $regexForParam;

	/** @var array */
	protected $prefixes = array();

	public function __construct() {
		$this->addFunction('_');
		$this->addFunction('!_');
		$this->addFunction('_n', 1, 2);
		$this->addFunction('!_n', 1, 2);
		$this->addFunction('_p', 2, null, 1);
		$this->addFunction('!_p', 2, null, 1);
		$this->addFunction('_np', 2, 3, 1);
		$this->addFunction('!_np', 2, 3, 1);
	}

	/**
	 * Includes a prefix to match in { }
	 * Alias for AFilter::addFunction
	 *
	 * @param $prefix string
	 * @param $singular int
	 * @param $plural int|null
	 * @param $context int|null
	 * @return NetteLatteFilter
	 */
	public function addPrefix($prefix, $singular = 1, $plural = null, $context = null) {
		parent::addFunction($prefix, $singular, $plural, $context);
		return $this;
	}

	/**
	 * Excludes a prefix from { }
	 * Alias for AFilter::removeFunction
	 *
	 * @param string $prefix
	 * @return NetteLatteFilter
	 */
	public function removePrefix($prefix) {
		parent::removeFunction($prefix);
		return $this;
	}

	/**
	 * Parses given file and returns found gettext phrases
	 *
	 * @param string $file
	 * @return array
	 */
	public function extract($file) {
		if (count($this->functions) === 0)
			return;
		$data = array();

		$regex = $this->createRegex(array_keys($this->functions));
		$paramsRegex = '/,\s*(__PARAM__)/';
		$paramsRegex = str_replace('__PARAM__', $this->createRegexForParam(), $paramsRegex);

		// parse file by lines
		foreach (file($file) as $line => $contents) {
			$matches = array();
			preg_match_all($regex, $contents, $matches, PREG_SET_ORDER);

			foreach ($matches as $message) {
				/* $message[0] = complete macro
				 * $message[1] = prefix
				 * $message[2] = 1. parameter
				 * $message[3] = additional parameters
				 */
				$prefix = $this->functions[$message[1]][0];
				$params = array(
					1 => $message[2]
				);
				if (isset($message[3])) {
					$m = array();
					preg_match_all($paramsRegex, $message[3], $m, PREG_SET_ORDER);
					foreach ($m as $index => $match) {
						$params[$index + 2] = $match[1];
					}
				}
				$result = array(
					GettextExtractor_Extractor::LINE => $line + 1
				);
				foreach ($prefix as $position => $type) {
					if (!isset($params[$position]) || !$this->isStaticString($params[$position])) {
						continue 2; // continue with next message
					}
					$result[$type] = $this->stripQuotes($this->fixEscaping($params[$position]));
				}
				$data[] = $result;
			}
		}
		return $data;
	}

	/**
	 * Return a regular expression for matching a parameter.
	 *
	 * @return string
	 */
	private function createRegexForParam() {
		if (!isset(self::$regexForParam)) {
			self::$regexForParam = '(?:'.self::RE_NUMBER.'|'.self::RE_STRING.'|'.self::RE_STATIC.'|'.self::RE_VARIABLE.'|'.self::RE_FUNCTION.')';
			$replace = array(
				'__STATIC__' => self::RE_STATIC,
				'__VARIABLE__' => self::RE_VARIABLE,
				'__FUNCTION__' => self::RE_FUNCTION,
				'__ARGS__' => self::RE_ARGS,
				'__KEY__' => self::RE_KEY,
			);
			self::$regexForParam = str_replace(
				array_keys($replace),
				array_values($replace),
				self::$regexForParam
			);
		}
		return self::$regexForParam;
	}

	/**
	 * Return a regular expression for matching macro.
	 *
	 * @param array $macros
	 * @return string
	 */
	private function createRegex(array $macros) {
		$quotedMacros = array();
		foreach ($macros as $prefix) {
			$quotedMacros[] = preg_quote($prefix);
		}
		$replace = array(
			'__MACRO__' => implode('|', $quotedMacros),
			'__PARAM__' => $this->createRegexForParam(),
			'__IDENTIFIER__' => self::RE_IDENTIFIER,
			'__MODIFIER__' => self::RE_MODIFIER,
		);
		$regex = str_replace(
				array_keys($replace),
				array_values($replace),
				self::RE_TAG
		);

		return "/$regex/";
	}

	/**
	 * Check if string is static. No variables, no composing.
	 *
	 * @param string $string
	 * @return bool
	 */
	private function isStaticString($string) {
		$prime = substr($string,0,1);
		if (($prime === "'" || $prime === '"') && substr($string, -1, 1) === $prime) {
			return true;
		}
		/** @todo more tests needed: "some$string" "{$object->method()}" */
		return false;
	}
}
