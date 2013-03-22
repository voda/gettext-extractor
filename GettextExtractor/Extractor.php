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

/**
 * GettextExtractor tool
 *
 * @author Karel Klima
 * @author Ondřej Vodáček
 */
class GettextExtractor_Extractor {

	const LOG_FILE = 'extractor.log';
	const ESCAPE_CHARS = '"';
	const OUTPUT_PO = 'PO';
	const OUTPUT_POT = 'POT';

	const CONTEXT = 'context';
	const SINGULAR = 'singular';
	const PLURAL = 'plural';
	const LINE = 'line';
	const FILE = 'file';

	/** @var resource */
	protected $logHandler;

	/** @var array */
	protected $inputFiles = array();

	/** @var array */
	protected $filters = array(
		'php' => array('PHP')
	);

	/** @var array */
	protected $filterStore = array();

	/** @var array */
	protected $comments = array(
		'Gettext keys exported by GettextExtractor'
	);

	/** @var array */
	protected $meta = array(
		"POT-Creation-Date" => "",
		"PO-Revision-Date" => "YEAR-MO-DA HO:MI+ZONE",
		"Last-Translator" => "FULL NAME <EMAIL@ADDRESS>",
		"Language-Team" => "LANGUAGE <LL@li.org>",
		"MIME-Version" => "1.0",
		"Content-Type" => "text/plain; charset=UTF-8",
		"Content-Transfer-Encoding" => "8bit",
		"Plural-Forms" => "nplurals=INTEGER; plural=EXPRESSION;"
	);

	/** @var array */
	protected $data = array();

	/** @var string */
	protected $outputMode = self::OUTPUT_PO;

	/**
	 * Log setup
	 *
	 * @param string|bool $logToFile Bool or path of custom log file
	 */
	public function __construct($logToFile = false) {
		if ($logToFile === false) {
			$logToFile = 'php://stderr';
		} elseif (!is_string($logToFile)) { // default log file
			$logToFile = self::LOG_FILE;
		}
		$this->logHandler = fopen($logToFile, "w");
		$this->setOutputMode(self::OUTPUT_POT);
		$this->addFilter('PHP', new GettextExtractor_Filters_PHPFilter());
		$this->setMeta('POT-Creation-Date', date('c'));
	}

	/**
	 * Close the log hangdler if needed
	 */
	public function __destruct() {
		if (is_resource($this->logHandler)) {
			fclose($this->logHandler);
		}
	}

	/**
	 * Writes messages into log or dumps them on screen
	 *
	 * @param string $message
	 */
	public function log($message) {
		if (is_resource($this->logHandler)) {
			fwrite($this->logHandler, $message."\n");
		} else {
			echo $message."\n";
		}
	}

	/**
	 * Exception factory
	 *
	 * @param string $message
	 * @throws Exception
	 */
	protected function throwException($message) {
		$message = $message ? $message : 'Something unexpected occured. See GettextExtractor log for details';
		$this->log($message);
		throw new Exception($message);
	}

	/**
	 * Scans given files or directories and extracts gettext keys from the content
	 *
	 * @param string|array $resource
	 * @return self
	 */
	public function scan($resource) {
		$this->inputFiles = array();
		if (!is_array($resource)) {
			$resource = array($resource);
		}
		foreach ($resource as $item) {
			$this->log("Scanning '$item'");
			$this->_scan($item);
		}
		$this->_extract($this->inputFiles);
		return $this;
	}

	/**
	 * Scans given files or directories (recursively)
	 *
	 * @param string $resource File or directory
	 */
	protected function _scan($resource) {
		if (is_file($resource)) {
			$this->inputFiles[] = $resource;
		} elseif (is_dir($resource)) {
			$iterator = new RecursiveIteratorIterator(
					new RecursiveDirectoryIterator($resource, RecursiveDirectoryIterator::SKIP_DOTS)
			);
			foreach ($iterator as $file) {
				$this->inputFiles[] = $file->getPathName();
			}
		} else {
			$this->throwException("Resource '$resource' is not a directory or file");
		}
	}

	/**
	 * Extracts gettext keys from input files
	 *
	 * @param array $inputFiles
	 * @return array
	 */
	protected function _extract($inputFiles) {
		$inputFiles = array_unique($inputFiles);
		sort($inputFiles);
		foreach ($inputFiles as $inputFile) {
			if (!file_exists($inputFile)) {
				$this->throwException('ERROR: Invalid input file specified: '.$inputFile);
			}
			if (!is_readable($inputFile)) {
				$this->throwException('ERROR: Input file is not readable: '.$inputFile);
			}

			$this->log('Extracting data from file '.$inputFile);

			$fileExtension = pathinfo($inputFile, PATHINFO_EXTENSION);
			foreach ($this->filters as $extension => $filters) {
				// Check file extension
				if ($fileExtension !== $extension) {
					continue;
				}

				$this->log('Processing file '.$inputFile);

				foreach ($filters as $filterName) {
					$filter = $this->getFilter($filterName);
					$filterData = $filter->extract($inputFile);
					$this->log('  Filter '.$filterName.' applied');
					$this->addMessages($filterData, $inputFile);
				}
			}
		}
		return $this->data;
	}

	/**
	 * Gets an instance of a GettextExtractor filter
	 *
	 * @param string $filterName
	 * @return GettextExtractor_Filters_IFilter
	 */
	public function getFilter($filterName) {
		if (isset($this->filterStore[$filterName])) {
			return $this->filterStore[$filterName];
		}
		$this->throwException("ERROR: Filter '$filterName' not found.");
	}

