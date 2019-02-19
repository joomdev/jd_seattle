<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * Languages view class for the list of available languages.
 *
 * @since   1.6.0
 */
class LanguagesViewLanguages extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix = 'language';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item = 'language';

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list = 'languages';

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  1.6.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$this->setPageTitle();

		$toolbar  = Toolbar::getInstance();
		$editable = file_exists(JPATH_COMPONENT . '/views/' . $this->view_item);

		if ($editable && $this->helper->access->check($this->action_prefix . '.create'))
		{
			$toolbar->appendButton(new StandardButton('new', 'JTOOLBAR_NEW', $this->view_item . '.add', false));
		}

		if (count($this->items))
		{
			$gState = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
			$toolbar->appendGroup($gState);

			if ($this->helper->access->check($this->action_prefix . '.edit.state'))
			{
				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '1')
				{
					// $gState->appendButton(new StandardButton('publish', 'JTOOLBAR_PUBLISH', $this->view_list . '.publish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '0')
				{
					// $gState->appendButton(new StandardButton('unpublish', 'JTOOLBAR_UNPUBLISH', $this->view_list . '.unpublish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '-2')
				{
					// $gState->appendButton(new StandardButton('trash', 'JTOOLBAR_TRASH', $this->view_list . '.trash', true));
				}
				// If 'edit.state' is granted, then show 'delete' only if filtered on 'trashed' items
				elseif ($state->get('filter.state') == '-2' && $this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
				{
					// ToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
				}
			}
			// We can allow direct 'delete' implicitly for his (seller) own items if so permitted.
			elseif ($this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
			{
				// ToolBarHelper::trash($this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}
	}


}
