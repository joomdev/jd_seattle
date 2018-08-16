<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

class SpsimpleportfolioViewTags extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;
	public $filterForm;
	public $activeFilters;
	protected $sidebar;

	function display($tpl = null) {

		// Get application
		$app = JFactory::getApplication();
		$context = "com_spsimpleportfolio.items";

		// Get data from the model
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filter_order = $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'id', 'cmd');
		$this->filter_order_Dir = $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', 'desc', 'cmd');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		$this->canDo = SpsimpleportfolioHelper::getActions();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		// Set the submenu
		SpsimpleportfolioHelper::addSubmenu('tags');
		$this->addToolBar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);

	}

	protected function addToolBar() {
		JToolBarHelper::title(JText::_('COM_SPSIMPLEPORTFOLIO_MANAGER') .  JText::_('COM_SPSIMPLEPORTFOLIO_TAGS'), 'pictures');

		if ($this->canDo->get('core.create')) {
			JToolBarHelper::addNew('tag.add', 'JTOOLBAR_NEW');
		} if ($this->canDo->get('core.edit')) {
			JToolBarHelper::editList('tag.edit', 'JTOOLBAR_EDIT');
		}

		if($this->canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'tags.delete', 'JTOOLBAR_DELETE');
		}

		if ($this->canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_spsimpleportfolio');
		}
	}
}
