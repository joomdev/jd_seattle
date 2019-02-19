<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Sellacious\Config\ConfigHelper;

/**
 * Sellacious configuration helper.
 *
 * @since  1.0.0
 *
 * @deprecated   Use Sellacious\Config\ConfigHelper instead
 */
class SellaciousHelperConfig extends SellaciousHelperBase
{
	/**
	 * Load single parameter value from configuration
	 *
	 * @param   string  $name        Name of the parameter to load
	 * @param   mixed   $default     Default value if not found
	 * @param   string  $context     Name of the extension
	 * @param   string  $subcontext  The additional subcontext identifier
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use Sellacious\Config\Config instead
	 */
	public function get($name, $default = null, $context = 'com_sellacious', $subcontext = 'core')
	{
		try
		{
			$config = ConfigHelper::getInstance($context, $subcontext);

			return $config->get($name, $default);
		}
		catch (Exception $e)
		{
			return $default;
		}
	}

	/**
	 * Method to save extension configuration for sellacious and other relevant extensions *keeping any existing* intact
	 *
	 * @param   string  $name        Name of the parameter to modify
	 * @param   mixed   $value       Value to be set for the parameter
	 * @param   string  $context     The extension element name
	 * @param   string  $subcontext  The additional subcontext identifier
	 *
	 * @return  bool  Success status
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use Sellacious\Config\Config instead
	 */
	public function set($name, $value = null, $context = 'com_sellacious', $subcontext = 'core')
	{
		$config = ConfigHelper::getInstance($context, $subcontext);

		$config->set($name, $value);

		return $config->store();
	}

	/**
	 * Method to save extension configuration for sellacious and other relevant extensions
	 *
	 * @param   array   $params      The new configuration values
	 * @param   string  $context     The extension element name
	 * @param   string  $subcontext  The additional subcontext identifier
	 *
	 * @return  bool  Success status
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use Sellacious\Config\Config instead
	 */
	public function save($params, $context, $subcontext = 'core')
	{
		$config = ConfigHelper::getInstance($context, $subcontext);

		$config->clear();
		$config->bind($params);

		return $config->store();
	}

	/**
	 * Method to load configuration for given configuration key
	 *
	 * @param   string  $context     The named key of the configuration
	 * @param   string  $subcontext  The additional subcontext identifier
	 *
	 * @return  Registry
	 *
	 * @since   1.0.0
	 *
	 * @deprecated   Use Sellacious\Config\Config instead
	 */
	public function getParams($context = 'com_sellacious', $subcontext = 'core')
	{
		try
		{
			$config = ConfigHelper::getInstance($context, $subcontext);

			return $config->getParams();
		}
		catch (Exception $e)
		{
			// Todo: Remove B/C and handle this exception
			return new Registry;
		}
	}

	/**
	 * Method to load favicon for sellacious back office only for Premium
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	public function getFaviconPremium()
	{
		$fk     = array(
			'list.select'=> 'a.path',
			'table_name' => 'config',
			'context'    => 'backoffice_favicon',
			'record_id'  => 1,
			'state'      => 1,
		);

		return $this->helper->media->loadResult($fk);

	}
}
