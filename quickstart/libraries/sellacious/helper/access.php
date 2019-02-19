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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Config\ConfigHelper;

/**
 * Sellacious access helper.
 *
 * @since  1.0
 */
class SellaciousHelperAccess extends SellaciousHelperBase
{
	/**
	 * @var  bool
	 *
	 * @since   1.1.0
	 */
	protected $hasTable = false;

	/**
	 * Check access of current user
	 *
	 * @param   string  $action     Action to be checked for
	 * @param   mixed   $key        Asset reference identifier such as id
	 * @param   string  $assetName  Asset name for this the access is required
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function check($action = '', $key = null, $assetName = 'com_sellacious')
	{
		$me = JFactory::getUser();

		return $me->authorise($action, $assetName) || $me->authorise('app.admin', 'com_sellacious');
	}

	/**
	 * Check access of current user for at least one action in a set of actions
	 *
	 * @param   string[]  $actions  Actions/asset to be accessed
	 * @param   string    $prefix   Actions/asset to be accessed may be grouped if they have same prefix asset name ('product.' + 'edit')
	 * @param   mixed     $key      Asset reference identifier such as id
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function checkAny($actions, $prefix = '', $key = '')
	{
		if (count($actions))
		{
			foreach ($actions as $action)
			{
				if ($this->check($prefix . $action, $key))
				{
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check access of current user for all actions in a set of actions
	 *
	 * @param   string[]  $actions  Actions/asset to be accessed
	 * @param   string    $prefix   Actions/asset to be accessed may be grouped if they have same prefix asset name ('product.' + 'edit')
	 * @param   mixed     $key      Asset reference identifier such as id
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function checkAll($actions, $prefix = '', $key = '')
	{
		if (count($actions))
		{
			foreach ($actions as $action)
			{
				if (!$this->check($prefix . $action, $key))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to return a list of actions from the component's access.xml file for which permissions can be set.
	 *
	 * @param   string  $component  The component name to load access from
	 * @param   string  $section    The access section name
	 *
	 * @return  stdClass[]  The list of actions available
	 *
	 * @since   1.0.0
	 */
	public function getActions($component = 'com_sellacious', $section = 'component')
	{
		$groups  = $this->getActionGroups($component, $section);
		$actions = ArrayHelper::getColumn($groups, 'actions');

		return array_reduce($actions, 'array_merge', array());
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string  $component  The component name to load access from
	 * @param   string  $section    The access section name
	 *
	 * @return  stdClass[]  The list of actions available.
	 *
	 * @since   12.1
	 */
	public function getActionGroups($component = 'com_sellacious', $section = 'component')
	{
		// Get the actions for the asset.
		$groups = array();

		if (!$component)
		{
			// Global configurations
			$file = JPath::clean(JPATH_SELLACIOUS . '/access.xml');
		}
		else
		{
			// Component configurations
			$file = JPath::clean(JPATH_SELLACIOUS . '/components/' . $component . '/access.xml');
		}

		if (is_file($file) && is_readable($file))
		{
			$xml = simplexml_load_file($file);
		}

		if (!isset($xml) || !($xml instanceof SimpleXMLElement))
		{
			return array();
		}

		$xpath  = $section ? "/access/section[@name='$section']" : '/access/section';

		// Fetch flat actions
		$xml_actions_general = $xml->xpath("$xpath/action[@name][@title][@description]");

		if (!empty($xml_actions_general))
		{
			$groups['0'] = (object) array(
				'name'    => '',
				'title'   => '',
				'actions' => array(),
			);

			foreach ($xml_actions_general as $xml_action)
			{
				$a_name = (string) $xml_action['name'];

				$groups['0']->actions[$a_name] = (object) array(
					'name'        => $a_name,
					'title'       => (string) $xml_action['title'],
					'description' => (string) $xml_action['description'],
				);
			}
		}

		// Fetch grouped actions
		$xml_actions_groups = $xml->xpath("$xpath/actions[@name]");

		if (!empty($xml_actions_groups))
		{
			foreach ($xml_actions_groups as $xml_actions_group)
			{
				$name  = (string) $xml_actions_group['name'];
				$title = (string) $xml_actions_group['title'];

				if (!isset($groups[$name]))
				{
					$groups[$name] = (object) array(
						'name'    => $name,
						'title'   => $title,
						'actions' => array(),
					);
				}
				elseif ($title)
				{
					$groups[$name]['title'] = $title;
				}

				$xml_actions = $xml_actions_group->xpath('action[@name][@title][@description]');

				if (!empty($xml_actions))
				{
					foreach ($xml_actions as $xml_action)
					{
						$a_name = (string) $xml_action['name'];

						$groups[$name]->actions[$a_name] = (object) array(
							'name'        => $a_name,
							'title'       => (string) $xml_action['title'],
							'description' => (string) $xml_action['description'],
						);
					}
				}
			}
		}

		// Finally return the actions array
		return $groups;
	}

	/**
	 * Normalise the URL by ignoring common variations of the same URL
	 *
	 * @param   string  $url      The URL to be normalised
	 * @param   bool    &$secure  ByRef param updated with the respective value as: https = true, http = false
	 *
	 * @return  string
	 *
	 * @since   1.4.6
	 */
	public static function normaliseUrl($url, &$secure = null)
	{
		$uri  = JUri::getInstance($url);

		// Ignore 'www.' generic sub-domain and http: or https:
		$secure = $uri->isSSL();
		$host   = str_replace('www.', '', $uri->getHost());
		$uri->setHost($host);

		// Calculate normalised url
		$url = trim($uri->toString(array('host', 'port', 'path')), '\\/');

		return $url;
	}

	/**
	 * Check access related to media upload / download / crop etc. depending on the requested context
	 *
	 * @param   string  $table
	 * @param   string  $context
	 * @param   int     $record_id
	 * @param   string  $action     Action to be performed for which access is queried
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function checkMediaAccess($table, $context, $record_id, $action)
	{
		// Fixme: Currently we are using only basic check using list of predefined contexts.
		// Fixme: Update this to allow for dynamic contexts
		$me = JFactory::getUser();

		switch ("$table.$context")
		{
			case 'config.shop_logo':
			case 'config.purchase_return_icon':
			case 'config.purchase_exchange_icon':
			case 'config.eproduct_image_watermark':
				$allow = $this->check('config.edit');
				break;

			case 'config.backoffice_logo':
			case 'config.backoffice_logoicon':
			case 'config.backoffice_favicon':
				$allow = $this->check('config.edit') && $this->isSubscribed();
				break;

			case 'eproduct_media.media':
			case 'eproduct_media.sample':
				$table = $this->getTable('EProductMedia');
				$table->load($record_id);
				$isOwner = ($me->id > 0) && $this->helper->product->getFieldValue($table->get('product_id'), 'owned_by') == $me->id;
				$allow   = $this->check('product.edit.basic') || ($isOwner && $this->check('product.edit.basic.own'));
				break;

			case 'variants.images':
				$isOwner = ($me->id > 0) && $this->helper->variant->getFieldValue($record_id, 'owned_by') == $me->id;
				$allow   = $this->check('variant.edit') || ($isOwner && $this->check('variant.edit.own'));
				break;

			default:
				$allow = false;
		}

		return $allow;
	}

	/**
	 * Check whether this copy of sellacious has an active subscription
	 *
	 * @return  bool
	 *
	 * @since   1.3.0
	 */
	public function isSubscribed()
	{
		if (!isset($this->cache[__METHOD__]))
		{
			$cDate = JFactory::getDate();
			$cUri  = trim(JUri::root(), '\\/');
			$eDate = $this->helper->config->get('license.expiry_date', null, 'sellacious', 'application');
			$url   = $this->helper->config->get('license.siteurl', null, 'sellacious', 'application');
			$value = $eDate && ($cDate < JFactory::getDate($eDate)) && ($this->normaliseUrl($url) == $this->normaliseUrl($cUri));

			$this->cache[__METHOD__] =  $value;
		}

		return $this->cache[__METHOD__];
	}

	/**
	 * Get a support PIN for this site from sellacious.com
	 *
	 * @param   bool  $new  Force a new PIN to be generated
	 *
	 * @return  string[]   An array containing [PIN, Key, Created DateTime, Validity (seconds)]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getSupportPIN($new = false)
	{
		$app = JFactory::getApplication();

		// If we can return an existing PIN?
		if (!$new)
		{
			$pin = $app->getUserState('application.sellacious.support.pin');
			$key = $app->getUserState('application.sellacious.support.key');

			if ($pin)
			{
				$exp = $app->getUserState('application.sellacious.support.expires');
				$exp = JFactory::getDate($exp);
				$now = JFactory::getDate();

				// We have a PIN that is not expired
				if ($now->toUnix() < $exp->toUnix())
				{
					return array($pin, $key);
				}
			}
		}

		$sitekey    = $this->helper->core->getLicense('sitekey');
		$jarvisSite = $this->helper->core->getJarvisSite();

		// @Todo: This sitekey needs to be hashed
		$http = JHttpFactory::getHttp();
		$post = array('sitekey' => $sitekey, 'siteurl' => JUri::root());
		$resp = $http->post($jarvisSite . '/index.php?option=com_jarvis&task=site.getPin&format=json', $post);

		if ($resp->code != 200)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_LICENSING'));
		}

		$response = new Registry($resp->body);

		if ($response->get('status') != 1)
		{
			throw new Exception($response->get('message', JText::_('COM_SELLACIOUS_ERROR_LICENSING')));
		}

		$pin = $response->get('data.pin');
		$key = $response->get('data.key');
		$tcr = $response->get('data.created');
		$sec = $response->get('data.valid');
		$exp = JFactory::getDate($tcr)->modify(sprintf('+%d seconds', $sec));

		$app->setUserState('application.sellacious.support.pin', $pin);
		$app->setUserState('application.sellacious.support.key', $key);
		$app->setUserState('application.sellacious.support.created', $tcr);
		$app->setUserState('application.sellacious.support.expires', $exp);

		return array($pin, $key);
	}

	/**
	 * Method to retrieve license information from Jarvis
	 *
	 * @param   string    $sitekey
	 * @param   Registry  $registry
	 *
	 * @return  Registry
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function fetchLicense($sitekey, $registry)
	{
		$app      = JFactory::getApplication();
		$tpl      = array('list.select' => 'a.template, a.title', 'list.from' => '#__template_styles', 'client_id' => '0', 'home' => 1);
		$style    = $this->helper->config->loadObject($tpl);
		$template = is_object($style) ? sprintf('%s (%s)', $style->template, $style->title) : 'NA';

		// Todo: This sitekey should be hashed
		$registry->set('sitekey', $sitekey);
		$registry->set('sitename', $app->get('sitename'));
		$registry->set('siteurl', trim(JUri::root(), '\\/'));
		$registry->set('version', S_VERSION_CORE);
		$registry->set('site_template', $template);

		$jarvisSite = $this->helper->core->getJarvisSite();

		$http = JHttpFactory::getHttp();
		$resp = $http->post($jarvisSite . '/index.php?option=com_jarvis&task=site.validate&format=json', $registry->toArray());

		if ($resp->code != 200)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ACTIVATION_COMMUNICATION_FAILURE'));
		}

		$response = new Registry($resp->body);

		return $response;
	}

	/**
	 * Update the license information into the database as obtained from the sellacious license server
	 *
	 * @param   Registry  $license   License data obtained from license server
	 * @param   Registry  $registry  Site metadata for the license
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function updateLicense($license, $registry)
	{
		if (!$license)
		{
			return false;
		}

		$config = ConfigHelper::getInstance('sellacious', 'application');

		$license->set('sitekey', $registry->get('sitekey'));
		$license->set('sitename', $registry->get('sitename'));
		$license->set('siteurl', $registry->get('siteurl'));
		$license->set('version', $registry->get('version'));
		$license->set('ts', JFactory::getDate()->toUnix());

		if ($license->get('expiry_iso') || $config->get('license.free_forever'))
		{
			$license->set('free_forever', true);
		}

		$config->set('license', $license->toArray());

		return $config->store();
	}
}
