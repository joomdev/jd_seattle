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
 * The HTML Menus Menu Item View.
 *
 * @since   1.5.0
 */
class MenusViewMenu extends SellaciousViewForm
{
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function addToolbar()
	{
		$input = JFactory::getApplication()->input;
		$input->set('hidemainmenu', true);
		$isNew = ($this->item->id == 0);

		JToolbarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_MENU_TITLE' : 'COM_MENUS_VIEW_EDIT_MENU_TITLE'), 'list menu');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		if ($isNew && $this->helper->access->check('core.create', null, 'com_menus'))
		{
			if ($this->helper->access->check('core.edit', null, 'com_menus'))
			{
				JToolbarHelper::apply('menu.apply');
			}

			JToolbarHelper::save('menu.save');
		}

		// If user can edit, can save the item.
		if (!$isNew && $this->helper->access->check('core.edit', null, 'com_menus'))
		{
			JToolbarHelper::apply('menu.apply');
			JToolbarHelper::save('menu.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($this->helper->access->check('core.create', null, 'com_menus'))
		{
			JToolbarHelper::save2new('menu.save2new');
		}

		JToolbarHelper::cancel('menu.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
