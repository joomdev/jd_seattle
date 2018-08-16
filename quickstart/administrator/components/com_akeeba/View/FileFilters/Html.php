<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\FileFilters;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\Model\FileFilters;
use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JHtml;
use JText;
use JUri;

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
	 * @return  void
	 */
	public function onBeforeMain()
	{
		$this->addJavascriptFile('media://com_akeeba/js/FileFilters.min.js');

		/** @var FileFilters $model */
		$model = $this->getModel();

		// Add custom submenus
		$task    = $model->getState('browse_task', 'normal');
		$toolbar = $this->container->toolbar;

		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_NORMALVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=FileFilters&task=normal',
			($task == 'normal')
		);
		$toolbar->appendLink(
			JText::_('COM_AKEEBA_FILEFILTERS_LABEL_TABULARVIEW'),
			JUri::base() . 'index.php?option=com_akeeba&view=FileFilters&task=tabular',
			($task == 'tabular')
		);

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
		$attribs           = 'onchange="akeeba.Fsfilters.activeRootChanged();"';
		$this->root_select =
			JHtml::_('select.genericlist', $options, 'root', $attribs, 'value', 'text', $site_root, 'active_root');
		$this->roots       = $roots;

		switch ($task)
		{
			case 'normal':
			default:
				$this->setLayout('default');

				// Get a JSON representation of the directory data
				$model      = $this->getModel();
				$json       = json_encode($model->make_listing($site_root, array(), ''));
				$this->json = $json;

				break;

			case 'tabular':
				$this->setLayout('tabular');

				// Get a JSON representation of the tabular filter data
				$model      = $this->getModel();
				$json       = json_encode($model->get_filters($site_root));
				$this->json = $json;

				break;
		}

		// Push translations
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIROOT');
		JText::script('COM_AKEEBA_FILEFILTERS_LABEL_UIERRORFILTER');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_FILES_ALL');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_APPLYTOALLDIRS');
		JText::script('COM_AKEEBA_FILEFILTERS_TYPE_APPLYTOALLFILES');

		$this->getProfileIdAndName();
	}

}
