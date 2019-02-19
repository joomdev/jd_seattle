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
class SellaciousControllerCompare extends SellaciousControllerBase
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
		$code = $this->input->get('p');

		try
		{
			$limit = (int) $this->helper->config->get('compare_limit', 3);
			$prev  = $this->app->getUserState('com_sellacious.compare.ids', array());

			$this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid);

			if (empty($product_id))
			{
				$all      = $prev;
				$response = array(
					'state'   => 0,
					'message' => JText::_('COM_SELLACIOUS_COMPARE_ADD_NO_ITEM_SELECTED'),
				);
			}
			elseif (!$this->helper->product->isComparable($product_id))
			{
				$all      = $prev;
				$response = array(
					'state'   => 0,
					'message' => JText::_('COM_SELLACIOUS_COMPARE_ADD_NOT_ALLOWED'),
				);
			}
			elseif (count($prev) >= $limit)
			{
				$all      = $prev;
				$response = array(
					'state'   => 0,
					'message' => JText::plural('COM_SELLACIOUS_COMPARE_ADD_LIMIT_EXCEEDED', $limit),
				);
			}
			else
			{
				$all      = in_array($code, $prev) ? $prev : array_merge($prev, array($code));
				$response = array(
					'state'   => 1,
					'message' => JText::_('COM_SELLACIOUS_COMPARE_ADD_SUCCESS'),
				);

				$this->app->setUserState('com_sellacious.compare.ids', $all);
			}

			$response['data'] = array(
				'prev'   => $prev,
				'add'    => $code,
				'output' => $all,
				'html'   => $this->getCompareBarHtml(),
				'free'   => $limit - count($all),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => array(),
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
		$cid   = $this->input->get('p', array(), 'array');
		$prev  = $this->app->getUserState('com_sellacious.compare.ids', array());
		$limit = (int) $this->helper->config->get('compare_limit', 3);

		if (count($cid))
		{
			$new      = array_values(array_diff($prev, $cid));
			$response = array(
				'state'   => 1,
				'message' => JText::_('COM_SELLACIOUS_COMPARE_REMOVE_SUCCESS'),
			);
			$this->app->setUserState('com_sellacious.compare.ids', $new);
		}
		else
		{
			$new      = $prev;
			$response = array(
				'state'   => 0,
				'message' => JText::_('COM_SELLACIOUS_COMPARE_REMOVE_NO_ITEM_SELECTED'),
			);
		}

		try
		{
			$barHtml = $this->getCompareBarHtml();

			$response['data'] = array(
				'prev'   => $prev,
				'remove' => $cid,
				'output' => $new,
				'html'   => $barHtml,
				'free'   => $limit - count($new),
			);
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
			);
		}

		echo json_encode($response);
		jexit();
	}

	/**
	 * Function to remove an item from comparison list
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function remove()
	{
		$cid = $this->input->get('cid', array(), 'array');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=compare', false));

		if (count($cid))
		{
			$prev = $this->app->getUserState('com_sellacious.compare.ids', array());
			$new  = array_diff($prev, $cid);

			$this->app->setUserState('com_sellacious.compare.ids', array_values($new));

			$this->setMessage(JText::_('COM_SELLACIOUS_COMPARE_REMOVE_SUCCESS'), 'success');
		}
		else
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_COMPARE_REMOVE_NO_ITEM_SELECTED'), 'info');
		}

		return true;
	}

	/**
	 * Get the top bar for various pages that displays current compare stack
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function getCompareBarHtml()
	{
		$limit = (int) $this->helper->config->get('compare_limit', 3);
		$codes = $this->app->getUserState('com_sellacious.compare.ids', array());
		$items = array_fill(0, $limit, null);

		ob_start();

		foreach ($codes as $i => $code)
		{
			if ($this->helper->product->parseCode($code, $product_id, $variant_id, $seller_uid))
			{
				$items[$i] = new Sellacious\Product($product_id, $variant_id, $seller_uid);
			}
		}

		return JLayoutHelper::render('com_sellacious.compare.bar', $items, '', array('debug' => 0));
	}
}
