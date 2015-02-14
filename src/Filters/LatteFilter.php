<?php
/**
 * @copyright Copyright (c) 2009 Karel Klíma
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use Vodacek\GettextExtractor\Extractor;
use PhpParser;

class LatteFilter extends AFilter implements IFilter {

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

	public function extract($file) {
		$data = array();

		$latteParser = new \Latte\Parser();
		$tokens = $latteParser->parse(file_get_contents($file));

		$functions = array_keys($this->functions);
		usort($functions, array(__CLASS__, 'functionNameComparator'));

		$phpParser = new PhpParser\Parser(new PHPParser\Lexer());
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
					$message[Extractor::LINE] = $token->line;
					$data[] = $message;
				}
			}
		}
		return $data;
	}

	/**
	 * @param array
	 * @param PhpParser\Node\Expr\FuncCall $node
	 * @return array
	 */
	private function processFunction(array $definition, PhpParser\Node\Expr\FuncCall $node) {
		foreach ($definition as $type => $position) {
			if (!isset($node->args[$position - 1])) {
				return;
			}
			$arg = $node->args[$position - 1]->value;
			if ($arg instanceof PhpParser\Node\Scalar\String) {
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
