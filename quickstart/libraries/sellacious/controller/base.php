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

/**
 * Base class for a Sellacious Controller
 *
 * @package  Sellacious
 *
 * @since    1.0.0
 */
class SellaciousControllerBase extends JControllerLegacy
{
	/**
	 * @var  SellaciousHelper
	 *
	 * @since  1.0.0
	 */
	protected $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since  1.6.0
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws  Exception
	 *
	 * @see     JControllerLegacy
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Return to referrer url
	 *
	 * @return  string  URL to redirect
	 *
	 * @since   1.6.0
	 */
	protected function getReturnURL()
	{
		$referrer = $this->app->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($referrer))
		{
			$referrer = JRoute::_('index.php?option=' . strtolower($this->name), false);
		}

		return $referrer;
	}
}
