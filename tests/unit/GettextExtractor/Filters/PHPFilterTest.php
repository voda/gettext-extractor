<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Integration;

use Vodacek\GettextExtractor as GE;

require_once __DIR__ . '/FilterTest.php';

class PHPFilterTest extends FilterTest {

	protected function setUp(): void {
		$this->object = new GE\Filters\PHPFilter();
		$this->object->addFunction('addRule', 2);
		$this->file = __DIR__ . '/../../data/php/default.php';
	}

	public function testNoValidMessages(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/noMessages.php');
		$this->assertSame(array(), $messages);
	}

	public function testNestedFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/nestedFunctions.php');
		$this->assertCount(5, $messages);

		$this->assertContains(array(
			GE\Extractor::LINE => 4,
			GE\Extractor::SINGULAR => 'Nested function.'
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 5,
			GE\Extractor::SINGULAR => 'Nested function 2.',
			GE\Extractor::CONTEXT => 'context'
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 6,
			GE\Extractor::SINGULAR => "%d meeting wasn't imported.",
			GE\Extractor::PLURAL => "%d meetings weren't imported."
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 8,
			GE\Extractor::SINGULAR => 'Please provide a text 2.'
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 9,
			GE\Extractor::SINGULAR => 'Please provide a text 3.'
		), $messages);
	}

	public function testConstantAsParameter(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/constantAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Please provide a text.'
		), $messages);
	}

	public function testMessageWithNewlines(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/messageWithNewlines.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "A\nmessage!"
		), $messages);
	}

	public function testArrayAsParameter(): void {
		$this->object->addFunction('addConfirmer', 3);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/arrayAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Really delete?'
		), $messages);
	}

	public function testSingularAndPluralMessageFromOneParameter(): void {
		$this->object->addFunction('plural', 1, 1);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/singularAndPluralMessageFromOneParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => '%d weeks ago',
			GE\Extractor::PLURAL => '%d weeks ago',
		), $messages);
	}

	/**
	 * @group bug5
	 */
	public function testArrayWithTranslationsAsParameter(): void {
		$this->object->addFunction('addSelect', 3);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/arrayWithTranslationsAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'item 1'
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'item 2'
		), $messages);
	}

	/**
	 * @group bug3
	 */
	public function testMultipleMessagesFromSingleFunction(): void {
		$this->object->addFunction('bar', 1);
		$this->object->addFunction('bar', 2);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/multipleMessagesFromSingleFunction.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Value A'
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Value B'
		), $messages);
	}

	public function testCallable(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/callable.php');
		$this->assertEmpty($messages);
	}

	public function testStaticFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/staticFunctions.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Static function'
		), $messages);
	}

	/**
	 * @group bug11
	 */
	public function testNoMessagesInArray(): void {
		$this->object->addFunction('translateArray');
		$messages = $this->object->extract(__DIR__ . '/../../data/php/bug11.php');
		$this->assertEmpty($messages);
	}
}
