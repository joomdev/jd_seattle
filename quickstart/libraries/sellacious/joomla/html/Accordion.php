<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Html;

// no direct access.
defined('_JEXEC') or die;

use JFactory;
use JHtml;
use JText;
use Joomla\Utilities\ArrayHelper;

/**
 * @package  Fix for Joomla Accordions to use JLayouts
 *
 * @since    1.6.1
 */

class Accordion
{
	protected static $loaded;

	/**
	 * Add javascript support for Bootstrap accordians and insert the accordian
	 *
	 * @param   string  $selector  The ID selector for the tooltip.
	 * @param   array   $params    An array of options for the tooltip.
	 *                             Options for the tooltip can be:
	 *                             - parent  selector  If selector then all collapsible elements under the specified parent will be closed when this
	 *                                                 collapsible item is shown. (similar to traditional accordion behavior)
	 *                             - toggle  boolean   Toggles the collapsible element on invocation
	 *                             - active  string    Sets the active slide during load
	 *
	 *                             - onShow    function  This event fires immediately when the show instance method is called.
	 *                             - onShown   function  This event is fired when a collapse element has been made visible to the user
	 *                                                   (will wait for css transitions to complete).
	 *                             - onHide    function  This event is fired immediately when the hide method has been called.
	 *                             - onHidden  function  This event is fired when a collapse element has been hidden from the user
	 *                                                   (will wait for css transitions to complete).
	 *
	 * @return  string  HTML for the accordian
	 *
	 * @since   3.0
	 */

	public static function startAccordion($selector = 'myAccordian', $params = array())
	{
		if (!isset(static::$loaded[__METHOD__][$selector]))
		{
			// Setup options object
			$opt['parent'] = isset($params['parent']) ? ($params['parent'] == true ? '#' . $selector : $params['parent']) : false;
			$opt['toggle'] = isset($params['toggle']) ? (boolean) $params['toggle'] : !($opt['parent'] === false || isset($params['active']));
			$onShow = isset($params['onShow']) ? (string) $params['onShow'] : null;
			$onShown = isset($params['onShown']) ? (string) $params['onShown'] : null;
			$onHide = isset($params['onHide']) ? (string) $params['onHide'] : null;
			$onHidden = isset($params['onHidden']) ? (string) $params['onHidden'] : null;

			$options = JHtml::getJSObject($opt);

			$opt['active'] = isset($params['active']) ? (string) $params['active'] : '';

			static::$loaded[__METHOD__][$selector] = $opt;

			$parents = array_key_exists(__METHOD__, static::$loaded) ? array_filter(array_column(static::$loaded[__METHOD__], 'parent')) : array();

			return $html = \JLayoutHelper::render('sellacious.html.accordion.start', get_defined_vars());
		}
	}


	/**
	 * Close the current accordion
	 *
	 * @return  string  HTML to close the accordian
	 *
	 * @since   3.0
	 */
	public static function endAccordion()
	{
		return $html = \JLayoutHelper::render('sellacious.html.accordion.end', get_defined_vars());
	}

	/**
	 * Begins the display of a new accordion slide.
	 *
	 * @param   string  $selector  Identifier of the accordion group.
	 * @param   string  $text      Text to display.
	 * @param   string  $id        Identifier of the slide.
	 * @param   string  $class     Class of the accordion group.
	 *
	 * @return  string  HTML to add the slide
	 *
	 * @since   3.0
	 */
	public static function addSlide($selector, $text, $id, $class = '')
	{
		$accordion = static::$loaded[__CLASS__ . '::startAccordion'][$selector];
		$in        = ($accordion['active'] == $id) ? ' show' : '';
		$collapsed = ($accordion['active'] == $id) ? '' : ' collapsed';
		$parent    = $accordion['parent'] ? ' data-parent="' . $accordion['parent'] . '"' : '';
		$class     = (!empty($class)) ? ' ' . $class : '';

		return $html = \JLayoutHelper::render('sellacious.html.accordion.addslide', get_defined_vars());
	}

	/**
	 * Close the current slide
	 *
	 * @return  string  HTML to close the slide
	 *
	 * @since   3.0
	 */
	public static function endSlide()
	{
		return $html = \JLayoutHelper::render('sellacious.html.accordion.endslide', get_defined_vars());
	}
}
