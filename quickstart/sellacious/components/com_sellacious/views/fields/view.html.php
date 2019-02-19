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
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * View class for a list of Sellacious.
 */
class SellaciousViewFields extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'field';

	/** @var  string */
	protected $view_item = 'field';

	/** @var  string */
	protected $view_list = 'fields';

	/** @var  bool */
	protected $is_nested = true;

	/**
	 * Method to preprocess data before rendering the display.
	 *
	 * @return  void
	 */
	protected function prepareDisplay()
	{
		if ($this->filterForm instanceof JForm)
		{
			// Update context for filter group but not for batch group.
			$context = $this->state->get('filter.context', '');
			$this->filterForm->setFieldAttribute('fieldgroup', 'context', $context, 'filter');
		}

		foreach ($this->items as $item)
		{
			$this->ordering[$item->parent_id][] = $item->id;
		}

		parent::prepareDisplay();
	}

	/**
	 * Add the page title and the toolbar.
	 *
	 * @since   1.6.0
	 */
	protected function addToolbar()
	{
		parent::addToolbar();

		$toolbar = Toolbar::getInstance();
		$group   = $toolbar->getGroup('state');
		$context = $this->state->get('filter.context');

		if (count($this->items) && $this->helper->access->check('product.edit'))
		{
			$group->appendButton(new StandardButton('require', 'COM_SELLACIOUS_FIELD_REQUIRED_BUTTON', $this->view_list . '.setRequired', true));
			$group->appendButton(new StandardButton('notrequire', 'COM_SELLACIOUS_FIELD_NOT_REQUIRED_BUTTON', $this->view_list . '.setNotRequired', true));

			if ($context == 'product')
			{
				$group->appendButton(new StandardButton('filterable', 'COM_SELLACIOUS_FIELD_FILTERABLE_BUTTON', $this->view_list . '.setFilterable', true));
				$group->appendButton(new StandardButton('notfilterable', 'COM_SELLACIOUS_FIELD_NOT_FILTERABLE_BUTTON', $this->view_list . '.setNotFilterable', true));
			}
		}
	}
}
