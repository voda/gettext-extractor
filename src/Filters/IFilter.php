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
 * @copyright Copyright (c) 2009 Karel Klíma
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

/**
 * Gettext parser filters interface
 * @author Karel Klíma
 * @author Ondřej Vodáček
 */
interface IFilter {

	/**
	 * Extracts gettext phrases from a file
	 *
	 * @param string $file
	 * @return array List<Map<KEY, string>>
	 */
	public function extract($file);
}
