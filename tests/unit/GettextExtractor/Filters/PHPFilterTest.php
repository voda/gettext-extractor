<?php

use Vodacek\GettextExtractor as GE;

require_once __DIR__ . '/FilterTest.php';

class PHPFilterTest extends FilterTest {

	protected function setUp(): void {
		$this->object = new GE\Filters\PHPFilter();
		$this->object->addFunction('addRule', 2);
		$this->file = __DIR__ . '/../../data/default.php';
	}

	public function testNoValidMessages() {
		$messages = $this->object->extract(__DIR__ . '/../../data/noMessages.php');
		$this->assertSame(array(), $messages);
	}

	public function testNestedFunctions() {
		$messages = $this->object->extract(__DIR__ . '/../../data/nestedFunctions.php');
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
			GE\Extractor::PLURAL => "%d meetings weren't importeded."
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 8,
			GE\Extractor::SINGULAR => "Please provide a text 2."
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 9,
			GE\Extractor::SINGULAR => "Please provide a text 3."
		), $messages);
	}

	public function testConstantAsParameter() {
		$messages = $this->object->extract(__DIR__ . '/../../data/constantAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "Please provide a text."
		), $messages);
	}

	public function testMessageWithNewlines() {
		$messages = $this->object->extract(__DIR__ . '/../../data/messageWithNewlines.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "A\nmessage!"
		), $messages);
	}

	public function testArrayAsParameter() {
		$this->object->addFunction('addConfirmer', 3);
		$messages = $this->object->extract(__DIR__ . '/../../data/arrayAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "Really delete?"
		), $messages);
	}

	public function testSingularAndPluralMessageFromOneParameter() {
		$this->object->addFunction('plural', 1, 1);
		$messages = $this->object->extract(__DIR__ . '/../../data/singularAndPluralMessageFromOneParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "%d weeks ago",
			GE\Extractor::PLURAL => "%d weeks ago",
		), $messages);
	}

	/**
	 * @group bug5
	 */
	public function testArrayWithTranslationsAsParameter() {
		$this->object->addFunction('addSelect', 3);
		$messages = $this->object->extract(__DIR__ . '/../../data/arrayWithTranslationsAsParameter.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "item 1"
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "item 2"
		), $messages);
	}

	/**
	 * @group bug3
	 */
	public function testMultipleMessagesFromSingleFunction() {
		$this->object->addFunction('bar', 1);
		$this->object->addFunction('bar', 2);
		$messages = $this->object->extract(__DIR__ . '/../../data/multipleMessagesFromSingleFunction.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "Value A"
		), $messages);
		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "Value B"
		), $messages);
	}

	public function testCallable() {
		$messages = $this->object->extract(__DIR__ . '/../../data/callable.php');
		$this->assertEmpty($messages);
	}

	public function testStaticFunctions() {
		$messages = $this->object->extract(__DIR__ . '/../../data/staticFunctions.php');

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "Static function"
		), $messages);
	}

	/**
	 * @group bug11
	 */
	public function testNoMessagesInArray() {
		$this->object->addFunction('translateArray');
		$messages = $this->object->extract(__DIR__ . '/../../data/bug11.php');
		$this->assertEmpty($messages);
	}
}
