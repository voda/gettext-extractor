<?php
/**
 * @copyright Copyright (c) 2009 Karel Klíma
 * @copyright Copyright (c) 2010 Ondřej Vodáček
 * @license New BSD License
 */

namespace Vodacek\GettextExtractor\Filters;

interface IFilter {

	/**
	 * Extracts gettext phrases from a file
	 *
	 * @param string $file
	 * @return array List<Map<KEY, string>>
	 */
	public function extract($file);
}
