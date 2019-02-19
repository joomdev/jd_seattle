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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Categories list controller class
 *
 * @since   1.0.0
 */
class SellaciousControllerCategories extends SellaciousControllerAdmin
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since   1.0.0
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CATEGORIES';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Category', $prefix = 'SellaciousModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.0.0
	 */
	public function rebuild()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$allowed = $this->helper->access->check('category.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=categories', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		/** @var  SellaciousModelCategory  $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(JText::sprintf($this->text_prefix . '_REBUILD_FAILURE', $model->getError()), 'error');

			return false;
		}
	}

	/**
	 * Method to set the default category for a category type.
	 *
	 * @since   1.0.0
	 */
	public function setDefault()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = $this->input->post->get('cid', array(), 'array');

		$this->setRedirect('index.php?option=com_sellacious&view=categories');

		try
		{
			$pks = ArrayHelper::toInteger($pks);

			if (empty($pks))
			{
				throw new Exception(500, JText::_('COM_SELLACIOUS_CATEGORIES_NO_ITEM_SELECTED'));
			}

			// Pop off the first element.
			$id    = array_shift($pks);
			$model = $this->getModel();

			$model->setDefault($id);

			$this->setMessage(JText::_('COM_SELLACIOUS_CATEGORIES_DEFAULT_SET'));
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}

	/**
	 * Method to set the default category for a category type.
	 *
	 * @return   bool
	 *
	 * @since    1.5.2
	 */
	public function syncMenus()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$allowed = $this->helper->access->check('category.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=categories', false));

		if (!$allowed)
		{
			$this->setMessage(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'), 'error');

			return false;
		}

		try
		{
			$this->helper->category->syncMenu();

			$this->setMessage(JText::_('COM_SELLACIOUS_CATEGORIES_MENU_CREATED_SUCCESS'));

			return true;
		}
		catch (Exception $e)
		{
			$this->setMessage(JText::sprintf('COM_SELLACIOUS_CATEGORIES_MENU_CREATED_FAILURE', $e->getMessage()), 'error');

			return false;
		}
	}
}
