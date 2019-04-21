<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Unit\GettextExtractor\Filters;

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

class LatteFilterTest extends TestCase {

	/** @var GE\Filters\LatteFilter */
	private $object;

	protected function setUp(): void {
		$this->object = new GE\Filters\LatteFilter();
	}

	public function testExtract(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/latte/default.latte');

		$this->assertIsArray($messages);

		$this->assertContains(array(
			GE\Extractor::LINE => 2,
			GE\Extractor::SINGULAR => 'A message!'
		), $messages);

		$this->assertContains(array(
			GE\Extractor::LINE => 3,
			GE\Extractor::SINGULAR => 'Another message!',
			GE\Extractor::CONTEXT => 'context'
		), $messages);

		$this->assertContains(array(
			GE\Extractor::LINE => 4,
			GE\Extractor::SINGULAR => 'I see %d little indian!',
			GE\Extractor::PLURAL => 'I see %d little indians!'
		), $messages);

		$this->assertContains(array(
			GE\Extractor::LINE => 5,
			GE\Extractor::SINGULAR => 'I see %d little indian!',
			GE\Extractor::PLURAL => 'I see %d little indians!',
			GE\Extractor::CONTEXT => 'context'
		), $messages);
	}

	public function testNoValidMessages(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/latte/noMessages.latte');
		$this->assertSame(array(), $messages);
	}

	public function testConstantsArrayMethodsAndFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/latte/test.latte');
		$this->assertCount(1, $messages);
		$expected = array(
			GE\Extractor::LINE => 1,
			GE\Extractor::SINGULAR => 'Message'
		);
		ksort($messages[0]);
		ksort($expected);
		$this->assertSame(array($expected), $messages);
	}

	/**
	 * @group bug6
	 */
	public function testExtractMultilineMessage(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/latte/bug6.latte');

		$this->assertContains(array(
			GE\Extractor::LINE => 1,
			GE\Extractor::SINGULAR => "A\nmultiline\nmessage!"
		), $messages);
	}
}
