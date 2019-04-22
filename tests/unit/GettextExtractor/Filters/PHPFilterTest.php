<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Unit\GettextExtractor\Filters;

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

class PHPFilterTest extends TestCase {

	/** @var GE\Filters\PHPFilter */
	private $object;

	protected function setUp(): void {
		$this->object = new GE\Filters\PHPFilter();
		$this->object->addFunction('addRule', 2);
	}

	public function testExtract(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/default.php');

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'A message!'
		), $messages);

		self::assertContains(array(
			GE\Extractor::LINE => 3,
			GE\Extractor::SINGULAR => 'Another message!',
			GE\Extractor::CONTEXT => 'context'
		), $messages);

		self::assertContains(array(
			GE\Extractor::LINE => 4,
			GE\Extractor::SINGULAR => 'I see %d little indian!',
			GE\Extractor::PLURAL => 'I see %d little indians!'
		), $messages);

		self::assertContains(array(
			GE\Extractor::LINE => 5,
			GE\Extractor::SINGULAR => 'I see %d little indian!',
			GE\Extractor::PLURAL => 'I see %d little indians!',
			GE\Extractor::CONTEXT => 'context'
		), $messages);
	}

	public function testNoValidMessages(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/noMessages.php');
		self::assertSame(array(), $messages);
	}

	public function testNestedFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/nestedFunctions.php');
		self::assertCount(5, $messages);

		self::assertContains(array(
			GE\Extractor::LINE => 4,
			GE\Extractor::SINGULAR => 'Nested function.'
		), $messages);
		self::assertContains(array(
			GE\Extractor::LINE => 5,
			GE\Extractor::SINGULAR => 'Nested function 2.',
			GE\Extractor::CONTEXT => 'context'
		), $messages);
		self::assertContains(array(
			GE\Extractor::LINE => 6,
			GE\Extractor::SINGULAR => "%d meeting wasn't imported.",
			GE\Extractor::PLURAL => "%d meetings weren't imported."
		), $messages);
		self::assertContains(array(
			GE\Extractor::LINE => 8,
			GE\Extractor::SINGULAR => 'Please provide a text 2.'
		), $messages);
		self::assertContains(array(
			GE\Extractor::LINE => 9,
			GE\Extractor::SINGULAR => 'Please provide a text 3.'
		), $messages);
	}

	public function testConstantAsParameter(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/constantAsParameter.php');

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Please provide a text.'
		), $messages);
	}

	public function testMessageWithNewlines(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/messageWithNewlines.php');

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => "A\nmessage!"
		), $messages);
	}

	public function testArrayAsParameter(): void {
		$this->object->addFunction('addConfirmer', 3);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/arrayAsParameter.php');

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Really delete?'
		), $messages);
	}

	public function testSingularAndPluralMessageFromOneParameter(): void {
		$this->object->addFunction('plural', 1, 1);
		$messages = $this->object->extract(__DIR__ . '/../../data/php/singularAndPluralMessageFromOneParameter.php');

		self::assertContains(array(
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

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'item 1'
		), $messages);
		self::assertContains(array(
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

		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Value A'
		), $messages);
		self::assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'Value B'
		), $messages);
	}

	public function testCallable(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/callable.php');
		self::assertEmpty($messages);
	}

	public function testStaticFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/php/staticFunctions.php');

		self::assertContains(array(
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
		self::assertEmpty($messages);
	}
}
