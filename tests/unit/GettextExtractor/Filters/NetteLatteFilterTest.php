<?php

require_once __DIR__ . '/FilterTest.php';

/**
 * Test class for GettextExtractor_Filters_NetteLatteFilter.
 * Generated by PHPUnit on 2010-12-15 at 21:59:47.
 */
class GettextExtractor_Filters_NetteLatteFilterTest extends GettextExtractor_Filters_FilterTest {

	protected function setUp() {
		$this->object = new GettextExtractor_Filters_NetteLatteFilter();
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
			GettextExtractor_Extractor::LINE => 1,
			GettextExtractor_Extractor::SINGULAR => 'Testovaci retezec'
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
			GettextExtractor_Extractor::LINE => 1,
			GettextExtractor_Extractor::SINGULAR => "A\nmultiline\nmessage!"
		), $messages);
	}
}
