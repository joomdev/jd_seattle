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
use Joomla\Utilities\ArrayHelper;
use Sellacious\Toolbar\Button\StandardButton;
use Sellacious\Toolbar\ButtonGroup;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * View class for a list of products.
 *
 * @since  1.0.0
 */
class SellaciousViewProducts extends SellaciousViewList
{
	/**
	 * @var  string
	 *
	 * @since   1.4.7
	 */
	protected $action_prefix = 'product';

	/**
	 * @var  string
	 *
	 * @since   1.4.7
	 */
	protected $view_item = 'product';

	/**
	 * @var  string
	 *
	 * @since   1.4.7
	 */
	protected $view_list = 'products';

	/**
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	var $languages = array();

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function display($tpl = null)
	{
		$defLanguage = JFactory::getLanguage();
		$tag         = $defLanguage->getTag();
		$languages   = JLanguageHelper::getContentLanguages();

		$this->languages = array_filter($languages, function ($item) use ($tag){
			return ($item->lang_code != $tag);
		});

		return parent::display($tpl);
	}

	/**
	 * Add the page title and the toolbar.
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state = $this->get('State');

		$this->setPageTitle();

		$toolbar = Toolbar::getInstance();

		if ($this->_layout != 'bulk')
		{
			if (file_exists(JPATH_BASE . '/components/com_importer/importer.php'))
			{
				// Todo: Need to be adjusted to call com_importer somehow to use import templates for export format
				// JToolBarHelper::custom($this->view_list . '.export', 'export', 'export', 'COM_SELLACIOUS_PRODUCTS_EXPORT_PRODUCTS', false);
			}

			if ($this->helper->access->check('product.create'))
			{
				JToolBarHelper::addNew('product.add', 'JTOOLBAR_NEW');
			}
		}

		if (count($this->items))
		{
			if ($this->_layout == 'bulk')
			{
				if (!$this->helper->access->isSubscribed())
				{
					if ($this->helper->core->getLicense('free_forever'))
					{
						$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_PREMIUM_FEATURE_NOTICE_INVENTORY_MANAGER'), 'premium');
					}
					else
					{
						$this->app->enqueueMessage(JText::_('COM_SELLACIOUS_PREMIUM_FEATURE_NOTICE_INVENTORY_MANAGER_TRIAL'), 'premium');
					}
				}
				elseif ($this->helper->access->checkAny(array('pricing', 'seller', 'pricing.own', 'seller.own'), 'product.edit.'))
				{
					JToolBarHelper::addNew('products.save', 'JTOOLBAR_APPLY', true);

					if ($this->helper->config->get('multi_variant'))
					{
						JToolBarHelper::custom('variants.manage', 'edit', 'edit', 'COM_SELLACIOUS_PRODUCTS_VARIANTS_BUTTON', true);
					}
				}
			}
			else
			{
				$gState = new ButtonGroup('state', 'COM_SELLACIOUS_BUTTON_GROUP_BULK_OPTIONS');
				$toolbar->appendGroup($gState);

				if ($this->helper->access->check('product.create'))
				{
					$gState->appendButton(new StandardButton('copy', 'COM_SELLACIOUS_PRODUCT_DUPLICATE_BUTTON', $this->view_list . '.duplicate', true));
				}

				if ($this->helper->access->checkAny(array('seller', 'seller.own'), 'product.edit.'))
				{
					$gState->appendButton(new StandardButton('publish', 'COM_SELLACIOUS_PRODUCTS_SELLING_ENABLE', $this->view_list . '.setSelling', true));
					$gState->appendButton(new StandardButton('unpublish', 'COM_SELLACIOUS_PRODUCTS_SELLING_DISABLE', $this->view_list . '.setNotSelling', true));
				}

				$filter_state = $state->get('filter.state');

				if ($this->helper->access->check('product.edit.state'))
				{
					if (!is_numeric($filter_state) || $filter_state != 1)
					{
						$gState->appendButton(new StandardButton('publish', 'JTOOLBAR_PUBLISH', $this->view_list . '.publish', true));

					}

					if (!is_numeric($filter_state) || $filter_state != 0)
					{
						$gState->appendButton(new StandardButton('unpublish', 'JTOOLBAR_UNPUBLISH', $this->view_list . '.unpublish', true));

					}

					if (!is_numeric($filter_state) || $filter_state != -2)
					{
						$gState->appendButton(new StandardButton('trash', 'JTOOLBAR_TRASH', $this->view_list . '.trash', true));

					}
					// If 'edit.state' is granted, then show 'delete' only if filtered on 'trashed' items
					elseif ($this->helper->access->checkAny(array('product.delete', 'product.delete.own')))
					{
						JToolBarHelper::deleteList('', 'products.delete', 'JTOOLBAR_DELETE');
					}
				}
				// We can allow direct 'delete' implicitly for his (seller) own items if so permitted.
				elseif ($this->helper->access->checkAny(array('product.delete', 'product.delete.own')))
				{
					JToolBarHelper::trash('products.delete', 'JTOOLBAR_DELETE');
				}

				// Approve/Disapprove product from pending approval state
				if ($this->helper->access->check('product.approve'))
				{
					if (!is_numeric($filter_state) || $filter_state != -1)
					{
						// Check for Global config
						if($this->helper->config->get('seller_product_approve', 0))
						{
							JToolBarHelper::custom('products.pending', 'pending.png', 'pending_f2.png', 'COM_SELLACIOUS_PRODUCTS_PENDING_APPROVAL_BUTTON', true);
						}
					}

					if (!is_numeric($filter_state) || $filter_state != -3)
					{
						// Check for Global config
						if($this->helper->config->get('seller_product_approve', 0))
						{
							JToolBarHelper::custom('products.disapprove', 'not-ok.png', 'not-ok_f2.png', 'COM_SELLACIOUS_PRODUCTS_DISAPPROVED_BUTTON', true);
						}
					}
				}

				// Todo: verify permissions usage for this
				if ($this->helper->listing->isApplicable())
				{
					JToolBarHelper::custom('products.listing', 'edit.png', 'edit.png', 'COM_SELLACIOUS_PRODUCTS_LISTING_BUTTON', true);
				}
			}
		}
	}

	/**
	 * Get a form object for the bulk editor view
	 *
	 * @param   int  $index  The repeat index
	 *
	 * @return  JForm
	 *
	 * @since   1.0.0
	 */
	public function getRepeatableForm($index)
	{
		$form = JForm::getInstance('com_sellacious.products.row', 'product_bulk_price', array('control' => 'jform'));

		if ($form instanceof JForm)
		{
			$me   = JFactory::getUser();
			$item = ArrayHelper::getValue($this->items, $index);

			$form->repeatCounter = $index;

			if ($this->helper->config->get('pricing_model') == 'flat')
			{
				$form->setFieldAttribute('price', 'mode', 'flat');
			}

			if (!($this->helper->access->check('product.edit.pricing') ||
				($this->helper->access->check('product.edit.pricing.own') && $item->seller_uid == $me->id)))
			{
				$form->setFieldAttribute('price', 'readonly', 'true');
			}

			if (!($this->helper->access->check('product.edit.seller') ||
				($this->helper->access->check('product.edit.seller.own') && $item->seller_uid == $me->id)))
			{
				$form->setFieldAttribute('stock', 'readonly', 'true');
			}

			if (!$this->helper->access->isSubscribed())
			{
				$form->setFieldAttribute('price', 'readonly', 'true');
				$form->setFieldAttribute('stock', 'readonly', 'true');
			}

			$data             = new stdClass;
			$data->seller_uid = $item->seller_uid;
			$data->stock      = $item->stock;
			$data->price      = array(
				'id'               => $item->price_id,
				'cost_price'       => $item->cost_price,
				'margin'           => $item->margin,
				'margin_type'      => $item->margin_type,
				'list_price'       => $item->list_price,
				'calculated_price' => $item->calculated_price,
				'ovr_price'        => $item->ovr_price,
			);

			$form->bind($data);
		}

		return $form;
	}
}
