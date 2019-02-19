<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * HTML View class for the Languages component.
 *
 * @since  1.6.0
 */
class LanguagesViewLanguage extends SellaciousViewForm
{
	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $action_prefix = 'language';

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $view_item = 'language';

	/**
	 * @var   string
	 *
	 * @since   1.6.0
	 */
	protected $view_list = 'languages';
}
