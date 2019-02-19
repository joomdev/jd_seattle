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
 * Base class for a Sellacious Administrator Controller
 *
 * @package  Sellacious
 *
 * @since    3.0
 */
class SellaciousControllerAdmin extends JControllerAdmin
{
	/**
	 * @var   SellaciousHelper
	 *
	 * @since   1.1.0
	 */
	protected $helper;

	/**
	 * @var  \JApplicationCms
	 *
	 * @since   1.6.0
	 */
	protected $app;

	/**
	 * @var   string
	 *
	 * @since   1.1.0
	 */
	protected $context;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerAdmin
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->app     = JFactory::getApplication();
		$this->helper  = SellaciousHelper::getInstance();
		$this->context = substr(get_class($this), strlen($this->name . 'Controller'));
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   12.2
	 */
	public function getModel($name = '', $prefix = 'SellaciousModel', $config = null)
	{
		if ($name == '')
		{
			$name = strtolower($this->context);
		}

		// Todo: see if we really want not to ignore_request
		return parent::getModel($name, $prefix, array('ignore_request' => false));
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
		$referrer = $this->input->server->getString('HTTP_REFERER');

		if (!JUri::isInternal($referrer))
		{
			$referrer = JRoute::_('index.php?option=com_sellacious&view=' . strtolower($this->context), false);
		}

		return $referrer;
	}
}
