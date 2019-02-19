<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Include dependencies
jimport('sellacious.loader');

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Report\ReportHandler;
use Sellacious\Report\ReportHelper;

/**
 * Cart report plugin of Sellacious Report
 *
 * @since  1.6.0
 */
class plgSystemSellaciousReportsCart extends SellaciousPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.6.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function __construct($subject, array $config)
	{
		parent::__construct($subject, $config);

		JLoader::registerNamespace('Sellacious', __DIR__ . '/libraries');
	}

	/**
	 * Registers handlers to the reporting system that will be managed by this plugin
	 *
	 * @param   string  $context  The calling context, must be 'com_sellaciousreporting.report' to effect
	 *
	 * @return  void
	 *
	 * @throws  \Exception  Bubbles exception from ReportHelper
	 *
	 * @since   1.6.0
	 */
	public function onLoadHandlers($context)
	{
		if ($context == 'com_sellaciousreporting.report')
		{
			/**
			 * We have already registered this class with autoloader in the constructor,
			 * so the component would be able to load this whenever needed
			 */
			ReportHelper::addHandler('cart', '\Sellacious\Report\CartReport');
			ReportHelper::addHandler('seller', '\Sellacious\Report\SellerReport');
		}
	}


	/**
	 * Adds additional fields to the reporting editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		parent::onContentPrepareForm($form, $data);

		if ($form instanceof JForm)
		{
			$name    = $form->getName();

			// Include filters for report creation
			if ($name == 'com_sellaciousreporting.report')
			{
				$data = is_array($data) ? ArrayHelper::toObject($data) : $data;

				if (isset($data->handler))
				{
					$formPath = __DIR__ . '/forms/reportfilters_' . $data->handler . '.xml';

					// Inject import configuration into edit form.
					$form->loadFile($formPath, false);
				}

				if (isset($data->filter))
				{
					$filter = new Registry($data->filter);
					$data->filter = $filter->toArray();
				}
			}
			else if ($name == 'com_sellaciousreporting.sreports.filter')
			{
				// Include filters for the report view
				$handler = JFactory::getApplication()->input->get("reportToBuild", "", "STRING");

				if ($handler)
				{
					$formPath = __DIR__ . '/forms/filters_' . $handler . '.xml';
					$form->loadFile($formPath, false);
				}
			}
		}

		return true;
	}
}
