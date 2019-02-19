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

use Joomla\Registry\Registry;

/**
 * View to edit
 *
 * @property int counter
 */
class SellaciousViewProduct extends SellaciousViewForm
{
	/** @var  string */
	protected $action_prefix = 'product';

	/** @var  string */
	protected $view_item = 'product';

	/** @var  string */
	protected $view_list = 'products';

	/** @var  array */
	protected $variants;

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		try
		{
			$this->state = $this->get('State');
			$this->form  = $this->get('Form');

			if (!$this->form) throw new Exception(implode('<br>', $this->get('Errors')));

			$item           = $this->form->getData();
			$this->item     = $item instanceOf Registry ? $item : new Registry;
			$this->variants = $this->helper->product->getVariants($this->item->get('id'), true);
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		$isNew = ($this->item->get('id') == 0);

		$this->setPageTitle();

		// todo: verify this
		JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');

		if ($this->helper->access->check('product.create'))
		{
			JToolBarHelper::custom('product.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}

		JToolBarHelper::cancel('product.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
