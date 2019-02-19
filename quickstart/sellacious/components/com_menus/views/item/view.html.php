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
class MenusViewItem extends SellaciousViewForm
{
	/**
	 * Prepare the display
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function prepareDisplay()
	{
		/*
		 * Check if we're allowed to edit this item
		 * No need to check for create, because then the module-type select is empty
		 */
		if (!empty($this->item->id) && !$this->helper->access->check('core.edit', null, 'com_menus'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	protected function addToolbar()
	{
		$app = JFactory::getApplication();
		$app->input->set('hidemainmenu', true);

		$user       = JFactory::getUser();
		$isNew      = $this->item->id == 0;
		$checkedOut = $this->item->checked_out != 0 && $this->item->checked_out != $user->get('id');

		JToolbarHelper::title(JText::_($isNew ? 'COM_MENUS_VIEW_NEW_ITEM_TITLE' : 'COM_MENUS_VIEW_EDIT_ITEM_TITLE'), 'list menu-add');

		// If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
		$check = $this->helper->access->check('core.create', null, 'com_menus');

		if ($isNew && $check)
		{
			if ($this->helper->access->check('core.edit', null, 'com_menus'))
			{
				JToolbarHelper::apply('item.apply');
			}

			JToolbarHelper::save('item.save');
		}

		// If not checked out, can save the item.
		if (!$isNew && !$checkedOut && $this->helper->access->check('core.edit', null, 'com_menus'))
		{
			JToolbarHelper::apply('item.apply');
			JToolbarHelper::save('item.save');
		}

		// If the user can create new items, allow them to see Save & New
		if ($check)
		{
			JToolbarHelper::save2new('item.save2new');
		}

		// If an existing item, can save to a copy only if we have create rights.
		if (!$isNew && $check)
		{
			JToolbarHelper::save2copy('item.save2copy');
		}

		JToolbarHelper::cancel('item.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
