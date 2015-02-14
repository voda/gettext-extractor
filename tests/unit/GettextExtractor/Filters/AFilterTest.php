<?php

use Vodacek\GettextExtractor as GE;

class AFilterTest extends \PHPUnit_Framework_TestCase {

	/** @var AFilter */
	protected $object;

	protected function setUp() {
		$this->object = $this->getMockForAbstractClass(GE\Filters\AFilter::class);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @dataProvider dataProvider_AddingFunctionWithInvalidParamter
	 */
	public function testAddingFunctionWithInvalidParamter($s, $p, $c) {
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
