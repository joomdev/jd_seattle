<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/**
 * View to edit email template
 *
 * @since   1.5.0
 */
class SellaciousViewEmailTemplate extends SellaciousViewForm
{
	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $action_prefix = 'emailtemplate';

	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $view_item = 'emailtemplate';

	/**
	 * @var    string
	 *
	 * @since   1.5.0
	 */
	protected $view_list = 'emailtemplates';
}
