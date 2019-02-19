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
 * View to edit
 */
class SellaciousViewCoupon extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'coupon';

	/** @var  string */
	protected $view_item = 'coupon';

	/** @var  string */
	protected $view_list = 'coupons';

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6
	 */
	protected function addToolbar()
	{
		$me    = JFactory::getUser();
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		// We must get stored seller_uid, not the current session value
		$seller_uid  = $this->helper->coupon->getFieldValue($this->item->get('id'), 'seller_uid');
		$allowCreate = $this->helper->access->check($this->action_prefix . '.create');
		$allowEdit   = $this->helper->access->check($this->action_prefix . '.edit') ||
			($this->helper->access->check($this->action_prefix . '.edit.own') && $seller_uid == $me->id);

		if ($isNew ? $allowCreate : $allowEdit)
		{
			JToolBarHelper::apply($this->view_item . '.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save($this->view_item . '.save', 'JTOOLBAR_SAVE');

			if ($allowCreate)
			{
				JToolBarHelper::custom($this->view_item . '.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);

				if (!$isNew)
				{
					JToolBarHelper::custom($this->view_item . '.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
				}
			}
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
