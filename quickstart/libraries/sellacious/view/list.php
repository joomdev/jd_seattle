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

use Joomla\Registry\Registry;
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;
use Sellacious\Toolbar\ToolbarHelper;

/**
 * Base class for sellacious views.
 * Class holding methods for displaying presentation data in list layout.
 *
 * @since   1.0.0
 */
abstract class SellaciousViewList extends SellaciousView
{
	/**
	 * @var  JForm
	 *
	 * @since   1.0.0
	 */
	public $filterForm;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	public $activeFilters;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	public $sidebar;

	/**
	 * @var  stdClass[]
	 *
	 * @since   1.0.0
	 */
	protected $items;

	/**
	 * @var  JPagination
	 *
	 * @since   1.0.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since   1.0.0
	 */
	protected $state;

	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected $ordering;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $action_prefix;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_item;

	/**
	 * @var  string
	 *
	 * @since   1.0.0
	 */
	protected $view_list;

	/**
	 * @var  bool
	 *
	 * @since   1.0.0
	 */
	protected $is_nested = false;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		if (!isset($this->state))
		{
			$this->state = $this->get('State');
		}

		if (!isset($this->items))
		{
			$this->items = $this->get('Items');
		}

		if (!isset($this->pagination))
		{
			$this->pagination = $this->get('Pagination');
		}

		if (!isset($this->filterForm))
		{
			$this->filterForm = $this->get('FilterForm');
		}

		if (!isset($this->activeFilters))
		{
			$this->activeFilters = $this->get('ActiveFilters');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		if ($this->is_nested)
		{
			foreach ($this->items as &$item)
			{
				$this->ordering[$item->parent_id][] = $item->id;
			}
		}

		$this->prepareDisplay();

		return parent::display($tpl);
	}

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareDisplay()
	{
		$this->addToolbar();

		$this->sidebar = JHtmlSidebar::render();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$this->setPageTitle();

		$toolbar = Toolbar::getInstance();

		$editable = file_exists(JPATH_COMPONENT . '/views/' . $this->view_item);

		if ($editable && $this->helper->access->check($this->action_prefix . '.create'))
		{
			$toolbar->appendButton(new StandardButton('new', 'JTOOLBAR_NEW', $this->view_item . '.add', false));
		}

		$gState = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
		$toolbar->appendGroup($gState);

		if (count($this->items))
		{
			if ($this->helper->access->check($this->action_prefix . '.edit.state'))
			{
				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '1')
				{
					$gState->appendButton(new StandardButton('publish', 'JTOOLBAR_PUBLISH', $this->view_list . '.publish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '0')
				{
					$gState->appendButton(new StandardButton('unpublish', 'JTOOLBAR_UNPUBLISH', $this->view_list . '.unpublish', true));
				}

				if (!is_numeric($state->get('filter.state')) || $state->get('filter.state') != '-2')
				{
					$gState->appendButton(new StandardButton('trash', 'JTOOLBAR_TRASH', $this->view_list . '.trash', true));
				}
				// If 'edit.state' is granted, then show 'delete' only if filtered on 'trashed' items
				elseif ($state->get('filter.state') == '-2' && $this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
				{
					ToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
				}
			}
			// We can allow direct 'delete' implicitly for his (seller) own items if so permitted.
			elseif ($this->helper->access->checkAny(array('.delete', '.delete.own'), $this->action_prefix))
			{
				ToolBarHelper::trash($this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}

		if ($this->is_nested && $this->helper->access->check('core.admin'))
		{
			ToolBarHelper::custom($this->view_list . '.rebuild', 'refresh.png', 'refresh_f2.png', 'JTOOLBAR_REBUILD', false);
		}
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @since   1.0.0
	 */
	protected function setPageTitle()
	{
		$lang = JFactory::getLanguage();
		$lang->load('mod_smartymenu', JPATH_SITE . '/' . JPATH_SELLACIOUS_DIR . '/modules/mod_smartymenu');
		$lang->load('mod_smartymenu', JPATH_SITE . '/' . JPATH_SELLACIOUS_DIR);

		$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
		$base_uri   = rtrim(JUri::base(true), '/') . '/';
		$url        = str_replace($base_uri, '', $active_uri);

		/** @var stdClass $item */
		$menu = $this->app->getMenu();
		$item = $menu->getItems(array('link', 'access'), array($url, 0), true);

		if (isset($item->params))
		{
			if (is_string($item->params))
			{
				$item->params = new Registry($item->params);
			}

			$icon  = $item->params->get('menu-anchor_css');
			$title = $item->params->get('menu-page-title');

			if (empty($title))
			{
				$title = JText::_($item->title);
			}
		}

		$icon  = empty($icon) ? 'list-alt' : $icon;
		$title = empty($title) ? JText::_(strtoupper($this->getOption()) . '_TITLE_' . strtoupper($this->getName())) : $title;

		ToolBarHelper::title($title, $icon);
	}
}