	/**
	 * Assigns a filter to an extension
	 *
	 * @param string $extension
	 * @param string $filterName
	 * @return self
	 */
	public function setFilter($extension, $filterName) {
		if (isset($this->filters[$extension]) && in_array($filterName, $this->filters[$extension])) {
			return $this;
		}
		$this->filters[$extension][] = $filterName;
		return $this;
	}

	/**
	 * Add a filter object
	 *
	 * @param type $filterName
	 * @param GettextExtractor_Filters_IFilter $filter
	 */
	public function addFilter($filterName, GettextExtractor_Filters_IFilter $filter) {
		$this->filterStore[$filterName] = $filter;
	}

	/**
	 * Removes all filter settings in case we want to define a brand new one
	 *
	 * @return self
	 */
	public function removeAllFilters() {
		$this->filters = array();
		return $this;
	}

	/**
	 * Adds a comment to the top of the output file
	 *
	 * @param string $value
	 * @return self
	 */
	public function addComment($value) {
		$this->comments[] = $value;
		return $this;
	}

	/**
	 * Gets a value of a meta key
	 *
	 * @param string $key
	 */
	public function getMeta($key) {
		return isset($this->meta[$key]) ? $this->meta[$key] : null;
	}

	/**
	 * Sets a value of a meta key
	 *
	 * @param string $key
	 * @param string $value
	 * @return self
	 */
	public function setMeta($key, $value) {
		$this->meta[$key] = $value;
		return $this;
	}

	/**
	 * Saves extracted data into gettext file
	 *
	 * @param string $outputFile
	 * @param array $data
	 * @return self
	 */
	public function save($outputFile, $data = null) {
		$data = $data ? $data : $this->data;

		// Output file permission check
		if (file_exists($outputFile) && !is_writable($outputFile)) {
			$this->throwException('ERROR: Output file is not writable!');
		}

		$handle = fopen($outputFile, "w");

		fwrite($handle, $this->formatData($data));

		fclose($handle);

		$this->log("Output file '$outputFile' created");

		return $this;
	}

	/**
	 * Formats fetched data to gettext syntax
	 *
	 * @param array $data
	 * @return string
	 */
	protected function formatData($data) {
		$output = array();
		foreach ($this->comments as $comment) {
			$output[] = '# '.$comment;
		}
		$output[] = '#, fuzzy';
		$output[] = 'msgid ""';
		$output[] = 'msgstr ""';
		foreach ($this->meta as $key => $value) {
			$output[] = '"'.$key.': '.$value.'\n"';
		}
		$output[] = '';

		foreach ($data as $message) {
			foreach ($message['files'] as $file) {
				$output[] = '#: '.$file[self::FILE].':'.$file[self::LINE];
			}
			if (isset($message[self::CONTEXT])) {
				$output[] = $this->formatMessage($message[self::CONTEXT], "msgctxt");
			}
			$output[] = $this->formatMessage($message[self::SINGULAR], 'msgid');
			if (isset($message[self::PLURAL])) {
				$output[] = $this->formatMessage($message[self::PLURAL], 'msgid_plural');
				switch ($this->outputMode) {
					case self::OUTPUT_POT:
						$output[] = 'msgstr[0] ""';
						$output[] = 'msgstr[1] ""';
						break;
					case self::OUTPUT_PO: // fallthrough
					default:
						$output[] = $this->formatMessage($message[self::SINGULAR], 'msgstr[0]');
						$output[] = $this->formatMessage($message[self::PLURAL], 'msgstr[1]');
				}
			} else {
				switch ($this->outputMode) {
					case self::OUTPUT_POT:
						$output[] = 'msgstr ""';
						break;
					case self::OUTPUT_PO: // fallthrough
					default:
						$output[] = $this->formatMessage($message[self::SINGULAR], 'msgstr');
				}
			}

			$output[] = '';
		}

		return join("\n", $output);
	}

	/**
	 * Escape a sring not to break the gettext syntax
	 *
	 * @param string $string
	 * @return string
	 */
	protected function addSlashes($string) {
		return addcslashes($string, self::ESCAPE_CHARS);
	}

	/**
	 * Sets output mode.
	 * On OUTPUT_PO, english msgstrs will be generated,
	 * on OUTPUT_POT, msgstrs will be empty
	 *
	 * @param string $outputMode
	 * @return string
	 */
	public function setOutputMode($outputMode) {
		$this->outputMode = $outputMode;
		return $this;
	}

	protected function addMessages(array $messages, $file) {
		foreach ($messages as $message) {
			$key = '';
			if (isset($message[self::CONTEXT])) {
				$key .= $message[self::CONTEXT];
			}
			$key .= chr(4);
			$key .= $message[self::SINGULAR];
			$key .= chr(4);
			if (isset($message[self::PLURAL])) {
				$key .= $message[self::PLURAL];
			}
			if ($key === chr(4).chr(4)) {
				continue;
			}
			$line = $message[self::LINE];
			if (!isset($this->data[$key])) {
				unset($message[self::LINE]);
				$this->data[$key] = $message;
				$this->data[$key]['files'] = array();
			}
			$this->data[$key]['files'][] = array(
				self::FILE => $file,
				self::LINE => $line
			);
		}
	}

	protected function formatMessage($message, $prefix = null) {
		$message = $this->addSlashes($message);
		$message = '"' . str_replace("\n", "\\n\"\n\"", $message) . '"';
		return ($prefix ? $prefix.' ' : '') . $message;
	}
}
