<?php

require_once dirname(__FILE__).'/../../Filters/AFilter.php';
require_once dirname(__FILE__).'/../../Filters/iFilter.php';

/**
 * Description of FilterTest
 *
 * @author Ondřej Vodáček
 */
abstract class FilterTest extends PHPUnit_Framework_TestCase {

	/** @var AFilter */
	protected $object;

	/** @var string */
	protected $file;

	public function testExtract() {
		$messages = $this->object->extract($this->file);

		$this->assertInternalType('array', $messages);

		$this->assertContains(array(
			iFilter::LINE => 2,
			iFilter::SINGULAR => 'A message!'
		), $messages);

		$this->assertContains(array(
			iFilter::LINE => 3,
			iFilter::SINGULAR => 'Another message!',
			iFilter::CONTEXT => 'context'
		), $messages);

		$this->assertContains(array(
			iFilter::LINE => 4,
			iFilter::SINGULAR => 'I see %d little indian!',
			iFilter::PLURAL => 'I see %d little indians!'
		), $messages);

		$this->assertContains(array(
			iFilter::LINE => 5,
			iFilter::SINGULAR => 'I see %d little indian!',
			iFilter::PLURAL => 'I see %d little indians!',
			iFilter::CONTEXT => 'context'
		), $messages);
	}
    
}
