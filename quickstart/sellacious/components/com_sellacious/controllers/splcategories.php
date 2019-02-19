<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * SplCategories list controller class.
 */
class SellaciousControllerSplcategories extends SellaciousControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_SPLCATEGORIES';

	/**
	 * Proxy for getModel.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'SplCategory', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return	bool	False on failure or error, true on success.
	 * @since	1.6
	 */
	public function rebuild()
	{
		$allowed = $this->helper->access->check('splcategory.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=splcategories', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		/** @var  \SellaciousModelSplCategory  $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix.'_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(JText::sprintf($this->text_prefix.'_REBUILD_FAILURE', $model->getError()), 'error');

			return false;
		}
	}

	/**
	 * Method to revoke active subscriptions from selected categories
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function revokeActiveSubscriptions()
	{
		$allowed = $this->helper->access->check('splcategories.edit.state');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=splcategories', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		$cid = $this->input->get('cid', 'array', array());
		$cid = ArrayHelper::toInteger($cid);

		if (count($cid) == 0)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_SPLCATEGORIES_NO_ITEM_SELECTED'), 'warning');

			return false;
		}

		try
		{
			$this->helper->listing->deactivate($cid);

			$this->setMessage(JText::_($this->text_prefix . '_REVOKE_ACTIVE_SUBSCRIPTIONS_SUCCESS'));

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_REVOKE_ACTIVE_SUBSCRIPTIONS_FAILURE', $e->getMessage()), 'error');

			return false;
		}
	}
}
