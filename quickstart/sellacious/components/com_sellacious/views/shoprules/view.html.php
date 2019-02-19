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
 * View class for a list of shoprules.
 */
class SellaciousViewShoprules extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'shoprule';

	/** @var  string */
	protected $view_item = 'shoprule';

	/** @var  string */
	protected $view_list = 'shoprules';

	/** @var  bool */
	protected $is_nested = true;

	/** @var  array */
	protected $types = array();

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareDisplay()
	{
		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		$this->types = $this->helper->shopRule->getTypes();

		parent::prepareDisplay();
	}
}
