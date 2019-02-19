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

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious core helper
 *
 * @since  1.0.0
 */
class SellaciousHelperCore
{
	/**
	 * Get the base path for sellacious backend directory. Tries to automatically resolve and update any change in path
	 *
	 * @param   bool  $absolute  Whether to return an absolute physical path
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use path constants JPATH_SELLACIOUS and JPATH_SELLACIOUS_DIR
	 */
	public function getAppPath($absolute = false)
	{
		return $absolute ? JPATH_SELLACIOUS : JPATH_SELLACIOUS_DIR;
	}

	/**
	 * Get the current version of sellacious
	 *
	 * @return  bool|string
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use S_VERSION_CORE constant
	 */
	public function getAppVersion()
	{
		return S_VERSION_CORE;
	}

	/**
	 * Get the active license for the software
	 *
	 * @param   string  $field  Specific value from the license
	 *
	 * @return  Registry|mixed  If $field is given return that value from the license else return entire license registry
	 *
	 * @since   1.0.0
	 */
	public function getLicense($field = null)
	{
		$helper = SellaciousHelper::getInstance();

		if ($field)
		{
			$license = $helper->config->get('license.' . $field, null, 'sellacious', 'application');
		}
		else
		{
			$license = $helper->config->get('license', array(), 'sellacious', 'application');
		}

		return $field ? $license : new Registry($license);
	}

	/**
	 * Check whether this copy of sellacious is configured before first use
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function isConfigured()
	{
		// Todo: Check if all required configurations are valid
		$helper = SellaciousHelper::getInstance();
		$config = $helper->config->getParams();

		return $config->count() > 10;
	}

	/**
	 * Check whether this copy of sellacious is registered
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function isRegistered()
	{
		$sitekey = $this->getLicense('sitekey');

		return strlen($sitekey) > 3;
	}

	/**
	 * Handles the meta refresh mechanism, usually used for redirecting to download exported file objects.
	 * If an url argument is passed then the url is set in the session to avoid loss during joomla internal redirects.
	 * When nothing is passed the session value will be used to "actually" set the meta refresh tag.
	 *
	 * @param   string  $url  The URL to set redirection meta tag to.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function metaRedirect($url = null)
	{
		$app = JFactory::getApplication();

		if ($url)
		{
			$app->setUserState('com_sellacious.meta.redirect', $url);
		}
		else
		{
			/** @var  JDocumentHtml  $doc */
			$doc  = JFactory::getDocument();
			$meta = $app->getUserState('com_sellacious.meta.redirect', null);

