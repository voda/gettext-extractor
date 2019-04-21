<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2012 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use Nette\Utils\FileSystem;
use PhpParser;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Vodacek\GettextExtractor\Extractor;

class PHPFilter extends AFilter implements IFilter, PhpParser\NodeVisitor {

	/** @var array */
	private $data = [];

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

	public function extract(string $file): array {
		$this->data = array();
		$parser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7);
		$stmts = $parser->parse(FileSystem::read($file));
		if ($stmts === null) {
			return [];
		}
		$traverser = new PhpParser\NodeTraverser();
		$traverser->addVisitor($this);
		$traverser->traverse($stmts);
		$data = $this->data;
		$this->data = [];
		return $data;
	}

	public function enterNode(Node $node) {
		$name = null;
		$args = [];
		if (($node instanceof MethodCall || $node instanceof StaticCall) && $node->name instanceof Identifier && is_string($node->name->name)) {
			$name = $node->name->name;
			$args = $node->args;
		} elseif ($node instanceof FuncCall && $node->name instanceof Name) {
			$parts = $node->name->parts;
			$name = array_pop($parts);
			$args = $node->args;
		} else {
			return null;
		}
		if (!isset($this->functions[$name])) {
			return null;
		}
		foreach ($this->functions[$name] as $definition) {
			$this->processFunction($definition, $node, $args);
		}
	}

	/**
	 * @param array $definition
	 * @param Node $node
	 * @param Arg[] $args
	 */
	private function processFunction(array $definition, Node $node, array $args) {
		$message = array(
			Extractor::LINE => $node->getLine()
		);
		foreach ($definition as $type => $position) {
			if (!isset($args[$position - 1])) {
				return;
			}
			$arg = $args[$position - 1]->value;
			if ($arg instanceof String_) {
				$message[$type] = $arg->value;
			} elseif ($arg instanceof Array_) {
				foreach ($arg->items as $item) {
					if ($item->value instanceof String_) {
						$message[$type][] = $item->value->value;
					}
				}
				if (count($message) === 1) { // line only
					return;
				}
			} else {
				return;
			}
		}
		if (is_array($message[Extractor::SINGULAR])) {
			foreach ($message[Extractor::SINGULAR] as $value) {
				$tmp = $message;
				$tmp[Extractor::SINGULAR] = $value;
				$this->data[] = $tmp;
			}
		} else {
			$this->data[] = $message;
		}
	}

	/* PhpParser\NodeVisitor: dont need these *******************************/

	public function afterTraverse(array $nodes) {
	}

	public function beforeTraverse(array $nodes) {
	}

	public function leaveNode(Node $node) {
	}
}
