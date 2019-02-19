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

/**
 * View to edit
 */
class SellaciousViewConfig extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'config';

	/** @var  string */
	protected $view_item = 'config';

	/** @var  string */
	protected $view_list = null;

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		$this->setLayout('edit');

		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check('config.edit'))
		{
			JToolBarHelper::apply('config.save', 'JTOOLBAR_APPLY');
		}
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws Exception
	 */
	protected function setPageTitle()
	{
		$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
		$base_uri   = rtrim(JUri::base(true), '/') . '/';
		$url        = str_replace($base_uri, '', $active_uri);

		$menu = $this->app->getMenu();
		$item = $menu->getItems('link', $url, true);

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
				$title = $item->title;
			}
		}

		$icon  = empty($icon) ? 'list-alt' : $icon;
		$title = empty($title) ? JText::_('COM_SELLACIOUS_TITLE_' . strtoupper($this->getName())) : $title;

		JToolBarHelper::title($title, $icon);
	}
}
