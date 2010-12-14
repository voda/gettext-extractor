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
 * @copyright  Copyright (c) 2009 Karel Klima
 * @license    New BSD License
 * @package    Nette Extras
 * @version    GettextExtractor 2.0, 2009-10-21
 */

if (version_compare(PHP_VERSION, '5.2.2', '<'))
    exit('GettextExtractor needs PHP 5.2.2 or newer');

/**
 * GettextExtractor tool
 *
 * @author     Karel Klima
 * @copyright  Copyright (c) 2009 Karel Klíma
 * @package    Nette Extras
 */    
class GettextExtractor
{
    const LOG_FILE = '/extractor.log';
    const ESCAPE_CHARS = '"';
    const OUTPUT_PO = 'PO';
    const OUTPUT_POT = 'POT';
    /** @var resource */
    protected $logHandler;
    /** @var array */
    protected $inputFiles = array();
    /** @var array */
    protected $filters = array(
        'php' => array('PHP'),
        'phtml'    => array('PHP', 'NetteLatte')
    );
    /** @var array */
    protected $comments = array(
        'Gettext keys exported by GettextExtractor'
    );
    /** @var array */
    protected $meta = array(
		"PO-Revision-Date" => "YEAR-MO-DA HO:MI+ZONE",
		"Last-Translator" => "FULL NAME <EMAIL@ADDRESS>",
		"Language-Team" => "LANGUAGE <LL@li.org>",
		"MIME-Version" => "1.0",
		"Content-Type" => "text/plain; charset=CHARSET",
		"Content-Transfer-Encoding" => "8bit",
		"Plural-Forms" => "nplurals=INTEGER; plural=EXPRESSION;"
    );
    /** @var array */
    protected $data = array();
    /** @var array */
    protected $filterStore = array();

    /** @var string */
    protected $outputMode = self::OUTPUT_PO;
    
    /**
     * Log setup
     * @param string|bool $logToFile Bool or path of custom log file
     */
    public function __construct($logToFile = false)
    {
        if (is_string($logToFile)) { // custom log file
            $this->logHandler = fopen($logToFile, "w");
        } elseif ($logToFile) { // default log file
            $this->logHandler = fopen(dirname(__FILE__) . self::LOG_FILE, "w");
        }
    }
    
    /**
     * Close the log hangdler if needed
     */
    public function __destruct()
    {
        if (is_resource($this->logHandler)) fclose($this->logHandler);
    }
    
    /**
     * Writes messages into log or dumps them on screen
     * @param string $message
     */
    public function log($message)
    {
        if (is_resource($this->logHandler)) {
            fwrite($this->logHandler, $message . "\n");
        } else {
            echo $message . "\n";
        }
    }
    
    /**
     * Exception factory
     * @param string $message
     * @throws Exception
     */
    protected function throwException($message)
    {
        $message = $message ? $message : 'Something unexpected occured. See GettextExtractor log for details';
        $this->log($message);
        //echo $message;
        throw new Exception($message);
    }
    
    /**
     * Scans given files or directories and extracts gettext keys from the content
     * @param string|array $resource
     * @return GettetExtractor
     */
    public function scan($resource)
    {
        $this->inputFiles = array();
        if (!is_array($resource)) $resource = array($resource);
        foreach ($resource as $item) {
            $this->log("Scanning '$item'");
            $this->_scan($item);
        }
        $this->_extract($this->inputFiles);
        return $this;
    }
    
