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
 * View class for a list of product variants.
 */
class SellaciousViewVariants extends SellaciousViewList
{
	/**
	 * Display the view
	 *
	 * @param   string $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		if (empty($this->state))
		{
			$this->state = $this->get('State');
		}

		if (empty($this->items))
		{
			$this->items = $this->get('Items');
		}

		$this->pagination    = false;
		$this->filterForm    = false;
		$this->activeFilters = false;

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	public function addToolbar()
	{
		if (!$this->helper->access->isSubscribed())
		{
			$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_PREMIUM_FEATURE_NOTICE_INVENTORY_MANAGER'), 'premium');
		}
		elseif ($this->helper->access->checkAny(array('pricing', 'seller', 'pricing.own', 'seller.own'), 'product.edit.'))
		{
			JToolBarHelper::apply('variants.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('variants.save', 'JTOOLBAR_SAVE');
		}

		JToolBarHelper::cancel('variants.cancel', 'JTOOLBAR_CANCEL');
	}
}
