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
 * @package  Calendar fix for Joomla 3.7 (BC break in J3.7.0)
 *
 * @since    1.4.7
 */
class Calendar
{
	/**
	 * Add unobtrusive JavaScript support for a calendar control. Added as this method is deprecated in Joomla3.7
	 *
	 * @return  void
	 *
	 * @since   J1.5-3.6.x
	 */
	public static function behaviorCalendar()
	{
		// Only load once
		static $loaded;

		if (!version_compare(JVERSION, '3.7', '>='))
		{
			JHtml::_('behavior.calendar');
		}
		elseif (!$loaded)
		{
			$document = JFactory::getDocument();
			$tag      = JFactory::getLanguage()->getTag();

			JHtml::_('stylesheet', 'system/calendar-jos.css', array(' title' => JText::_('JLIB_HTML_BEHAVIOR_GREEN'), ' media' => 'all'), true);
			JHtml::_('script', $tag . '/calendar.js', false, true);
			JHtml::_('script', $tag . '/calendar-setup.js', false, true);

			$translation = static::calendartranslation();

			if ($translation)
			{
				$document->addScriptDeclaration($translation);
			}

			$loaded = true;
		}
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param   string  $value    The date value
	 * @param   string  $name     The name of the text field
	 * @param   string  $id       The id of the text field
	 * @param   string  $format   The date format
	 * @param   mixed   $attribs  Additional HTML attributes
	 *
	 * @return  string  HTML markup for a calendar field
	 *
	 * @since   1.5
	 */
	public static function calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null)
	{
		static $done;

		if ($done === null)
		{
			$done = array();
		}

		$readonly = isset($attribs['readonly']) && $attribs['readonly'] == 'readonly';
		$disabled = isset($attribs['disabled']) && $attribs['disabled'] == 'disabled';

		if (is_array($attribs))
		{
			$attribs['class'] = isset($attribs['class']) ? $attribs['class'] : 'input-medium';
			$attribs['class'] = trim($attribs['class'] . ' hasTooltip');

			$attribs = ArrayHelper::toString($attribs);
		}

		JHtml::_('bootstrap.tooltip');

		// Format value when not nulldate ('0000-00-00 00:00:00'), otherwise blank it as it would result in 1970-01-01.
		if ($value && $value != JFactory::getDbo()->getNullDate() && strtotime($value) !== false)
		{
			$tz = date_default_timezone_get();
			date_default_timezone_set('UTC');
			$inputvalue = strftime($format, strtotime($value));
			date_default_timezone_set($tz);
		}
		else
		{
			$inputvalue = '';
		}

		// Load the calendar behavior
		static::behaviorCalendar();

		// Only display the triggers once for each control.
		if (!in_array($id, $done))
		{
			$document = JFactory::getDocument();
			$document
				->addScriptDeclaration(
				'jQuery(document).ready(function($) {
					Calendar.setup({
						// Id of the input field
						inputField: "' . $id . '",
						// Format of the input field
						ifFormat: "' . $format . '",
						// Trigger for the calendar (button ID)
						button: "' . $id . '_img",
						// Alignment (defaults to "Bl")
						align: "Tl",
						singleClick: true,
						firstDay: ' . JFactory::getLanguage()->getFirstDay() . '
					});
				});'
			);
			$done[] = $id;
		}

		// Hide button using inline styles for readonly/disabled fields
		$btn_style = ($readonly || $disabled) ? ' style="display:none;"' : '';
		$div_class = (!$readonly && !$disabled) ? ' class="input-append"' : '';

		return '<div' . $div_class . '>'
				. '<input type="text" title="' . ($inputvalue ? JHtml::_('date', $value, null, null) : '')
				. '" name="' . $name . '" id="' . $id . '" value="' . htmlspecialchars($inputvalue, ENT_COMPAT, 'UTF-8') . '" ' . $attribs . ' />'
				. '<button type="button" class="btn" id="' . $id . '_img" ' . $btn_style . '><span class="icon-calendar"></span></button>'
			. '</div>';
	}

	/**
	 * Internal method to translate the JavaScript Calendar
	 *
	 * @return  string  JavaScript that translates the object
	 *
	 * @since   1.5
	 */
	protected static function calendarTranslation()
	{
		static $jsscript = 0;

		// Guard clause, avoids unnecessary nesting
		if ($jsscript)
		{
			return false;
		}

		$jsscript = 1;

		// To keep the code simple here, run strings through JText::_() using array_map()
		$callback = array('JText', '_');
		$weekdays_full = array_map(
			$callback, array(
				'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY',
			)
		);
		$weekdays_short = array_map(
			$callback,
			array(
				'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN',
			)
		);
		$months_long = array_map(
			$callback, array(
				'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
				'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
			)
		);
		$months_short = array_map(
			$callback, array(
				'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
				'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',
			)
		);

		// This will become an object in Javascript but define it first in PHP for readability
		$today = " " . JText::_('JLIB_HTML_BEHAVIOR_TODAY') . " ";
		$text = array(
			'INFO'           => JText::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR'),
			'ABOUT'          => "DHTML Date/Time Selector\n"
				. "(c) dynarch.com 2002-2005 / Author: Mihai Bazon\n"
				. "For latest version visit: http://www.dynarch.com/projects/calendar/\n"
				. "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details."
				. "\n\n"
				. JText::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION')
				. JText::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT')
				. JText::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT')
				. JText::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE'),
			'ABOUT_TIME'      => "\n\n"
				. "Time selection:\n"
				. "- Click on any of the time parts to increase it\n"
				. "- or Shift-click to decrease it\n"
				. "- or click and drag for faster selection.",
			'PREV_YEAR'       => JText::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU'),
			'PREV_MONTH'      => JText::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU'),
			'GO_TODAY'        => JText::_('JLIB_HTML_BEHAVIOR_GO_TODAY'),
			'NEXT_MONTH'      => JText::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU'),
			'SEL_DATE'        => JText::_('JLIB_HTML_BEHAVIOR_SELECT_DATE'),
			'DRAG_TO_MOVE'    => JText::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE'),
			'PART_TODAY'      => $today,
			'DAY_FIRST'       => JText::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'),
			'WEEKEND'         => JFactory::getLanguage()->getWeekEnd(),
			'CLOSE'           => JText::_('JLIB_HTML_BEHAVIOR_CLOSE'),
			'TODAY'           => JText::_('JLIB_HTML_BEHAVIOR_TODAY'),
			'TIME_PART'       => JText::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE'),
			'DEF_DATE_FORMAT' => "%Y-%m-%d",
			'TT_DATE_FORMAT'  => JText::_('JLIB_HTML_BEHAVIOR_TT_DATE_FORMAT'),
			'WK'              => JText::_('JLIB_HTML_BEHAVIOR_WK'),
			'TIME'            => JText::_('JLIB_HTML_BEHAVIOR_TIME'),
		);

		return 'Calendar._DN = ' . json_encode($weekdays_full) . ';'
			. ' Calendar._SDN = ' . json_encode($weekdays_short) . ';'
			. ' Calendar._FD = 0;'
			. ' Calendar._MN = ' . json_encode($months_long) . ';'
			. ' Calendar._SMN = ' . json_encode($months_short) . ';'
			. ' Calendar._TT = ' . json_encode($text) . ';';
	}
}
