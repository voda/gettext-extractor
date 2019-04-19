<?php
declare(strict_types=1);

namespace Vodacek\GettextExtractor\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Vodacek\GettextExtractor as GE;

abstract class FilterTest extends TestCase {

	/** @var GE\Filters\IFilter */
	protected $object;

	/** @var string */
	protected $file;

	public function testExtract(): void {
		$messages = $this->object->extract($this->file);

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
}
