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
 * Controller tailored to suit most form-based admin operations.
 *
 * @package  Sellacious
 * @since    3.0
 */
class SellaciousControllerForm extends JControllerForm
{
	/**
	 * @var  \SellaciousHelper
	 *
	 * @since   1.6.0
	 */
	protected $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since   1.6.0
	 */
	protected $app;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerForm
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app    = JFactory::getApplication();
		$this->helper = SellaciousHelper::getInstance();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name
	 * @param   string  $prefix
	 * @param   array   $config
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.6
	 */
	public function getModel($name = '', $prefix = 'SellaciousModel', $config = null)
	{
		// @todo: see if we really want not to ignore_request
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Method to add a new record.
	 *
	 * @return  boolean  True if the record can be added, false if not.
	 *
	 * @since   12.2
	 */
	public function add()
	{
		if ($add = parent::add())
		{
			$context = "$this->option.edit.$this->context";

			$this->app->setUserState($context . '.id', 0);
		}

		return $add;
	}

	/**
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   12.2
	 */
	public function save($key = null, $urlVar = null)
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		try
		{
			return parent::save($key, $urlVar);
		}
		catch (Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend(null, $urlVar), false));

			return false;
		}
	}

	/**
	 * Get redirect url taking care of all modifiers
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	protected function getRedirectURL()
	{
		$return = $this->input->get('return', null, 'base64');

		if ($return)
		{
			// Should we check for isInternal here?
			return base64_decode($return);
		}

		$label  = $this->input->getString('label', null);
		$tmpl   = $this->input->get('tmpl', null);
		$layout = $this->input->get('layout', null);

		$suffix = strtolower($this->context);

		$suffix .= !empty($label) ? '&label=' . $label : '';
		$suffix .= !empty($tmpl) ? '&tmpl=' . $tmpl : '';
		$suffix .= !empty($layout) ? '&layout=' . $layout : '';

		return JRoute::_('index.php?option=com_sellacious&view=' . $suffix, false);
	}

	/**
	 * Return to referrer url
	 *
	 * @return  string  URL to redirect
	 *
	 * @since   1.1.0
	 */
	protected function getReturnURL()
	{
		$referrer = $this->app->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($referrer))
		{
			$referrer = JRoute::_('index.php?option=' . strtolower($this->name) . '&view=' . strtolower($this->context), false);
		}

		return $referrer;
	}
}
