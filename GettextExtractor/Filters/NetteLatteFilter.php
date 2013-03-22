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
	 * @return self
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
	 * @return self
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
		if (count($this->functions) === 0) {
			return;
		}
		$data = array();

		$latteParser = new \Latte\Parser();
		$tokens = $latteParser->parse(file_get_contents($file));

		$functions = array_keys($this->functions);
		usort($functions, array(__CLASS__, 'functionNameComparator'));

		$phpParser = new PHPParser_Parser(new PHPParser_Lexer());
		foreach ($tokens as $token) {
			if ($token->type !== \Latte\Token::MACRO_TAG) {
				continue;
			}

			$name = $this->findMacroName($token->text, $functions);
			if (!$name) {
				continue;
			}
			$value = $this->trimMacroValue($name, $token->value);
			$stmts = $phpParser->parse("<?php\nf($value);");

			foreach ($this->functions[$name] as $definition) {
				$message = $this->processFunction($definition, $stmts[0]);
				if ($message) {
					$message[GettextExtractor_Extractor::LINE] = $token->line;
					$data[] = $message;
				}
			}
		}
		return $data;
	}

	/**
	 * @param array
	 * @param PHPParser_Node_Expr_FuncCall $node
	 * @return array
	 */
	private function processFunction(array $definition, PHPParser_Node_Expr_FuncCall $node) {
		foreach ($definition as $type => $position) {
			if (!isset($node->args[$position - 1])) {
				return;
			}
			$arg = $node->args[$position - 1]->value;
			if ($arg instanceof PHPParser_Node_Scalar_String) {
				$message[$type] = $arg->value;
			} else {
				return;
			}
		}
		return $message;
	}

	/**
	 * @param string
	 * @param array
	 * @return string|null
	 */
	private function findMacroName($text, array $functions) {
		foreach ($functions as $function) {
			if (strpos($text, '{'.$function) === 0) {
				return $function;
			}
		}
	}

	/**
	 * @param string
	 * @param string
	 * @return string
	 */
	private function trimMacroValue($name, $value) {
		$offset = strlen(ltrim($name, '!_'));
		return substr($value, $offset);
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return integer
	 * @internal
	 */
	public static function functionNameComparator($a, $b) {
		return strlen($b) - strlen($a);
	}
}
