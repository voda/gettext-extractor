<?php
/**
 * @copyright Copyright (c) 2012 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

use PhpParser;
use Vodacek\GettextExtractor\Extractor;

class PHPFilter extends AFilter implements IFilter, PhpParser\NodeVisitor {

	/** @var array */
	private $data;

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

	public function extract($file) {
		$this->data = array();
		$parser = (new PhpParser\ParserFactory())->create(PhpParser\ParserFactory::PREFER_PHP7);
		$stmts = $parser->parse(file_get_contents($file));
		$traverser = new PhpParser\NodeTraverser();
		$traverser->addVisitor($this);
		$traverser->traverse($stmts);
		$data = $this->data;
		$this->data = null;
		return $data;
	}

	public function enterNode(PhpParser\Node $node) {
		$name = null;
		if (($node instanceof PhpParser\Node\Expr\MethodCall || $node instanceof PhpParser\Node\Expr\StaticCall) && $node->name instanceof PhpParser\Node\Identifier && is_string($node->name->name)) {
			$name = $node->name->name;
		} elseif ($node instanceof PhpParser\Node\Expr\FuncCall && $node->name instanceof PhpParser\Node\Name) {
			$parts = $node->name->parts;
			$name = array_pop($parts);
		} else {
			return;
		}
		if (!isset($this->functions[$name])) {
			return;
		}
		foreach ($this->functions[$name] as $definition) {
			$this->processFunction($definition, $node);
		}
	}

	private function processFunction(array $definition, PhpParser\Node $node) {
		$message = array(
			Extractor::LINE => $node->getLine()
		);
		foreach ($definition as $type => $position) {
			if (!isset($node->args[$position - 1])) {
				return;
			}
			$arg = $node->args[$position - 1]->value;
			if ($arg instanceof PhpParser\Node\Scalar\String_) {
				$message[$type] = $arg->value;
			} elseif ($arg instanceof PhpParser\Node\Expr\Array_) {
				foreach ($arg->items as $item) {
					if ($item->value instanceof PhpParser\Node\Scalar\String_) {
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

	/*** PHPParser_NodeVisitor: dont need these *******************************/

	public function afterTraverse(array $nodes) {
	}

	public function beforeTraverse(array $nodes) {
	}

	public function leaveNode(PhpParser\Node $node) {
	}
}
