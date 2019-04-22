<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2009 Karel Klíma
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use Nette\Utils\FileSystem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Vodacek\GettextExtractor\Extractor;
use PhpParser;
use Latte;

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

	public function extract(string $file): array {
		$data = array();

		$latteParser = new Latte\Parser();
		$tokens = $latteParser->parse(FileSystem::read($file));

		$functions = array_keys($this->functions);
		usort($functions, static function(string $a, string $b) {
			return strlen($b) <=> strlen($a);
		});

		$phpParser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7);
		foreach ($tokens as $token) {
			if ($token->type !== Latte\Token::MACRO_TAG) {
				continue;
			}

			$name = $this->findMacroName($token->text, $functions);
			if ($name === null) {
				continue;
			}
			$value = $this->trimMacroValue($name, $token->value);
			$stmts = $phpParser->parse("<?php\nf($value);");

			if ($stmts === null) {
				continue;
			}
			if ($stmts[0] instanceof Expression && $stmts[0]->expr instanceof FuncCall) {
				foreach ($this->functions[$name] as $definition) {
					$message = $this->processFunction($definition, $stmts[0]->expr);
					if ($message !== []) {
						$message[Extractor::LINE] = $token->line;
						$data[] = $message;
					}
				}
			}
		}
		return $data;
	}

	private function processFunction(array $definition, FuncCall $node): array {
		$message = [];
		foreach ($definition as $type => $position) {
			if (!isset($node->args[$position - 1])) {
				return [];
			}
			$arg = $node->args[$position - 1]->value;
			if ($arg instanceof String_) {
				$message[$type] = $arg->value;
			} else {
				return [];
			}
		}
		return $message;
	}

	private function findMacroName(string $text, array $functions): ?string {
		foreach ($functions as $function) {
			if (strpos($text, '{'.$function) === 0) {
				return $function;
			}
		}
		return null;
	}

	private function trimMacroValue(string $name, string $value): string {
		$offset = strlen(ltrim($name, '!_'));
		return substr($value, $offset);
	}
}
