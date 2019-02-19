<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the Reports component
 *
 * @since  1.6.0
 */
class SellaciousreportingViewReports extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'report';

	/** @var  string */
	protected $view_item = 'report';

	/** @var  string */
	protected $view_list = 'reports';
}
