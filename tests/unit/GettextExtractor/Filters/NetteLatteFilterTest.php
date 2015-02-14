<?php

use Vodacek\GettextExtractor as GE;

require_once __DIR__ . '/FilterTest.php';

class NetteLatteFilterTest extends FilterTest {

	protected function setUp() {
		$this->object = new GE\Filters\NetteLatteFilter();
		$this->file = __DIR__ . '/../../data/default.latte';
	}

	public function testNoValidMessages() {
		$messages = $this->object->extract(__DIR__ . '/../../data/noMessages.latte');
		$this->assertSame(array(), $messages);
	}

	public function testConstantsArrayMethodsAndFunctions() {
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
	public function testExtractMultilineMessage() {
		$messages = $this->object->extract(__DIR__ . '/../../data/bug6.latte');

		$this->assertContains(array(
			GE\Extractor::LINE => 1,
			GE\Extractor::SINGULAR => "A\nmultiline\nmessage!"
		), $messages);
	}
}
