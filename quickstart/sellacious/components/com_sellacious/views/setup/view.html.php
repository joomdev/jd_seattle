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
 * View to edit basic configuration after installation
 *
 * @since   1.5.0
 */
class SellaciousViewSetup extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $action_prefix = 'setup';

	/**
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $view_item = 'setup';

	/**
	 * @var  string
	 *
	 * @since   1.5.0
	 */
	protected $view_list = null;

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function prepareDisplay()
	{
		if (!$this->helper->access->check('config.edit'))
		{
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return;
		}

		$this->app->input->set('hidemainmenu', 1);

		$this->setPageTitle();
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	protected function setPageTitle()
	{
		$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
		$base_uri   = rtrim(JUri::base(true), '/') . '/';
		$url        = str_replace($base_uri, '', $active_uri);

		/** @var stdClass $item */
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

		$title = empty($title) ? JText::_('COM_SELLACIOUS_TITLE_' . strtoupper($this->getName())) : $title;

		JToolBarHelper::title($title, 'cogs');
	}
}
