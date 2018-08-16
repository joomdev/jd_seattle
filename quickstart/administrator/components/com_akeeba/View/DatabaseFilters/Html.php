<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\DatabaseFilters;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\DatabaseFilters;
use Akeeba\Backup\Admin\Model\Profiles;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JHtml;
use JText;
use JUri;

/**
 * View for database table exclusion
 */
class Html extends BaseView
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
		$this->addJavascriptFile('media://com_akeeba/js/DatabaseFilters.min.js');

		/** @var DatabaseFilters $model */
		$model = $this->getModel();

		// Add custom submenus
		$task    = $model->getState('browse_task', 'normal');
		$toolbar = $this->container->toolbar;

		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_NORMALVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=DatabaseFilters&task=normal',
			($task == 'normal')
		);
		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TABULARVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=DatabaseFilters&task=tabular',
			($task == 'tabular')
		);

		// Get a JSON representation of the available roots
		$root_info = $model->get_roots();
		$roots     = array();
		$options   = array();

		if (!empty($root_info))
		{
			// Loop all dir definitions
			foreach ($root_info as $def)
			{
				$roots[]   = $def->value;
				$options[] = JHtml::_('select.option', $def->value, $def->text);
			}
		}

		$site_root         = '[SITEDB]';
		$attributes        = 'onchange="akeeba.Dbfilters.activeRootChanged ();"';
		$this->root_select =
			JHtml::_('select.genericlist', $options, 'root', $attributes, 'value', 'text', $site_root, 'active_root');
		$this->roots       = $roots;

		switch ($task)
		{
			case 'normal':
			default:
				$this->setLayout('default');

				// Get a JSON representation of the database data
				$json       = json_encode($model->make_listing($site_root));
				$this->json = $json;

				break;

			case 'tabular':
				$this->setLayout('tabular');

				// Get a JSON representation of the tabular filter data
				$json       = json_encode($model->get_filters($site_root));
				$this->json = $json;

				break;
		}

		// Translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_DBFILTER_TYPE_TABLES');
		JText::script('COM_AKEEBA_DBFILTER_TYPE_TABLEDATA');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_MISC');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_TABLE');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_VIEW');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_PROCEDURE');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_FUNCTION');
		JText::script('COM_AKEEBA_DBFILTER_TABLE_TRIGGER');

		$this->getProfileIdAndName();
	}
}
