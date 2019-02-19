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

/**
 * ShopRules list controller class.
 */
class SellaciousControllerShippingRules extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_SHIPPINGRULES';

	/**
	 * Proxy for getModel.
	 *
	 * @param  string $name
	 * @param  string $prefix
	 * @param  array  $config
	 *
	 * @return  JModelLegacy
	 */
	public function getModel($name = 'ShippingRule', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=shippingrules', false));

		if (!$this->helper->access->check('shippingrule.rebuild'))
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		/** @var SellaciousModelShippingRule $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_SUCCESS'));
		}
		else
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_REBUILD_FAILURE', $model->getError()), 'error');

			return false;
		}

		return true;
	}

}
