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
 * View to edit Question
 *
 * @since   1.6.0
 */
class SellaciousViewQuestion extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'question';

	/** @var  string */
	protected $view_item = 'question';

	/** @var  string */
	protected $view_list = 'questions';

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function addToolbar()
	{
		$me    = JFactory::getUser();

		$this->setPageTitle();

		// We must get stored seller_uid, not the current session value
		$filter = array(
			'list.select' => 'a.seller_uid',
			'list.from'   => '#__sellacious_product_questions',
			'id'          => $this->item->get('id'),
		);

		$seller_uid = $this->helper->product->loadResult($filter);
		$allowEdit   = $this->helper->access->check($this->action_prefix . '.edit') ||
			($this->helper->access->check($this->action_prefix . '.edit.own') && $seller_uid == $me->id);

		if ($allowEdit)
		{
			JToolBarHelper::save($this->view_item . '.save', 'COM_SELLACIOUS_QUESTION_SEND_BUTTON');
		}

		JToolBarHelper::cancel($this->view_item . '.cancel', 'JTOOLBAR_CLOSE');
	}
}
