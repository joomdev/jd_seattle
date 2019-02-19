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
 * View to edit
 */
class SellaciousViewLocation extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'location';

	/** @var  string */
	protected $view_item = 'location';

	/** @var  string */
	protected $view_list = 'locations';
}
