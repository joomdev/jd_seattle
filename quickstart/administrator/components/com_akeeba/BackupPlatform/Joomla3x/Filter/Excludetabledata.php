<?php
/**
 * Akeeba Engine
 * The modular PHP5 site backup engine
 *
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or, at your option, any later version
 * @package   akeebaengine
 *
 */

namespace Akeeba\Engine\Filter;

// Protection against direct access
defined('AKEEBAENGINE') or die();

use Akeeba\Engine\Factory;

/**
 * Subdirectories exclusion filter. Excludes temporary, cache and backup output
 * directories' contents from being backed up.
 */
class Excludetabledata extends Base
{
	public function __construct()
	{
		$this->object      = 'dbobject';
		$this->subtype     = 'content';
		$this->method      = 'direct';
		$this->filter_name = 'Excludetabledata';

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data['[SITEDB]'] = array(
			'#__session',        // Sessions table
			'#__guardxt_runs'    // Guard XT's run log (bloated to the bone)
		);

		parent::__construct();
	}

}
