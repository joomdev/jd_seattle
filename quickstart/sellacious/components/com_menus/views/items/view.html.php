<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Items View.
 *
 * @since  1.5.0
 */
class MenusViewItems extends SellaciousViewList
{
	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @since   1.5.0
	 */
	public function prepareDisplay()
	{
		$this->ordering = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;

			// Item type text
			switch ($item->type)
			{
				case 'url':
					$value = JText::_('COM_MENUS_TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = JText::_('COM_MENUS_TYPE_ALIAS');
					break;

				case 'separator':
					$value = JText::_('COM_MENUS_TYPE_SEPARATOR');
					break;

				case 'heading':
					$value = JText::_('COM_MENUS_TYPE_HEADING');
					break;

				case 'container':
					$value = JText::_('COM_MENUS_TYPE_CONTAINER');
					break;

				case 'component':
				default:
					$lang = JFactory::getLanguage();
					$lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR, null, false, true)
						|| $lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->componentname, null, false, true);

					if (!empty($item->componentname))
					{
						$value = implode(' » ', $this->getLinkRoute($item));
					}
					elseif (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
					{
						$value = JText::sprintf('COM_MENUS_TYPE_UNEXISTING', $result[1]);
					}
					else
					{
						$value = JText::_('COM_MENUS_TYPE_UNKNOWN');
					}

					break;
			}

			$item->item_type = $value;
			$item->protected = $item->menutype == 'main' || $item->client_id != 2;
		}

		// Allow a system plugin to insert dynamic menu types to the list shown in menus:
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onBeforeRenderMenuItems', array($this));

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
		$menutypeId = (int) $this->state->get('menutypeid');

		$canDo = JHelperContent::getActions('com_menus', 'menu', (int) $menutypeId);
		$user  = JFactory::getUser();

		$this->setPageTitle();

		$toolbar = Toolbar::getInstance();
		$gState  = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
		$toolbar->appendGroup($gState);

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('item.add');
		}

		$protected = $this->state->get('filter.menutype') == 'main' && $this->state->get('filter.client_id') != '2';

		if ($canDo->get('core.edit') && !$protected)
		{
			JToolbarHelper::editList('item.edit');
		}

		if ($canDo->get('core.edit.state') && !$protected)
		{
			$gState->appendButton(new StandardButton('publish', 'JTOOLBAR_PUBLISH', 'items.publish', true));
			$gState->appendButton(new StandardButton('unpublish', 'JTOOLBAR_UNPUBLISH', 'items.unpublish', true));
		}

		if (JFactory::getUser()->authorise('core.admin') && !$protected)
		{
			$gState->appendButton(new StandardButton('checkin', 'JTOOLBAR_CHECKIN', 'items.checkin', true));
		}

		if ($canDo->get('core.edit.state') && $this->state->get('filter.client_id') == 0)
		{
			JToolbarHelper::makeDefault('items.setDefault', 'COM_MENUS_TOOLBAR_SET_HOME');
		}

		if (JFactory::getUser()->authorise('core.admin'))
		{
			JToolbarHelper::custom('items.rebuild', 'refresh.png', 'refresh_f2.png', 'JToolbar_Rebuild', false);
		}

		// Add a batch button
		if (!$protected && $user->authorise('core.create', 'com_menus')
			&& $user->authorise('core.edit', 'com_menus')
			&& $user->authorise('core.edit.state', 'com_menus'))
		{
			/*
			// Instantiate a new JLayoutFile instance and render the batch button
			$title  = JText::_('JTOOLBAR_BATCH');
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$bar->appendButton('Custom', $layout->render(array('title' => $title)), 'batch');
			*/
		}

		if (!$protected && $this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'items.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif (!$protected && $canDo->get('core.edit.state'))
		{
			$gState->appendButton(new StandardButton('trash', 'JTOOLBAR_TRASH', 'items.trash', true));
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.5.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.lft'       => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.title'     => JText::_('JGLOBAL_TITLE'),
			'a.id'        => JText::_('JGRID_HEADING_ID')
		);
	}

	/**
	 * Method to get the component/view/layout route for the given component type menu item
	 *
	 * @param   stdClass  $item  The menu item
	 *
	 * @return  array
	 *
	 * @since   1.5.0
	 */
	protected function getLinkRoute($item)
	{
		$titleParts   = array();
		$lang         = JFactory::getLanguage();
		$titleParts[] = JText::_($item->componentname);

		parse_str($item->link, $vars);

		if (isset($vars['view']))
		{
			// Attempt to load the view xml file.
			// Attempt to load the view xml file.
			$baseDir = JPATH_SITE . '/' . JPATH_SELLACIOUS_DIR;

			$file    = $baseDir . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/metadata.xml';

			if (!is_file($file))
			{
				$file = $baseDir . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/metadata.xml';
			}

			if (is_file($file) && $xml = simplexml_load_file($file))
			{
				// Look for the first view node off of the root node.
				if (($view = $xml->xpath('view[1]')) && !empty($view[0]['title']))
				{
					// Add view title if present.
					$titleParts[] = JText::_(trim((string) $view[0]['title']));
				}
			}

			$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

			// Attempt to load the layout xml file.
			// If Alternative Menu Item, get template folder for layout file
			if (strpos($vars['layout'], ':') > 0)
			{
				// Use template folder for layout file
				$temp = explode(':', $vars['layout']);
				$file = $baseDir . '/templates/' . $temp[0] . '/html/' . $item->componentname . '/' . $vars['view'] . '/' . $temp[1] . '.xml';

				// Load template language file
				$lang->load('tpl_' . $temp[0] . '.sys', $baseDir, null, false, true)
				|| $lang->load('tpl_' . $temp[0] . '.sys', $baseDir . '/templates/' . $temp[0], null, false, true);
			}
			else
			{
				// Get XML file from component folder for standard layouts
				$file = $baseDir . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';

				if (!file_exists($file))
				{
					$file = $baseDir . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';
				}
			}

			if (is_file($file) && $xml = simplexml_load_file($file))
			{
				// Look for the first view node off of the root node.
				if ($layout = $xml->xpath('layout[1]'))
				{
					if (!empty($layout[0]['title']))
					{
						$titleParts[] = JText::_(trim((string) $layout[0]['title']));
					}
				}

				if (!empty($layout[0]->message[0]))
				{
					$item->item_type_desc = JText::_(trim((string) $layout[0]->message[0]));
				}
			}

			unset($xml);

			// Special case if neither a view nor layout title is found
			if (count($titleParts) == 1)
			{
				$titleParts[] = $vars['view'];
			}
		}

		return $titleParts;
	}
}
