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
namespace Sellacious;

defined('_JEXEC') or die;

/**
 * Sellacious Seller Object.
 *
 * @since   1.4.0
 */
class Seller extends BaseObject
{
	/**
	 * @var  int
	 *
	 * @since   1.4.0
	 */
	protected $seller_uid;

	/**
	 * Seller constructor.
	 *
	 * @param   int  $seller_uid
	 *
	 * @throws  \Exception
	 *
	 * @since   1.4.0
	 */
	public function __construct($seller_uid)
	{
		$this->seller_uid = $seller_uid;

		parent::__construct();
	}

	/**
	 * load the relevant information for this seller into object
	 *
	 * @return  void
	 *
	 * @since   1.4.0
	 */
	public function load()
	{
		// Load user account
		$query = $this->dbo->getQuery(true);

		$query->select('u.name, u.username, u.email, u.block')
			->from($this->dbo->qn('#__users', 'u'))
			->where('u.id = ' . (int) $this->seller_uid);

		try
		{
			$user = $this->dbo->setQuery($query)->loadAssoc();

			$this->bind($user);
		}
		catch (\Exception $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING, 'jerror');
		}

		// Load user profile
		$query = $this->dbo->getQuery(true);

		$query->select('su.mobile')
			->from($this->dbo->qn('#__sellacious_profiles', 'su'))
			->where('su.user_id = ' . (int) $this->seller_uid);

		try
		{
			$profile = $this->dbo->setQuery($query)->loadAssoc() ?: array();

			if (empty($profile['currency']) || !$this->helper->config->get('listing_currency'))
			{
				$profile['currency'] = $this->helper->currency->getGlobal('code_3');
			}

			$this->bind($profile);
		}
		catch (\Exception $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING, 'jerror');
		}

		// Load seller profile
		$query = $this->dbo->getQuery(true);

		$query->select('ss.currency, ss.title AS company, ss.store_name AS store, ss.code')
			->from($this->dbo->qn('#__sellacious_sellers', 'ss'))
			->where('ss.user_id = ' . (int) $this->seller_uid);

		try
		{
			$seller = $this->dbo->setQuery($query)->loadAssoc();

			$this->bind($seller);
		}
		catch (\Exception $e)
		{
			\JLog::add($e->getMessage(), \JLog::WARNING, 'jerror');
		}
	}
}
