<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * The Menu List Controller
 *
 * @since  1.6
 */
class MenusControllerMenus extends JControllerLegacy
{
	/**
	 * Display the view
	 *
	 * @param   boolean  $cacheable  If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.6
	 */
	public function display($cacheable = false, $urlparams = null)
	{
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Menu', $prefix = 'MenusModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Remove an item.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();
		$app  = JFactory::getApplication();
		$cids = (array) $this->input->get('cid', array(), 'array');

		if (count($cids) < 1)
		{
			$app->enqueueMessage(JText::_('COM_MENUS_NO_MENUS_SELECTED'), 'notice');
		}
		else
		{
			// Access checks.
			foreach ($cids as $i => $id)
			{
				if (!$user->authorise('core.delete', 'com_menus.menu.' . (int) $id))
				{
					// Prune items that you can't change.
					unset($cids[$i]);
					$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');
				}
			}

			if (count($cids) > 0)
			{
				// Get the model.
				$model = $this->getModel();

				// Make sure the item ids are integers
				$cids = ArrayHelper::toInteger($cids);

				// Remove the items.
				if (!$model->delete($cids))
				{
					$this->setMessage($model->getError());
				}
				else
				{
					$this->setMessage(JText::plural('COM_MENUS_N_MENUS_DELETED', count($cids)));
				}
			}
		}

		$this->setRedirect('index.php?option=com_menus&view=menus');
	}

	/**
	 * Rebuild the menu tree.
	 *
	 * @return  bool    False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect('index.php?option=com_menus&view=menus');

		$model = $this->getModel('Item');

		if ($model->rebuild())
		{
			// Reorder succeeded.
			$this->setMessage(JText::_('JTOOLBAR_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			// Rebuild failed.
			$this->setMessage(JText::sprintf('JTOOLBAR_REBUILD_FAILED', $model->getError()), 'error');

			return false;
		}
	}
}
