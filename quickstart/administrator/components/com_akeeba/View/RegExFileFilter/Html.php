<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\RegExFileFilter;

// Protect from unauthorized access
use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Backup\Admin\Model\RegExFileFilters;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use JHtml;
use JText;

defined('_JEXEC') or die();

class Html extends \FOF30\View\DataView\Html
{
	use ProfileIdAndName;

	/**
	 * SELECT element for choosing a database root
	 *
	 * @var  string
	 */
	public $root_select = '';

	/**
	 * List of database roots
	 *
	 * @var  array
	 */
	public $roots = [];

	/**
	 * The view's interface data encoded in JSON format
	 *
	 * @var  string
	 */
	public $json = '';

	/**
	 * Main page
	 */
	public function onBeforeMain()
	{
		// Load Javascript files
		$this->addJavascriptFile('media://com_akeeba/js/FileFilters.min.js');
		$this->addJavascriptFile('media://com_akeeba/js/RegExFileFilter.min.js');

		/** @var RegExFileFilters $model */
		$model = $this->getModel();

		// Get a JSON representation of the available roots
		$filters   = Factory::getFilters();
		$root_info = $filters->getInclusions('dir');
		$roots     = array();
		$options   = array();

		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $dir_definition)
			{
				if (is_null($dir_definition[1]))
				{
					// Site root definition has a null element 1. It is always pushed on top of the stack.
					array_unshift($roots, $dir_definition[0]);
				}
				else
				{
					$roots[] = $dir_definition[0];
				}

				$options[] = JHtml::_('select.option', $dir_definition[0], $dir_definition[0]);
			}
		}
		$site_root         = $roots[0];
		$attribs           = 'onchange="akeeba.Regexfsfilters.activeRootChanged();"';
		$this->root_select = JHtml::_('select.genericlist', $options, 'root', $attribs, 'value', 'text', $site_root, 'active_root');
		$this->roots       = $roots;

		// Get a JSON representation of the directory data
		$json       = json_encode($model->get_regex_filters($site_root));
		$this->json = $json;

		$this->getProfileIdAndName();

		// Push translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES');

	}
}
