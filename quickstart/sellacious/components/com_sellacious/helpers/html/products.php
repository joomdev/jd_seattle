<?php
/**
 * @version     __DEOPLOY_VERSION__
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;


/**
 * Products HTML helper
 *
 * @since  __DEOPLOY_VERSION__
 */
class JHtmlProducts
{
	/**
	 * Show the published/unpublished/pending/disapproved links
	 *
	 * @param   integer  $value      The state value
	 * @param   integer  $i          Row number
	 * @param   boolean  $canChange  Is user allowed to change?
	 *
	 * @return  string       HTML code
	 *
	 * @since 1.6.0
	 */
	public static function status($value = 0, $i, $canChange = true)
	{
		JHtml::_('bootstrap.tooltip');

		// Array of image, task, title, action
		$states = array(
			0 => array('unpublish', 'products.publish', 'JTOOLBAR_UNPUBLISH', 'JTOOLBAR_PUBLISH'),
			1 => array('publish', 'products.unpublish', 'JTOOLBAR_PUBLISH', 'JTOOLBAR_UNPUBLISH'),
			-1 => array('pending', 'products.disapprove', 'COM_SELLACIOUS_PRODUCTS_PENDING_APPROVAL_BUTTON', 'COM_SELLACIOUS_PRODUCTS_DISAPPROVED_BUTTON'),
			-2 => array('trash', 'products.publish', 'JTOOLBAR_TRASH', 'JTOOLBAR_PUBLISH'),
			-3 => array('not-ok', 'products.pending', 'COM_SELLACIOUS_PRODUCTS_DISAPPROVED_BUTTON', 'COM_SELLACIOUS_PRODUCTS_PENDING_APPROVAL_BUTTON'),
		);
		$state = ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon  = $state[0];

		if ($canChange)
		{
			if($state[1] == 'products.pending' || $state[1] == 'products.disapprove')
			{
				$html = '<a class="btn btn-micro hasTooltip disabled'
					. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
					. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
			}
			else
			{
				$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
					. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3])
					. '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
			}

		}
		else
		{
			$html = '<a class="btn btn-micro hasTooltip disabled' . ($value == 1 ? ' active' : '') . '" title="'
				. JHtml::_('tooltipText', $state[2]) . '"><span class="icon-' . $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
