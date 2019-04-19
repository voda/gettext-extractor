<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Integration;

use Vodacek\GettextExtractor as GE;

class LatteFilterTest extends FilterTest {

	protected function setUp(): void {
		$this->object = new GE\Filters\LatteFilter();
		$this->file = __DIR__ . '/../../data/default.latte';
	}

	public function testNoValidMessages(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/noMessages.latte');
		$this->assertSame(array(), $messages);
	}

	public function testConstantsArrayMethodsAndFunctions(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/test.latte');
		$this->assertCount(1, $messages);
		$expected = array(
			GE\Extractor::LINE => 1,
			GE\Extractor::SINGULAR => 'Testovaci retezec'
		);
		ksort($messages[0]);
		ksort($expected);
		$this->assertSame(array($expected), $messages);
	}

	/**
	 * @group bug6
	 */
	public function testExtractMultilineMessage(): void {
		$messages = $this->object->extract(__DIR__ . '/../../data/bug6.latte');

		$this->assertContains(array(
			GE\Extractor::LINE => 1,
			GE\Extractor::SINGULAR => "A\nmultiline\nmessage!"
		), $messages);
	}
}
