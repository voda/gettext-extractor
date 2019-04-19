<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

class NetteFormsTest extends TestCase {

	/** @var GE\NetteExtractor */
	protected $object;

	protected function setUp(): void {
		$this->object = new GE\NetteExtractor('/dev/null');
		$this->object->setMeta('POT-Creation-Date', '2013-02-11T14:18:02+01:00');
	}

	public function testForm(): void {
		$this->object->setupForms();
		$this->object->scan('tests/integration/data/form.php');
		$temp = tempnam(sys_get_temp_dir(), __CLASS__);
		$this->object->save($temp);
		$this->assertFileEquals(__DIR__.'/data/form.pot', $temp);
	}
}
