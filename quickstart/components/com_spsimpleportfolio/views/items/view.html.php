<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

class SpsimpleportfolioViewItems extends JViewLegacy {

	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {
		// Assign data to the view
		$model = $this->getModel();
		$this->items = $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->tagList = $model->getTagList($this->items);

		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		$menus = JFactory::getApplication()->getMenu();
		$menu = $menus->getActive();

		if($menu) {
			$this->params->merge($menu->params);
		}

		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JLog::add(implode('<br />', $errors), JLog::WARNING, 'jerror');
			return false;
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_SPSIMPLEPORTFOLIO_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->get('sitename');
		} elseif ($app->get('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
