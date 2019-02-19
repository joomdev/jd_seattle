<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Reports table class
 *
 * @since 1.6.0
 */
class SellaciousTableReport extends SellaciousTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver $db A database connector object
	 *
	 * @since   1.6.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__sellacious_reports', 'id', $db);
	}
}
