<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Unit\GettextExtractor;

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor\NetteExtractor;

class ExtractorTest extends TestCase {

	/** @var NetteExtractor */
	protected $object;

	protected function setUp(): void {
		$this->object = new NetteExtractor('/dev/null');
		$this->object->setMeta('POT-Creation-Date', '2020-01-29T17:22:02+01:00');
	}

	public function testForm(): void {
		$this->object->setupForms();
		$this->object->scan('tests/unit/data/latte/backslash.latte');
		$temp = tempnam(sys_get_temp_dir(), __CLASS__);
		if ($temp === false) {
			throw new \RuntimeException('Failed to create temporary file.');
		}
		$this->object->save($temp);
		self::assertFileEquals(__DIR__.'/../data/pot/backslash.pot', $temp);
		unlink($temp);
	}
}
