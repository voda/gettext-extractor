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
 * @package Nette Extras
 */

/**
 * NetteGettextExtractor tool - designed specially for use with Nette Framework
 *
 * @author Karel Klima
 * @author Ondřej Vodáček
 * @package Nette Extras
 */
class GettextExtractor_NetteExtractor extends GettextExtractor_Extractor {

	/**
	 * Setup mandatory filters
	 *
	 * @param string|bool $logToFile
	 */
	public function __construct($logToFile = FALSE) {
		parent::__construct($logToFile);

		// Clean up...
		$this->removeAllFilters();

		// Set basic filters
		$this->setFilter('php', 'PHP')
				->setFilter('phtml', 'PHP')
				->setFilter('phtml', 'NetteLatte')
				->setFilter('latte', 'PHP')
				->setFilter('latte', 'NetteLatte');

		$this->addFilter('NetteLatte', new GettextExtractor_Filters_NetteLatteFilter());

		$this->getFilter('PHP')
				->addFunction('translate');

		$this->getFilter('NetteLatte')
				->addPrefix('!_')
				->addPrefix('_');
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
				->addFunction('addFile', 2)
				->addFunction('addGroup')
				->addFunction('addImage', 3)
				->addFunction('addmultiSelect', 2)
				->addFunction('addPassword', 2)
				->addFunction('addRadioList', 2)
				->addFunction('addRule', 2)
				->addFunction('addSelect', 2)
				->addFunction('addSubmit', 2)
				->addFunction('addText', 2)
				->addFunction('addTextArea', 2)
				->addFunction('setRequired')
				->addFunction('skipFirst')
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
