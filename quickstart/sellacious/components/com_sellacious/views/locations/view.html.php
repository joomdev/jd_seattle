<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * View class for a list of Sellacious.
 */
class SellaciousViewLocations extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'location';

	/** @var  string */
	protected $view_item = 'location';

	/** @var  string */
	protected $view_list = 'locations';

	/**
	 * Display the view
	 *
	 * @param   string $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		if ($this->getLayout() == 'import')
		{
			$this->state = $this->get('State');
			$this->items = $this->app->getUserState('com_sellacious.locations.import.stats', array());

			$this->pagination    = false;
			$this->filterForm    = false;
			$this->activeFilters = false;
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		if ($this->getLayout() != 'import')
		{
			parent::addToolbar();
		}

		if ($this->helper->access->check('config.edit'))
		{
			if ($this->getLayout() == 'import')
			{
				JToolBarHelper::cancel($this->view_list . '.cancelImport');
				JToolBarHelper::custom($this->view_list . '.import', 'upload', 'upload', 'COM_SELLACIOUS_LOCATIONS_IMPORT_FINISH_TOOLBAR_LABEL');
			}
			else
			{
				// JToolBarHelper::custom($this->view_list . '.export', 'download', 'download', 'COM_SELLACIOUS_LOCATIONS_EXPORT_TOOLBAR_LABEL', false);
				JToolBarHelper::custom('', 'upload', 'upload', 'COM_SELLACIOUS_LOCATIONS_IMPORT_TOOLBAR_LABEL', false);
				JToolBarHelper::custom($this->view_list . '.buildCache', 'refresh', 'refresh', 'COM_SELLACIOUS_LOCATIONS_CACHE_REBUILD_TOOLBAR_LABEL', false);
				JToolBarHelper::custom($this->view_list . '.clearCache', 'delete', 'delete', 'COM_SELLACIOUS_LOCATIONS_CACHE_CLEAR_TOOLBAR_LABEL', false);
			}
		}
	}
}
