<?php
/**
 * @version     1.6.1
 * @package     Sellacious Filters Module
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package  Sellacious
 *
 * @since   1.6.0
 */
class ModSellaciousFinderForStoreHelper
{
	/**
	 * Check whether the module should be displayed on current page
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 *
	 * @throws  \Exception
	 */
	public static function validate()
	{
		$app = JFactory::getApplication();

		$option = $app->input->get('option');
		$view   = $app->input->get('view');
		$shop   = $app->input->get('shop_uid');

		return ($option == 'com_sellacious' && ($view == 'store' || $view == 'products' && !empty($shop)));
	}
}
