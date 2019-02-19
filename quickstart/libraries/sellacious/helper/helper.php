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
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

/**
 * Sellacious helper.
 *
 * @property  SellaciousHelperAccess          access
 * @property  SellaciousHelperCart            cart
 * @property  SellaciousHelperCategory        category
 * @property  SellaciousHelperConfig          config
 * @property  SellaciousHelperCore            core
 * @property  SellaciousHelperClient          client
 * @property  SellaciousHelperCurrency        currency
 * @property  SellaciousHelperCoupon          coupon
 * @property  SellaciousHelperField           field
 * @property  SellaciousHelperLicense         license
 * @property  SellaciousHelperListing         listing
 * @property  SellaciousHelperLocation        location
 * @property  SellaciousHelperMedia           media
 * @property  SellaciousHelperManufacturer    manufacturer
 * @property  SellaciousHelperMessage         message
 * @property  SellaciousHelperOrder           order
 * @property  SellaciousHelperPackage         package
 * @property  SellaciousHelperPayment         payment
 * @property  SellaciousHelperPaymentMethod   paymentMethod
 * @property  SellaciousHelperProduct         product
 * @property  SellaciousHelperProductQuery    productQuery
 * @property  SellaciousHelperPrice           price
 * @property  SellaciousHelperProfile         profile
 * @property  SellaciousHelperRelatedProduct  relatedProduct
 * @property  SellaciousHelperRating          rating
 * @property  SellaciousHelperShopRule        shopRule
 * @property  SellaciousHelperSeller          seller
 * @property  SellaciousHelperShipping        shipping
 * @property  SellaciousHelperShippingRule    shippingRule
 * @property  SellaciousHelperSplCategory     splCategory
 * @property  SellaciousHelperStaff           staff
 * @property  SellaciousHelperUnit            unit
 * @property  SellaciousHelperUser            user
 * @property  SellaciousHelperVariant         variant
 * @property  SellaciousHelperTransaction     transaction
 * @property  SellaciousHelperTranslation     translation
 * @property  SellaciousHelperWishlist        wishlist
 * @property  SellaciousHelperReport          report
 *
 * @since  1.0.0
 */
class SellaciousHelper
{
	/**
	 * @var  array
	 *
	 * @since   1.0.0
	 */
	protected static $helpers = array();

	/**
	 * @var  SellaciousHelper
	 *
	 * @since   1.0.0
	 */
	private static $instance = false;

