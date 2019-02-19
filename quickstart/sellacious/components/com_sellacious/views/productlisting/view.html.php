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

/**
 * View class for a list of products.
 */
class SellaciousViewProductListing extends SellaciousViewForm
{
	/**
	 * @var  array
	 */
	protected $items;

	/**
	 * Method to prepare data/view before rendering the display.
	 * Child classes can override this to alter view object before actual display is called.
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function prepareDisplay()
	{
		$items = $this->form->getValue('products', null, array());

		if (count($items) == 0)
		{
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_PRODUCTLISTING_NO_ITEM_SELECTED'), 'warning');

			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=products', false));
		}

		parent::prepareDisplay();
	}

	/**
	 * Get wallet balance of the selected seller uid
	 *
	 * @param   int  $seller_uid  Seller user id
	 *
	 * @return  stdClass[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	public function getBalance($seller_uid)
	{
		return $this->helper->transaction->getBalance($seller_uid);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	public function addToolbar()
	{
		JToolBarHelper::apply('productlisting.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::apply('productlisting.save', 'JTOOLBAR_SAVE');

		JToolBarHelper::cancel('productlisting.cancel', 'JTOOLBAR_CANCEL');
	}
}
