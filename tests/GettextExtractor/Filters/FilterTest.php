<?php

/**
 * Description of FilterTest
 *
 * @author Ondřej Vodáček
 */
abstract class GettextExtractor_Filters_FilterTest extends PHPUnit_Framework_TestCase {

	/** @var AFilter */
	protected $object;

	/** @var string */
	protected $file;

	public function testExtract() {
		$messages = $this->object->extract($this->file);

		$this->assertInternalType('array', $messages);

		$this->assertContains(array(
			GettextExtractor_Extractor::LINE => 2,
			GettextExtractor_Extractor::SINGULAR => 'A message!'
		), $messages);

		$this->assertContains(array(
			GettextExtractor_Extractor::LINE => 3,
			GettextExtractor_Extractor::SINGULAR => 'Another message!',
			GettextExtractor_Extractor::CONTEXT => 'context'
		), $messages);

		$this->assertContains(array(
			GettextExtractor_Extractor::LINE => 4,
			GettextExtractor_Extractor::SINGULAR => 'I see %d little indian!',
			GettextExtractor_Extractor::PLURAL => 'I see %d little indians!'
		), $messages);

		$this->assertContains(array(
			GettextExtractor_Extractor::LINE => 5,
			GettextExtractor_Extractor::SINGULAR => 'I see %d little indian!',
			GettextExtractor_Extractor::PLURAL => 'I see %d little indians!',
			GettextExtractor_Extractor::CONTEXT => 'context'
		), $messages);
	}

}
