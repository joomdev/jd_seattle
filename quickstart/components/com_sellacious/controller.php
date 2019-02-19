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

class SellaciousController extends SellaciousControllerBase
{
	/**
	 * Method to display a view.
	 *
	 * @param   bool   $cacheable  If true, the view output will be cached
	 * @param   mixed  $urlparams  An array of safe url parameters and their variable types, for valid values.
	 *
	 * @see     JFilterInput::clean()
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cacheable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'categories');
		$view = $view == 'sellacious' ? 'categories' : $view;

		$this->input->set('view', $view);

		if (!$this->canView())
		{
			$tmpl   = $this->input->get('tmpl', null);
			$suffix = !empty($tmpl) ? '&tmpl=' . $tmpl : '';
			$return = JRoute::_('index.php?option=com_sellacious' . $suffix, false);

			if ($tmpl != 'raw')
			{
				$this->setRedirect($return);

				JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), JLog::WARNING, 'jerror');
			}

			return $this;
		}

		return parent::display($cacheable, $urlparams);
	}

	/**
	 * Checks whether a user can see this view.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	protected function canView()
	{
		$view   = $this->input->get('view', 'products');
		$layout = $this->input->get('layout');
		$id     = $this->input->getInt('id', null);

		if ($layout == 'edit')
		{
			$editId = (int) $this->app->getUserState('com_sellacious.edit.' . $view . '.id');

			if ($id === null)
			{
				// We should allow default edit id if not set in request uri
				$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=' . $view . '&layout=edit&id=' . $editId, false));
			}

			if ($editId != $id)
			{
				// Somehow the person just went to the form - we don't allow that.
				// But instead of stopping him just switch the context
				// if already editing something else, clear it from session to prevent data in new form
				$this->app->setUserState('com_sellacious.edit.' . $view . '.id', $id);
				$this->app->setUserState('com_sellacious.edit.' . $view . '.data', null);
				$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=' . $view . '&layout=edit&id=' . $id, false));
			}
		}

		// TODO: Devise a logic to know the allowed views based on the access rules set
		// $allowed = 'products,product,categories,compare,cart,orders';
		// $allowed = in_array($view, explode(',', $allowed));
		$allowed = strlen($view) && is_dir(__DIR__ . '/views/' . $view);

		return $allowed;
	}

	/**
	 * Method to log out a user.
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function logout()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$user_id = $this->input->getInt('uid', null);

		$options = array(
			'clientid' => $this->app->getClientId(),
		);

		$result = $this->app->logout($user_id, $options);

		if (!($result instanceof Exception))
		{
			$return = JRoute::_('index.php');

			$this->app->redirect($return);
		}

		parent::display();
	}

	/**
	 * Set default currency for current session
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function setCurrencyAjax()
	{
		$code = $this->app->input->get('c');

		try
		{
			$currency = $this->helper->currency->getItem($code);

			if ($currency->state == 1)
			{
				$this->app->setUserState('com_sellacious.currency.current', $currency->code_3);

				$response = array(
					'state'   => 1,
					'message' => JText::_('COM_SELLACIOUS_CURRENCY_SELECTION_SET_PREFERENCE_SUCCESS'),
					'data'    => null,
				);
			}
			else
			{
				throw new Exception('COM_SELLACIOUS_CURRENCY_INVALID_SELECTION');
			}
		}
		catch (Exception $e)
		{
			$response = array(
				'state'   => 0,
				'message' => $e->getMessage(),
				'data'    => null,
			);
		}

		echo json_encode($response); jexit();
	}
}