			if ($meta)
			{
				$app->setUserState('com_sellacious.meta.redirect', null);
				$doc->addCustomTag('<meta http-equiv="refresh" content="0; URL=' . $meta . '" />');
			}
		}
	}

	/**
	 * Method to skew a 2-d array. Switches first two indices of the array as
	 * $array['a']['b']      into $array['b']['a']
	 * $array['a']['b']['c'] into $array['b']['a'][c'] etc.
	 *
	 * @param   array $data The 2-d array to skew.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function skew2dArray(array $data)
	{
		$result = array();

		foreach ($data as $dk => $array)
		{
			if (is_array($array))
			{
				foreach ($array as $ak => $value)
				{
					$result[$ak][$dk] = $value;
				}
			}
		}

		return $result;
	}

	/**
	 * Returns ordinal suffix (optionally appended to number) for a given natural number
	 *
	 * If the tens digit of a number is 1, then write "th" after the number. For example: 13th, 19th, 112th, 9,311th.
	 * If the tens digit is not equal to 1, then use the following table:
	 *     If the units digit is:        0    1    2    3    4    5    6    7    8    9
	 *     write this after the number  th   st   nd   rd   th   th   th   th   th   th
	 *
	 * @param   int     $number      Number to add ordinal suffix
	 * @param   boolean $suffix_only Whether to return only the suffix or append it to the given number (default)
	 *
	 * @see     http://en.wikipedia.org/wiki/English_numerals#Ordinal_numbers
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function ordinal($number, $suffix_only = false)
	{
		$tens  = $number % 100;
		$units = $number % 10;

		if (($tens >= 10 && $tens <= 19) || $units == 0 || $units >= 4)
		{
			$suffix = 'th';
		}
		else
		{
			$ends   = array('1' => 'st', '2' => 'nd', '3' => 'rd');
			$suffix = $ends[$units];
		}

		return $suffix_only ? $suffix : $number . $suffix;
	}

	/**
	 * Convert given array into single page csv format
	 *
	 * @param   array  $array  Input Array
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function array2csv($array)
	{
		if (count($array) == 0)
		{
			return '';
		}

		ini_set("auto_detect_line_endings", true);

		ob_start();

		$handle = fopen("php://output", 'w');

		foreach ($array as $row)
		{
			if ($row instanceOf JObject)
			{
				$row = $row->getProperties();
			}

			fputcsv($handle, (array) $row, ',', '"');
		}

		fclose($handle);

		return ob_get_clean();
	}

	/**
	 * Pass an array or object contents from content filtering based on desired data types per key
	 *
	 * @param   object|array  $data    Data to filter
	 * @param   array         $fields  Fields to extract with optional data type specification to apply.
	 *                                 Other keys will be ignored
	 * @param   string        $filter  Global filter if no filter is specified in $fields
	 *
	 * @return  object|array  Data type of input is preserved
	 *
	 * @since   1.0.0
	 */
	public function filterObject($data, $fields, $filter = 'string')
	{
		$oType = gettype($data);
		settype($data, 'array');

		$input = JFilterInput::getInstance();

		$new = array();

		foreach ($fields as $field => $type)
		{
			if (isset($data[$field]))
			{
				$new[$field] = $input->clean($data[$field], $type ? $type : $filter);
			}
			else
			{
				$new[$field] = null;
			}
		}

		settype($new, $oType);

		return $new;
	}

	/**
	 * Pass an list of array or object contents from content filtering based on desired data types per key
	 *
	 * @param   array   $array   Data array to filter (2-D array expected)
	 * @param   array   $fields  Fields to extract with optional data type specification to apply. Other keys will be
	 *                           ignored
	 * @param   string  $filter  global filter if no filter is specified in $fields
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function filterObjects(array $array, $fields, $filter = 'string')
	{
		foreach ($array as $i => $data)
		{
			$array[$i] = self::filterObject($data, $fields, $filter);
		}

		return $array;
	}

	/**
	 * Returns a field of an object in an object array matching some identifier key
	 *
	 * @param   array  $array    Array to lookup
	 * @param   mixed  $key      Key to find
	 * @param   mixed  $match    Value to find in key
	 * @param   mixed  $field    Key for the return value
	 * @param   mixed  $default  Default return value
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	public function getArrayField($array, $key, $match, $field = null, $default = null)
	{
		if (is_array($array))
		{
			foreach ($array as $item)
			{
				// Allow 2D array as well as object array usage.
				$current = (array) $item;

				if (isset($current[$key]) && $current[$key] == $match)
				{
					// We found it.
					if (empty($field))
					{
						// Entire "original" object returned
						return $item;
					}
					elseif (isset($current[$field]))
					{
						// Match found
						return $current[$field];
					}

					// Don't break out! look for another match
				}
			}
		}

		return $default;
	}

	/**
	 * Get last modified date, fallback to created date, and otherwise default
	 *
	 * @param   mixed   $item     Item containing modified, created date attributes
	 * @param   string  $default  Default value if no valid date found
	 * @param   string  $format   Date format for the returned value
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getLastModified($item, $default = '', $format = 'm/d/Y h:i A')
	{
		$db = JFactory::getDbo();

		if (!($item instanceof Registry))
		{
			$item = new Registry($item);
		}

		$date = $item->get('modified');

		if ($date && $date != $db->getNullDate())
		{
			return JHtml::_('date', $date, $format);
		}

		$date = $item->get('created');

		if ($date && $date != $db->getNullDate())
		{
			return JHtml::_('date', $date, $format);
		}

		return $default;
	}

	/**
	 * Validate for a email format
	 *
	 * @param   string  $value
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function isValidEmail($value)
	{
		$pattern = '^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';
		$regex   = chr(1) . $pattern . chr(1);

		// Add unicode property support if available.
		$unicode = defined('JCOMPAT_UNICODE_PROPERTIES') && JCOMPAT_UNICODE_PROPERTIES;
		$regex   = $unicode ? $regex . 'u' : $regex;

		// Handle idn e-mail addresses by converting to punyCode.
		$value = JStringPunycode::emailToPunycode($value);

		// Test the value against the regular expression.
		return preg_match($regex, $value);
	}

	/**
	 * Create associative array out of a given object list or a 2d array
	 *
	 * @param   array   $array     Object list to process
	 * @param   string  $keyCol    Key column
	 * @param   string  $valueCol  Value column
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use ArrayHelper::getColumn() instead.
	 */
	public function arrayAssoc($array, $keyCol = null, $valueCol = null)
	{
		return is_array($array) ? ArrayHelper::getColumn($array, $valueCol, $keyCol) : array();
	}

	/**
	 * Check whether the current user is a guest, and process redirect to login page if so.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function checkGuest()
	{
		$app = JFactory::getApplication();
		$me  = JFactory::getUser();

		if ($me->guest)
		{
			JLog::add(JText::_('COM_SELLACIOUS_ERROR_HELPER_LOGIN_REGISTER'), JLog::INFO, 'jerror');

			$return = JUri::getInstance()->toString();
			$url    = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($return), false);

			$app->redirect($url);
		}
	}

	/**
	 * Return stars icons using font-awesome fa-star-* classes
	 *
	 * @param   float  $value  Displayed value
	 * @param   bool   $show   Show the value after the stars
	 * @param   float  $limit  Upper limit out of which value is calculated
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getStars($value, $show = false, $limit = 5.0)
	{
		$s_max  = 5;
		$number = round($value * $s_max * 2 / $limit);
		$full   = intval($number / 2);
		$half   = $number % 2;
		$empty  = $s_max - ($full + $half);

		$stars[] = str_repeat('<i class="fa fa-star"></i> ', $full);
		$stars[] = str_repeat('<i class="fa fa-star-half-full"></i> ', $half);
		$stars[] = str_repeat('<i class="fa fa-star-o"></i> ', $empty);

		$html = implode($stars);

		$html .= $show ? number_format($value, 1) : '';

		return $html;
	}

	/**
	 * Load relevant plugins for sellacious
	 *
	 * @param   string  $group  Plugin group
	 * @param   string  $name   Plugin name
	 *
	 * @return  JEventDispatcher
	 *
	 * @since   1.0.0
	 */
	public function loadPlugins($group = null, $name = null)
	{
		if (empty($group))
		{
			// Load all sellacious plugin groups
			JPluginHelper::importPlugin('content');
			JPluginHelper::importPlugin('sellacious');
			JPluginHelper::importPlugin('sellaciousrules');
			JPluginHelper::importPlugin('sellaciousshipment');
			JPluginHelper::importPlugin('sellaciouspayment');
		}
		else
		{
			JPluginHelper::importPlugin($group, $name);
		}

		return $dispatcher = JEventDispatcher::getInstance();
	}

	/**
	 * Convert a date value to match a desired timezone
	 *
	 * @param   string      $value     The source date value
	 * @param   string|int  $tzSource  Timezone identifier. Special: UTC (default) or SERVER or <user_id> (int) to match a user's timezone config
	 * @param   string|int  $tzTarget  Timezone identifier. Special: UTC (default) or SERVER or <user_id> (int) to match a user's timezone config
	 *
	 * @return  JDate
	 *
	 * @since   1.0.0
	 */
	public function fixDate($value, $tzSource = 'UTC', $tzTarget = 'UTC')
	{
		$app   = JFactory::getApplication();
		$tzDef = $app->get('offset', 'UTC');

		// Find source timezone
		if ($tzSource === null || is_numeric($tzSource))
		{
			$user = JFactory::getUser($tzSource);
			$tz1  = $user->getParam('timezone', $tzDef);
		}
		elseif (strtoupper($tzSource) == 'SERVER')
		{
			$tz1 = $tzDef;
		}
		else
		{
			$tz1 = $tzSource;
		}

		// Find target timezone
		if (is_null($tzTarget) || is_numeric($tzTarget))
		{
			$user = JFactory::getUser($tzTarget);
			$tz2  = $user->getParam('timezone', $tzDef);
		}
		elseif (strtoupper($tzSource) == 'SERVER')
		{
			$tz2 = $tzDef;
		}
		else
		{
			$tz2 = $tzTarget;
		}

		try
		{
			$date = JFactory::getDate($value, $tz1)->setTimezone(new DateTimeZone($tz2));
		}
		catch (Exception $e)
		{
			$date = null;
		}

		return $date;
	}

	/**
	 * Custom autoloader for Phar archives as Joomla 3.8.9 broke the autoloading of Phar archives
	 *
	 * @param   string  $namespace
	 * @param   string  $phar
	 *
	 * @since   1.6.0
	 */
	public function registerPharPsr4($namespace, $phar)
	{
		spl_autoload_register(function ($class) use ($namespace, $phar) {
			if (!class_exists($class, false) && strpos(trim($class, '/'), $namespace) === 0)
			{
				$classFilePath = $phar . '/' . substr_replace(str_replace('\\', '/', $class) . '.php', '', 0, strlen($namespace) + 1);

				@include_once $classFilePath;
			}
			return true;
		}, false);
	}

	/**
	 * Get a relative date-time from the given date-time with respect to the base date (default = now)
	 *
	 * @param   string  $datetime  The date which need to converted into relative date
	 * @param   string  $base      Base for the relative date calculation, defaults to current date-time
	 * @param   string  $tz        The timezone in which the calculation will be based
	 * @param   string  $tzo       The timezone of the given date(s)
	 *
	 * @return  string
	 *
	 * @fixme  Sync logic with shortDateTime()
	 * @see    shortDateTime()
	 *
	 * @since   1.0.0
	 */
	public function relativeDateTime($datetime, $base = 'now', $tz = 'utc', $tzo = 'utc')
	{
		$segments = array();

		$nowDate = $this->fixDate($base, $tzo, $tz);
		$valDate = $this->fixDate($datetime, $tzo, $tz);

		$diff_u = $valDate->toUnix() - $nowDate->toUnix();
		$diff_y = $valDate->format('Y') - $nowDate->format('Y');
		$diff_m = $valDate->format('m') - $nowDate->format('m');
		$diff_d = $valDate->format('d') - $nowDate->format('d');

		$diff_h = $valDate->format('H') - $nowDate->format('H');
		$diff_i = $valDate->format('i') - $nowDate->format('i');
		$diff_s = $valDate->format('s') - $nowDate->format('s');

		if ($diff_y == 0)
		{
			if ($diff_m == 0)
			{
				if ($diff_d == 0)
				{
					if ($diff_h == 0)
					{
						if ($diff_i == 0)
						{
							if ($diff_s == 0)
							{
								$suffix     = false;
								$segments[] = JText::_('COM_SELLACIOUS_DATE_RELATIVE_NOW');
							}
							else
							{
								$suffix     = true;
								$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_SECONDS', abs($diff_s));
							}
						}
						else
						{
							$suffix     = true;
							$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_MINUTES', abs($diff_i));
						}
					}
					else
					{
						$suffix = true;

						// Must use the 'unix timestamp' as hour ± min causes problems around ±30 minutes boundary.
						$diff_uh = floor($diff_u / 3600);
						$diff_ui = floor(($diff_u % 3600) / 60);

						$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_HOURS', abs($diff_uh));

						if (abs($diff_ui) > 0)
						{
							$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_MINUTES', abs($diff_ui));
						}
					}
				}
				elseif (abs($diff_d) == 1)
				{
					$suffix     = false;
					$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_DAYS', $diff_d);
					$segments[] = $valDate->format('h:i A');
				}
				else
				{
					$suffix     = false;
					$segments[] = $valDate->format('M dS, h:i A');
				}
			}
			else
			{
				$suffix     = false;
				$segments[] = $valDate->format('M dS');
			}
		}
		else
		{
			$suffix     = false;
			$segments[] = $valDate->format('M dS, Y');
		}

		if ($suffix)
		{
			if ($diff_u > 0)
			{
				array_unshift($segments, JText::_('COM_SELLACIOUS_DATE_RELATIVE_PREFIX_REMAINING'));
			}
			else
			{
				$segments[] = JText::_('COM_SELLACIOUS_DATE_RELATIVE_SUFFIX_AGO');
			}
		}

		$output = implode(' ', $segments);

		return $output;
	}

	/**
	 * Get a relative date-time from the given date-time with respect to the base date (default = now)
	 *
	 * @param   string  $datetime  The date which need to converted into relative date
	 * @param   string  $base      Base for the relative date calculation, defaults to current date-time
	 * @param   string  $tz        The timezone in which the calculation will be based
	 * @param   string  $tzo       The timezone of the given date(s)
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function shortDateTime($datetime, $base = 'now', $tz = 'utc', $tzo = 'utc')
	{
		$segments = array();

		$nowDate = $this->fixDate($base, $tzo, $tz);
		$valDate = $this->fixDate($datetime, $tzo, $tz);

		$diff_u = $valDate->toUnix() - $nowDate->toUnix();
		$diff_y = $valDate->format('Y', true) - $nowDate->format('Y', true);
		$diff_m = $valDate->format('m', true) - $nowDate->format('m', true);
		$diff_d = $valDate->format('d', true) - $nowDate->format('d', true);

		$diff_h = $valDate->format('H', true) - $nowDate->format('H', true);
		$diff_i = $valDate->format('i', true) - $nowDate->format('i', true);
		$diff_s = $valDate->format('s', true) - $nowDate->format('s', true);

		if ($diff_y == 0)
		{
			if ($diff_m == 0)
			{
				if ($diff_d == 0)
				{
					if ($diff_h == 0)
					{
						if ($diff_i == 0)
						{
							if ($diff_s == 0)
							{
								$suffix     = false;
								$segments[] = JText::_('COM_SELLACIOUS_DATE_RELATIVE_NOW');
							}
							else
							{
								$suffix     = true;
								$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_SECONDS', abs($diff_s));
							}
						}
						else
						{
							$suffix     = true;
							$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_MINUTES', abs($diff_i));
						}
					}
					else
					{
						$suffix = true;

						// Must use the 'unix timestamp' as hour ± min causes problems around ±30 minutes boundary.
						$diff_uh = floor(abs($diff_u) / 3600);
						$diff_ui = floor((abs($diff_u) % 3600) / 60);

						// Now an hour may have get consumed as minutes
						if ($diff_uh > 0)
						{
							$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_HOURS', abs($diff_uh));
						}

						if ($diff_ui > 0)
						{
							$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_MINUTES', abs($diff_ui));
						}
					}

					$shortDate = $valDate->format('(h:i A)', true);
				}
				elseif (abs($diff_d) == 1)
				{
					$suffix     = false;
					$segments[] = JText::plural('COM_SELLACIOUS_DATE_RELATIVE_N_DAYS', $diff_d);
					$shortDate  = $valDate->format('(h:i A)', true);
				}
				elseif (abs($diff_d) < 7)
				{
					$suffix    = false;
					$shortDate = $valDate->format('l h:i A', true);
				}
				else
				{
					$suffix    = false;
					$shortDate = $valDate->format('M dS, h:i A', true);
				}
			}
			else
			{
				$suffix    = false;
				$shortDate = $valDate->format('M dS', true);
			}
		}
		else
		{
			$suffix    = false;
			$shortDate = $valDate->format('M dS, Y', true);
		}

		if ($suffix)
		{
			if ($diff_u > 0)
			{
				array_unshift($segments, JText::_('COM_SELLACIOUS_DATE_RELATIVE_PREFIX_REMAINING'));
			}
			else
			{
				$segments[] = JText::_('COM_SELLACIOUS_DATE_RELATIVE_SUFFIX_AGO');
			}
		}

		$segments[] = $shortDate;

		$output = implode(' ', $segments);

		/*
		// TEST CASE FOR THIS FUNCTION AS BELOW

		$array = array();
		$tDate = JFactory::getDate('2015-09-19 00:00');

		for ($i = 1; $i <= 288; $i++)
		{
			$key         = $tDate->format('Y-m-d H:i:s', true);
			$array[$key] = $this->helper->core->shortDateTime($tDate->format('Y-m-d H:i:s'), '2015-09-19 12:00:00', null);

			$tDate->addInterval(5, 'min');
		}

		printr($array);
		*/

		return trim($output);
	}

	/**
	 * Rebuild backoffice menu from the xml
	 *
	 * @param   bool  $clear  Whether to clear all the existing menu items before recreating them.
	 *
	 * @since   1.4.0
	 */
	public function rebuildMenu($clear = false)
	{
		// Create menu items for backoffice
		JLoader::register('SellaciousInstallerScript', JPATH_ADMINISTRATOR . '/manifests/files/sellacious/installer.php');

		if (class_exists('SellaciousInstallerScript'))
		{
			$installer = new SellaciousInstallerScript;
			$installer->rebuildMenu(JPATH_SELLACIOUS . '/menu.xml', $clear);
		}
	}

	/**
	 * Render sellacious brand footer
	 *
	 * @return  string
	 *
	 * @since   1.4.4
	 */
	public function renderBrandFooter()
	{
		$html   = '';
		$helper = SellaciousHelper::getInstance();

		if ($helper->config->get('show_brand_footer', 1) || !$helper->access->isSubscribed())
		{
			$basePath = JPATH_SELLACIOUS . '/templates/sellacious/html/layouts';
			$html     = JLayoutHelper::render('com_sellacious.footer', null, $basePath, array('debug' => false));
		}

		return $html;
	}

	/**
	 * Request a trial subscription
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function requestTrial()
	{
		$jarvisSite = $this->getJarvisSite();

		$url  = $jarvisSite . '/index.php?option=com_jarvis&task=site.getTrialAjax';
		$data = $this->getLicense();
		$http = JHttpFactory::getHttp();
		$resp = $http->post($url, $data->toArray());

		if ($resp->code != 200 || !($response = json_decode($resp->body)) || !is_object($response))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_LICENSING'));
		}

		if ($response->status != 1)
		{
			throw new Exception($response->message);
		}

		return true;
	}

	/**
	 * Generate levels(multi dimension array) from a linear array
	 *
	 * @param   stdClass[]  $items      The list of object to be levelled
	 * @param   string      $childKey   Key name for the child elements array
	 * @param   string      $parentKey  Key name for the parent identifier
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.2
	 */
	public function buildLevels($items, $childKey = 'children', $parentKey = 'parent_id')
	{
		$result = array();

		if (isset($items[1]))
		{
			$items[1]->$childKey = array();

			foreach ($items as &$node)
			{
				if (isset($items[$node->$parentKey]))
				{
					$parent = &$items[$node->$parentKey];

					if (!isset($parent->$childKey))
					{
						$parent->$childKey = array();
					}

					$siblings   = &$parent->$childKey;
					$siblings[] = &$node;
				}
			}

			$result = &$items[1]->$childKey;
		}

		return $result;
	}

	/**
	 * Method to return a link to the jarvis site
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getJarvisSite()
	{
		return 'https://www.sellacious.com';
	}
}
