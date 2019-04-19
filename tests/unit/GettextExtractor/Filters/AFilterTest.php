<?php

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

class AFilterTest extends TestCase {

	/** @var GE\Filters\AFilter */
	protected $object;

	protected function setUp(): void {
		$this->object = $this->getMockForAbstractClass(GE\Filters\AFilter::class);
	}

	/**
	 * @dataProvider dataProvider_AddingFunctionWithInvalidParamter
	 */
	public function testAddingFunctionWithInvalidParamter($s, $p, $c) {
		$this->expectException(InvalidArgumentException::class);
		$this->object->addFunction('function', $s, $p, $c);
	}
	public static function dataProvider_AddingFunctionWithInvalidParamter() {
		return array(
			array(0, null, null),
			array(1, 0, null),
			array(1, 2, 0),
			array('a', null, null),
			array(-2, null, null)
		);
	}
}
