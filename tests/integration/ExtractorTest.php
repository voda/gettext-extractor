<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor\NetteExtractor;

class ExtractorTest extends TestCase {

	/** @var NetteExtractor */
	protected $object;

	protected function setUp(): void {
		$this->object = new NetteExtractor('/dev/null');
		$this->object->setMeta('POT-Creation-Date', '2020-01-29T17:22:02+01:00');
	}

	public function testBackslash(): void {
		$this->object->scan('tests/integration/data/backslash.php');
		$temp = tempnam(sys_get_temp_dir(), __CLASS__);
		if ($temp === false) {
			throw new \RuntimeException('Failed to create temporary file.');
		}
		$this->object->save($temp);
		self::assertFileEquals(__DIR__.'/data/backslash.pot', $temp);
		unlink($temp);
	}
}
