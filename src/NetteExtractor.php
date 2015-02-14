<?php
/**
 * GettextExtractor
 *
 * Cool tool for automatic extracting gettext strings for translation
 *
 * Works best with Nette Framework
 *
 * This source file is subject to the New BSD License.
 *
 * @copyright Copyright (c) 2009 Karel Klima
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor;

/**
 * NetteGettextExtractor tool - designed specially for use with Nette Framework
 *
 * @author Karel Klima
 * @author Ondřej Vodáček
 */
class NetteExtractor extends Extractor {

	/**
	 * Setup mandatory filters
	 *
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

		$this->getFilter('PHP')
				->addFunction('translate');

		$this->getFilter('Latte')
				->addFunction('!_')
				->addFunction('_');
	}

	/**
	 * Optional setup of Forms translations
	 *
	 * @return NetteGettextExtractor
	 */
	public function setupForms() {
		$php = $this->getFilter('PHP');
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
	 * @return NetteGettextExtractor
	 */
	public function setupDataGrid() {
		$php = $this->getFilter('PHP');
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
