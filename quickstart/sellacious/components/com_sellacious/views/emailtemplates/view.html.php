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
 * View class for a list of licenses.
 *
 * @since  1.5.0
 */
class SellaciousViewEmailTemplates extends SellaciousViewList
{
	/**
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $action_prefix = 'emailtemplate';

	/**
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $view_item = 'emailtemplate';

	/**
	 * @var    string
	 *
	 * @since  1.5.0
	 */
	protected $view_list = 'emailtemplates';

	/**
	 * @var    array
	 *
	 * @since  1.5.0
	 */
	protected $lists = array();

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	public function addToolbar()
	{
		parent::addToolbar();

		JToolbarHelper::link(JRoute::_('index.php?option=com_sellacious&view=emailoption&layout=edit'), 'COM_SELLACIOUS_TOOLBAR_EMAILOPTIONS', 'emailOptions');
	}
}
