<?php
/**
 * @package   AkeebaBackup
 * @copyright Copyright (c)2006-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\View\Profiles;

// Protect from unauthorized access
defined('_JEXEC') or die();

use Akeeba\Backup\Admin\View\ViewTraits\ProfileIdAndName;
use Akeeba\Engine\Platform;
use FOF30\View\DataView\Html as BaseView;
use JHtml;
use JText;

/**
 * View controller for the profiles management page
 */
class Html extends BaseView
{
	use ProfileIdAndName;
	
	/**
	 * Sorting order fields
	 *
	 * @var  array
	 */
	public $sortFields;

	/**
	 * The default layout, shows a list of profiles
	 */
	function onBeforeBrowse()
	{
		$this->getProfileIdAndName();

		// Get Sort By fields
		$this->sortFields = array(
			'id'          => JText::_('JGRID_HEADING_ID'),
			'description' => JText::_('COM_AKEEBA_PROFILES_COLLABEL_DESCRIPTION'),
		);

		parent::onBeforeBrowse();

		$js = <<< JS
	Joomla.orderTable = function ()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;

		if (order != '{$this->lists->order}')
		{
			dirn = 'asc';
		}
		else
		{
			dirn = direction.options[direction.selectedIndex].value;
		}

		Joomla.tableOrdering(order, dirn);
	}

JS;
		$this->addJavascriptInline($js);

		JHtml::_('behavior.multiselect');
		JHtml::_('dropdown.init');
	}

	/**
	 * The edit layout, editing a profile's name
	 */
	protected function onBeforeEdit()
	{
		parent::onBeforeEdit();

		// Include tooltip support
		JHtml::_('behavior.tooltip');
	}


}
