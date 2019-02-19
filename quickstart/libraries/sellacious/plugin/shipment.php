<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Shipping\ShippingHandler;

/**
 * Plugin to manage shipment via carrier api integrations for sellacious shops checkout process
 *
 * @subpackage  Sellacious Shipment
 *
 * @since   1.3.3
 */
abstract class SellaciousPluginShipment extends SellaciousPlugin
{
	/**
	 * @var  stdClass  The current shipping rule
	 *
	 * @since   1.3.3
	 */
	protected $rule = null;

	/**
	 * Adds additional fields to the relevant sellacious form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.3.3
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!parent::onContentPrepareForm($form, $data))
		{
			return false;
		}

		if (!$form instanceof JForm)
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$name = $form->getName();

		// No more inject plugin configuration into config form view. Its handled with shipment rules.
		if ($name == 'com_sellacious.shippingrule')
		{
			$my_handlers = array();
			$this->onCollectHandlers('com_sellacious.shipment', $my_handlers);

			$array = is_object($data) ? ArrayHelper::fromObject($data) : (array) $data;

			if (isset($array['method_name']) && array_key_exists($array['method_name'], $my_handlers))
			{
				// Load sys languages as we are going to show config page.
				$lang = JFactory::getLanguage();

				$lang->load($this->pluginName. '.sys', JPATH_ADMINISTRATOR, null, false, true)
				|| $lang->load($this->pluginName. '.sys', $this->pluginPath, null, false, true);

				// Inject plugin configuration into shipment method edit form.
				$form->loadFile($this->pluginPath . '/config.xml', false, '//config');
			}
		}

		return true;
	}

	/**
	 * Returns handlers to the shipment methods that will be managed by this plugin
	 *
	 * @param   string             $context    The calling context, must be 'com_sellacious.shipment' to effect
	 * @param   ShippingHandler[]  &$handlers  ByRef, associative array of handlers
	 *
	 * @return  bool
	 *
	 * @since   1.3.3
	 */
	abstract public function onCollectHandlers($context, array &$handlers);

	/**
	 * Triggers rate request for the given items
	 *
	 * @param   string    $context   The calling context, must be 'com_sellacious.shipment' to effect
	 * @param   object    $rule      The shipment rule object loaded from the database
	 * @param   array     $items     The products for which the rate is to be fetched. Product must include:
	 *                               Product id, weight+unit and dimensions+unit. May include any additional info.
	 * @param   Registry  $shipFrom  The shipper contact and address:
	 *                               Name, Company, Phone, City, State, Country, ZIP, Residential
	 * @param   Registry  $shipTo    The recipient contact and address:
	 *                               Name, Company, Phone, City, State, Country, ZIP, Residential
	 * @param   array     $quotes    The quotes for the item with given shipment parameters. (ByRef) Will be updated.
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.3
	 */
	public function onRequestFreightQuote($context, $rule, $items, $shipFrom, $shipTo, &$quotes)
	{
		return true;
	}

	/**
	 * Returns handlers to the shipment methods that will be managed by this plugin
	 *
	 * @param   string      $context  The calling context, must be 'com_sellacious.shipment' to effect
	 * @param   stdClass    $order    The order for which to fetch the shipment label
	 * @param   stdClass[]  $items    The order items for which to fetch the shipment label
	 * @param   stdClass[]  $labels   Array of shipment label objects byRef, to be appended.
	 *
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function onFetchShipmentLabel($context, $order, $items, &$labels = array())
	{
		// Todo: Decide what more params are needed for this function
		return true;
	}

	/**
	 * Adds plugin form(s) to the sellacious payment params form for user to fil, such as card details etc.
	 *
	 * @param   string    $context      The calling context
	 * @param   JForm     $form         The form to manipulate.
	 * @param   stdClass  $method       Shipment Method record object.
	 * @param   string    $serviceName  Any specific service to load the form for
	 *
	 * @return  bool
	 *
	 * @since   1.3.3
	 */
	public function onLoadShippingForm($context, $form, $method, $serviceName = null)
	{
		// Check for valid contexts.
		if ($context == 'com_sellacious.cart.shippingform' && $method->method_name != '')
		{
			$my_handlers = array();

			$this->onCollectHandlers('com_sellacious.shipment', $my_handlers);

			if (array_key_exists($method->method_name, $my_handlers))
			{
				$xml = $this->getFormXml($method, $serviceName);

				if ($xml instanceof SimpleXMLElement)
				{
					$form->load($xml);
				}
			}
		}

		return true;
	}

	/**
	 * Get the form for selected handler. Some plugins may set up different forms for different context/handlers.
	 *
	 * @param   stdClass  $method   payment method object
	 * @param   string    $context  The Payment Context, i.e. for what the payment is to be made (unused)
	 *
	 * @return  SimpleXMLElement
	 *
	 * @since   1.3.3
	 */
	protected function getFormXml($method, $context = null)
	{
		$path = $this->pluginPath . '/forms/' . $method->method_name . '.xml';
		$xml  = file_exists($path) ? simplexml_load_file($path) : false;

		return $xml;
	}

	/**
	 * The selected shipping rule will be set as active and the state will be updated. Must update this before carrying out any rule logic.
	 *
	 * @param   stdClass  $rule  Explicit shipping rule to load params from.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.3
	 */
	protected function setActiveRule($rule)
	{
		$params = new Registry($rule->params);
		$params = $params->extract($this->pluginName);

		$this->rule   = $rule;
		$this->params = $params ?: new Registry;
	}

	/**
	 * Initialize API configurations with any required token keys or additional settings if any.
	 *
	 * @throws  Exception
	 *
	 * @return  Registry  API settings
	 *
	 * @since   1.3.3
	 */
	protected function getApiContext()
	{
		return new Registry;
	}
}