	/**
	 * Initialize Sellacious Application.
	 * Assign path reference to all relevant class for the auto-loader
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		$options['text_file'] = 'errors-' . date('Y-m-d') . '.log';
		$options['format']    = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";

		JLog::addLogger($options, JLog::ALL, array('error', 'warning', 'note', 'jerror'));

		$options['text_file'] = 'route-errorss.log';
		$options['format']    = "{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}";

		JLog::addLogger($options, JLog::ALL, array('jroute'));

		// Do not error in Cli environment
		if (JFactory::$application && !JFactory::getApplication()->isClient('sellacious'))
		{
			JFormHelper::loadFieldClass('radio');
			JFormHelper::loadFieldClass('checkbox');
			JFormHelper::loadFieldClass('checkboxes');
		}

		// Global include
		JTable::addIncludePath(JPATH_SITE . '/components/com_sellacious/tables');
		JForm::addFormPath(JPATH_SITE . '/components/com_sellacious/models/forms');
		JFormHelper::addFieldPath(JPATH_SITE . '/components/com_sellacious/models/fields');
		JFormHelper::addRulePath(JPATH_SITE . '/components/com_sellacious/models/rules');

		// Client specific include
		JTable::addIncludePath(JPATH_BASE . '/components/com_sellacious/tables');
		JForm::addFormPath(JPATH_BASE . '/components/com_sellacious/models/forms');
		JFormHelper::addFieldPath(JPATH_BASE . '/components/com_sellacious/models/fields');
		JFormHelper::addRulePath(JPATH_BASE . '/components/com_sellacious/models/rules');

		$lang         = JFactory::getLanguage();
		$language_tag = $lang->getTag();

		$lang->load('com_sellacious', JPATH_SITE . '/components/com_sellacious', 'en-GB');
		$lang->load('com_sellacious', JPATH_SITE, 'en-GB');
		$lang->load('com_sellacious', JPATH_SITE . '/components/com_sellacious', $language_tag);
		$lang->load('com_sellacious', JPATH_SITE, $language_tag);

		if (JFactory::$document && JFactory::getDocument()->getType() === 'html')
		{
			// Todo: move this to a separate behaviour function/class
			JText::script('COM_SELLACIOUS_LABEL_KEYBOARD_SHORTCUTS', true);
			JText::script('COM_SELLACIOUS_USER_LOAD_WAIT_ADDRESS', true);
		}

		$this->compatibility();
	}

	/**
	 * Get a singleton instance of a sellacious helper class
	 * Create one if not already, otherwise return existing instance
	 *
	 * @param   string  $key  Class name for the helper requested
	 *
	 * @return  mixed
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function __get($key)
	{
		return self::getInstance($key);
	}

	/**
	 * Get an instance of helper class.
	 * Create one if not already, otherwise return existing instance
	 *
	 * @param   string  $name    Name of the helper class
	 * @param   string  $prefix  The helper prefix
	 *
	 * @return  SellaciousHelper
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function getInstance($name = '', $prefix = 'Sellacious')
	{
		if (false === self::$instance)
		{
			self::$instance = new self;

			// Call the state detection logic on first instantiation
			self::$instance->detectVersion();
			self::$instance->detectPath();
			self::$instance->core->loadPlugins();
		}

		if ($name == '')
		{
			return self::$instance;
		}

		$key       = strtolower($prefix . ':' . $name);
		$className = ucfirst($prefix) . 'Helper' . ucfirst(strtolower($name));

		if (!isset(self::$helpers[$key]))
		{
			self::$helpers[$key] = class_exists($className) ? new $className : false;
		}

		if (self::$helpers[$key] === false)
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_HELPER_NOT_SUPPORTED', $className), '5501');
		}

		return self::$helpers[$key];
	}

	/**
	 * Method to detect sellacious version
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	final private function detectVersion()
	{
		if (!defined('S_VERSION_CORE'))
		{
			$ver    = '0.0.0';
			$table  = JTable::getInstance('Extension');
			$config = array(
				'type'      => 'package',
				'element'   => 'pkg_sellacious_extended',
				'client_id' => 0,
			);
			$table->load($config);

			if ($table->get('extension_id'))
			{
				$cache = json_decode($table->get('manifest_cache'));

				if (is_object($cache) && isset($cache->version))
				{
					$ver = $cache->version;
				}
			}

			define('S_VERSION_CORE', $ver);
		}
	}

	/**
	 * Get the base path for sellacious backend directory. Tries to automatically resolve and update any change in path
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	final private function detectPath()
	{
		// If sellacious directory is not already identified, evaluate it
		$config = ConfigHelper::getInstance('sellacious', 'application');
		$path   = $config->get('app_path', '');

		// If we are on sellacious application, try to detect and update any change automatically!
		if (defined('JPATH_SELLACIOUS_DIR'))
		{
			if (JPATH_SELLACIOUS_DIR !== $path)
			{
				$path = JPATH_SELLACIOUS_DIR;

				$config->set('app_path', $path);
				$config->store();
			}

			return;
		}

		// If the path does not exists we'd try to auto-detect before we give up.
		if (strlen($path) == 0 || !file_exists(JPATH_ROOT . '/' . $path . '/sellacious.xml'))
		{
			jimport('joomla.filesystem.folder');

			$folders = JFolder::folders(JPATH_ROOT);

			foreach ($folders as $folder)
			{
				if (file_exists(JPATH_ROOT . '/' . $folder . '/sellacious.xml'))
				{
					$path = $folder;

					$config->set('app_path', $folder);
					$config->store();

					break;
				}
			}
		}

		$exists = strlen($path) && file_exists(JPATH_ROOT . '/' . $path . '/sellacious.xml');

		define('JPATH_SELLACIOUS_DIR', $exists ? $path : 'sellacious');
		define('JPATH_SELLACIOUS', JPATH_ROOT . '/' . JPATH_SELLACIOUS_DIR);

		if (!$exists)
		{
			// We can't use language key if the library could not be loaded.
			throw new Exception('The sellacious installation was not found. Make sure it is installed correctly and its files are intact.', 500);
		}
	}

	/**
	 * Apply Joomla specific b/c patches
	 *
	 * @return  void
	 *
	 * @since   1.4.7
	 */
	public function compatibility()
	{
		/**
		 * PHP7 reserves String as keyword. Class renamed in Joomla 3.5.0 and aliased as 'String'
		 */
		if (version_compare(JVERSION, '3.5', 'lt') && version_compare(PHP_VERSION, '7.0', 'lt'))
		{
			if (class_exists('\Joomla\String\String') && class_exists('\Joomla\String\StringHelper'))
			{
				class_alias('\Joomla\String\String', '\Joomla\String\StringHelper');
			}
		}

		/**
		 * Joomla removed old calendar functions from 3.7 release, it may come back in 3.7.1 but we don't depend anymore
		 */
		if (version_compare(JVERSION, '3.7', 'ge'))
		{
			require_once dirname(__DIR__) . '/joomla/html/calendar.php';

			//Accordion Override
			require_once dirname(__DIR__) . '/joomla/html/Accordion.php';

			JHtml::register('calendar', array('Sellacious\Html\Calendar', 'calendar'));
			JHtml::register('behavior.calendar', array('Sellacious\Html\Calendar', 'behaviorCalendar'));


			//Accordion Overrides
			JHtml::register('bootstrap.startAccordion', array('Sellacious\Html\Accordion', 'startAccordion'));
			JHtml::register('bootstrap.endAccordion', array('Sellacious\Html\Accordion', 'endAccordion'));
			JHtml::register('bootstrap.addSlide', array('Sellacious\Html\Accordion', 'addSlide'));
			JHtml::register('bootstrap.endSlide', array('Sellacious\Html\Accordion', 'endSlide'));
		}
	}
}
