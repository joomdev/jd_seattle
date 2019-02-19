<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access

defined('_JEXEC') or die;

/**
 * Product controller class.
 */
class SellaciousControllerWishlist extends SellaciousControllerBase
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_COMPARE';

	/**
	 * Function to add an item to compare queue via Ajax
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function addAjax()
	{
		$code  = $this->input->get('p');
		$valid = $this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

		try
		{
			if (!$valid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_WISHLIST_ADD_INVALID_ITEM'));
			}

			$added = $this->helper->wishlist->addItem($product_id, $variant_id, $seller_uid, null);

			if (!$added)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_WISHLIST_ADD_FAILED'));
			}

			$response = array(
				'state'   => 1,
				'message' => JText::_('COM_SELLACIOUS_WISHLIST_ADD_SUCCESS'),
				'data'    => array(
					'code'     => $code,
					'redirect' => JRoute::_('index.php?option=com_sellacious&view=wishlist', false),
				),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => array('code' => $code),
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Function to remove an item from comparison list via ajax call
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function removeAjax()
	{
		$code  = $this->input->get('p');
		$valid = $this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

		try
		{
			if (!$valid)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_WISHLIST_REMOVE_INVALID_ITEM'));
			}

			$removed = $this->helper->wishlist->removeItem($product_id, $variant_id, $seller_uid, null);

			if (!$removed)
			{
				throw new Exception(JText::_('COM_SELLACIOUS_WISHLIST_REMOVE_FAILED'));
			}

			$response = array(
				'state'   => 1,
				'message' => JText::_('COM_SELLACIOUS_WISHLIST_REMOVE_SUCCESS'),
				'data'    => array('code' => $code),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => array('code' => $code),
			);
		}

		echo json_encode($response);
		jexit();
	}
}
