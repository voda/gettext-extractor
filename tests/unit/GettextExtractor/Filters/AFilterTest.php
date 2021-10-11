<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Unit\GettextExtractor\Filters;

use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

class AFilterTest extends TestCase {

	/** @var MockObject&GE\Filters\AFilter */
	protected $object;

	protected function setUp(): void {
		$this->object = $this->getMockForAbstractClass(GE\Filters\AFilter::class);
	}

	/**
	 * @dataProvider dataProvider_AddingFunctionWithInvalidParameter
	 */
	public function testAddingFunctionWithInvalidParameter(int $s, ?int $p, ?int $c): void {
		$this->expectException(InvalidArgumentException::class);
		$this->object->addFunction('function', $s, $p, $c);
	}

	public static function dataProvider_AddingFunctionWithInvalidParameter(): array {
		return [
			[0, null, null],
			[1, 0, null],
			[1, 2, 0],
			[-2, null, null]
		];
	}
}
