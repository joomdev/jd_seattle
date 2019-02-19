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
 * View class for a list of imports.
 *
 * @since   1.6.1
 */
class ImporterViewImports extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $action_prefix = 'import';

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $view_item = 'import';

	/**
	 * @var  string
	 *
	 * @since   1.6.1
	 */
	protected $view_list = 'imports';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if (count($this->items) && ($this->helper->access->check('template.delete', null, 'com_importer') ||
		                         $this->helper->access->check('template.delete.own', null, 'com_importer')))
		{
			JToolBarHelper::deleteList('', $this->view_list . '.delete', 'JTOOLBAR_DELETE');
		}
	}
}
