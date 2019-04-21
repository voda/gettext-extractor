<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2009 Karel Klima
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor;

use Vodacek\GettextExtractor\Filters\LatteFilter;
use Vodacek\GettextExtractor\Filters\PHPFilter;

class NetteExtractor extends Extractor {

	/**
	 * @param string|bool $logToFile
	 */
	public function __construct($logToFile = false) {
		parent::__construct($logToFile);

		// Clean up...
		$this->removeAllFilters();

		// Set basic filters
		$this->setFilter('php', 'PHP')
				->setFilter('phtml', 'PHP')
				->setFilter('phtml', 'Latte')
				->setFilter('latte', 'PHP')
				->setFilter('latte', 'Latte');

		$this->addFilter('Latte', new Filters\LatteFilter());

		$phpFilter = $this->getFilter('PHP');
		assert($phpFilter instanceof PHPFilter);

		$phpFilter->addFunction('translate');

		$latteFilter = $this->getFilter('Latte');
		assert($latteFilter instanceof LatteFilter);

		$latteFilter->addFunction('!_')
				->addFunction('_');
	}

	/**
	 * Optional setup of Forms translations
	 *
	 * @return self
	 */
	public function setupForms(): self {
		$php = $this->getFilter('PHP');
		assert($php instanceof PHPFilter);

		$php->addFunction('setText')
				->addFunction('setEmptyValue')
				->addFunction('setValue')
				->addFunction('addButton', 2)
				->addFunction('addCheckbox', 2)
				->addFunction('addError')
				->addFunction('addFile', 2) // Nette 0.9
				->addFunction('addGroup')
				->addFunction('addImage', 3)
				->addFunction('addMultiSelect', 2)
				->addFunction('addMultiSelect', 3)
				->addFunction('addPassword', 2)
				->addFunction('addRadioList', 2)
				->addFunction('addRadioList', 3)
				->addFunction('addRule', 2)
				->addFunction('addSelect', 2)
				->addFunction('addSelect', 3)
				->addFunction('addSubmit', 2)
				->addFunction('addText', 2)
				->addFunction('addTextArea', 2)
				->addFunction('addUpload', 2) // Nette 2.0
				->addFunction('setRequired')
				->addFunction('setDefaultValue')
				->addFunction('skipFirst') // Nette 0.9
				->addFunction('setPrompt') // Nette 2.0
				->addFunction('addProtection');

		return $this;
	}

	/**
	 * Optional setup of DataGrid component translations
	 *
	 * @return self
	 */
	public function setupDataGrid(): self {
		$php = $this->getFilter('PHP');
		assert($php instanceof PHPFilter);

		$php->addFunction('addColumn', 2)
				->addFunction('addNumericColumn', 2)
				->addFunction('addDateColumn', 2)
				->addFunction('addCheckboxColumn', 2)
				->addFunction('addImageColumn', 2)
				->addFunction('addPositionColumn', 2)
				->addFunction('addActionColumn')
				->addFunction('addAction');

		return $this;
	}
}
