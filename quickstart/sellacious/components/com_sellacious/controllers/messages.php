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

/**
 * Messages list controller class.
 *
 */
class SellaciousControllerMessages extends SellaciousControllerAdmin
{
	/**
	 * @var string
	 */
	protected $text_prefix = 'COM_SELLACIOUS_MESSAGES';

	/**
	 * Proxy for getModel.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param array  $config
	 *
	 * @return object
	 */
	public function getModel($name = 'Message', $prefix = 'SellaciousModel', $config = Array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		$allowed = $this->helper->access->check('message.rebuild');

		$this->setRedirect(JRoute::_('index.php?option=com_sellacious&view=messages', false));

		if (!$allowed)
		{
			JLog::add(JText::_('COM_SELLACIOUS_ACCESS_NOT_ALLOWED'));

			return false;
		}

		$model = $this->getModel();

		if ($model->rebuild())
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_SUCCESS'));

			return true;
		}
		else
		{
			$this->setMessage(JText::_($this->text_prefix . '_REBUILD_FAILURE'));

			return false;
		}
	}
}
