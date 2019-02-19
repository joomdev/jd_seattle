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
 * View to edit a sellacious user account
 *
 * @property  int $counter  Used for sub layout calling per record index
 *
 * @since   1.2.0
 */
class SellaciousViewUser extends SellaciousViewForm
{
	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'user';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'user';

	/**
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = 'users';
}
