<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Menus View.
 *
 * @since  1.5.0
 */
class MenusViewMenus extends SellaciousViewList
{
	/**
	 * Display the view
	 *
	 * @param   string $tpl
	 *
	 * @return  mixed
	 *
	 * @since   1.5.0
	 */
	public function display($tpl = null)
	{
		// Temporarily redirect to a fixed menu (sellacious-menu)
		JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_menus&view=items', false));

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_MENUS_VIEW_MENUS_TITLE'), 'list menumgr');

		if ($this->helper->access->check('core.create', null, 'com_menus'))
		{
			JToolbarHelper::addNew('menu.add');
		}

		if ($this->helper->access->check('core.edit', null, 'com_menus'))
		{
			JToolbarHelper::editList('menu.edit');
		}

		if ($this->helper->access->check('core.delete', null, 'com_menus'))
		{
			JToolbarHelper::deleteList('', 'menus.delete', 'JTOOLBAR_DELETE');
		}

		JToolbarHelper::custom('menus.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
	}
}