    /**
     * Scans given files or directories (recursively) and stores extracted gettext keys in a buffer
	 *
     * @param string $resource File or directory
     */
    protected function _scan($resource)
    {
        if (is_file($resource)) {
            $this->inputFiles[] = $resource;
        } elseif (is_dir($resource)) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($resource, RecursiveDirectoryIterator::SKIP_DOTS)
			);
			foreach ($iterator as $file) {
				$this->inputFiles[] = $file;
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
    protected function _extract($inputFiles)
    {
        $inputFiles = array_unique($inputFiles);
        foreach ($inputFiles as $inputFile)
        {
            if (!file_exists($inputFile)) {
                $this->throwException('ERROR: Invalid input file specified: ' . $inputFile);
            }
            if (!is_readable($inputFile)) {
                $this->throwException('ERROR: Input file is not readable: ' . $inputFile);
            }
            
            $this->log('Extracting data from file ' . $inputFile);

			$fileExtension = pathinfo($inputFile, PATHINFO_EXTENSION);
            foreach ($this->filters as $extension => $filters)
            {
                // Check file extension
                if ($fileExtension !== $extension) continue;
                
                $this->log('Processing file ' . $inputFile);
                
                foreach ($filters as $filterName)
                {
                    $filter = $this->getFilter($filterName);
                    $filterData = $filter->extract($inputFile);
                    $this->log('  Filter ' . $filterName . ' applied');
                    $this->data = array_merge_recursive($this->data, $filterData);
                }
            }
        }
        return $this->data;
    }
    
    /**
     * Gets an instance of a GettextExtractor filter
     * @param string $filter
     * @return iFilter
     */
    public function getFilter($filter)
    {
        $filter = $filter . 'Filter';
        
        if (isset($this->filterStore[$filter])) return $this->filterStore[$filter];
        
        if (!class_exists($filter)) {
            $filter_file = dirname(__FILE__) . '/Filters/' . $filter . ".php";
            if (!file_exists($filter_file)) {
                $this->throwException('ERROR: Filter file ' . $filter_file . ' not found');
            }
            require_once $filter_file;
            if (!class_exists($filter)) {
                $this->throwException('ERROR: Class ' . $filter . ' not found');
            }
        }
        
        $this->filterStore[$filter] = new $filter;
        $this->log('Filter ' . $filter . ' loaded');
        return $this->filterStore[$filter];
    }
    
    /**
     * Assigns a filter to an extension
     * @param string $extension
     * @param string $filter
     * @return GettextExtractor
     */
    public function setFilter($extension, $filter)
    {
        if (isset($this->filters[$extension]) && in_array($filter, $this->filters[$extension])) return $this;
        $this->filters[$extension][] = $filter;
        return $this;
    }
    
    /**
     * Removes all filter settings in case we want to define a brand new one
     * @return GettextExtractor
     */
    public function removeAllFilters()
    {
        $this->filters = array();
        return $this;
    }
    
    /**
     * Adds a comment to the top of the output file
     * @param string $value
     * @return GettextExtractor
     */
    public function addComment($value) {
        $this->comments[] = $value;
        return $this;
    }
    
    /**
     * Gets a value of a meta key
     * @param string $key
     */
    public function getMeta($key)
    {
        return isset($this->meta[$key]) ? $this->meta[$key] : NULL;
    }
    
    /**
     * Sets a value of a meta key
     * @param string $key
     * @param string $value
     * @return GettextExtractor
     */
    public function setMeta($key, $value)
    {
        $this->meta[$key] = $value;
        return $this;
    }
    
    /**
     * Saves extracted data into gettext file
     * @param string $outputFile
     * @param array $data
     * @return GettextExtractor
     */
    public function save($outputFile, $data = null)
    {
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
     * @param array $data
     * @return string
     */
    protected function formatData($data)
    {
        $output = array();
        foreach ($this->comments as $comment) {
            $output[] = '# ' . $comment;
        }
		$output[] = '#, fuzzy';
        $output[] = 'msgid ""';
        $output[] = 'msgstr ""';
		$output[] = '"POT-Creation-Date: '.date('c').'\n"';
        foreach ($this->meta as $key => $value) {
            $output[] = '"' . $key . ': ' . $value . '\n"';
        }
        $output[] = '';
        
        ksort($data);
        
        foreach ($data as $key => $files)
        {
            ksort($files);
            foreach ($files as $file)
                $output[] = '# ' . $file;
            $output[] = 'msgid "' . $this->addSlashes($key) . '"';
            /*if (preg_match($this->pluralMatchRegexp, $key, $matches)) { // TODO: really export plurals? deprecated for now
                $output[] = 'msgid_plural "' . addslashes($key) . '"';
                //$output[] = 'msgid_plural ""';
                $output[] = 'msgstr[0] "' . addslashes($key) . '"';
                $output[] = 'msgstr[1] "' . addslashes($key) . '"';
            } else {
                $output[] = 'msgstr "' . addslashes($key) . '"'; 
            }*/
            
            switch ($this->outputMode) {
                case self::OUTPUT_POT:
                    $output[] = 'msgstr ""';
                    break;
                case self::OUTPUT_PO:
                    // fallthrough
                default:
                    $output[] = 'msgstr "' . $this->addSlashes($key) . '"';
            }
            
            $output[] = '';
        }
        
        return join("\n", $output);
    }
    
    /**
     * Escape a sring not to break the gettext syntax
     * @param string $string
     * @return string
     */
    public function addSlashes($string)
    {
        return addcslashes($string, self::ESCAPE_CHARS);
    }

    /**
     * Sets output mode.
     * On OUTPUT_PO, english msgstrs will be generated,
     *     on OUTPUT_POT, msgstrs will be empty
     *
     * @param string $outputMode
     * @return string
     */
    public function setOutputMode($outputMode)
    {
        $this->outputMode = $outputMode;
        return $this;
    }


    
}