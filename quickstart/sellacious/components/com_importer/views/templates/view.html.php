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

/**
 * View class for a list of templates.
 *
 * @since   1.5.2
 */
class ImporterViewTemplates extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $action_prefix = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_item = 'template';

	/**
	 * @var  string
	 *
	 * @since   1.5.2
	 */
	protected $view_list = 'templates';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$this->setPageTitle();

		if ($this->helper->access->check('template.create', null, 'com_importer'))
		{
			JToolBarHelper::addNew($this->view_item . '.add', 'JTOOLBAR_NEW');
		}

		if (!count($this->items))
		{
			return;
		}

		if ($this->helper->access->check('template.edit', null, 'com_importer') ||
			$this->helper->access->check('template.edit.own', null, 'com_importer'))
		{
			JToolBarHelper::editList($this->view_item . '.edit', 'JTOOLBAR_EDIT');
		}

		if ($this->helper->access->check('template.edit.state', null, 'com_importer'))
		{
			$published = $state->get('filter.state');

			if (!is_numeric($published) || $published != '1')
			{
				JToolBarHelper::custom($this->view_list . '.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
			}

			if (!is_numeric($published) || $published != '0')
			{
				JToolBarHelper::custom($this->view_list . '.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}

			if (!is_numeric($published) || $published != '-2')
			{
				JToolBarHelper::trash($this->view_list . '.trash', 'JTOOLBAR_TRASH');
			}
			elseif ($published == '-2' &&
				($this->helper->access->check('template.delete', null, 'com_importer') ||
					$this->helper->access->check('template.delete.own', null, 'com_importer')))
			{
				JToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
			}
		}
	}
}
