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

use Joomla\Registry\Registry;

/**
 * View to edit a sellacious user account
 *
 * @since   1.2.0
 */
class SellaciousViewProfile extends SellaciousViewForm
{
	/**
	 * @var    string
	 *
	 * @since   1.2.0
	 */
	protected $action_prefix = 'user';

	/**
	 * @var    string
	 *
	 * @since   1.2.0
	 */
	protected $view_item = 'profile';

	/**
	 * @var    string
	 *
	 * @since   1.2.0
	 */
	protected $view_list = null;

	/**
	 * A copy of the $item object attribute of this class but in registry format
	 *
	 * @var    Registry
	 *
	 * @since   1.2.0
	 */
	protected $registry;

	/**
	 * @var    Registry
	 *
	 * @since   1.6.0
	 */
	protected $ccParams;

	/**
	 * @var    Registry
	 *
	 * @since   1.6.0
	 */
	protected $scParams;

	/**
	 * Display the view
	 *
	 * @return  void
	 *
	 * @since   1.2.0
	 */
	public function prepareDisplay()
	{
		$me = JFactory::getUser();

		if ($me->guest)
		{
			$this->app->redirect(JRoute::_('index.php?option=com_sellacious&view=register', false));
		}

		$this->registry = new Registry($this->item);

		$cParams = $this->helper->category->getFieldValue($this->registry->get('client.category_id'), 'params');
		$sParams = $this->helper->category->getFieldValue($this->registry->get('seller.category_id'), 'params');

		$this->ccParams = new Registry($cParams);
		$this->scParams = new Registry($sParams);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.2.0
	 */
	protected function addToolbar()
	{
	}

	/**
	 * Check from category params whether to show/hide field
	 *
	 * @param   string  $fieldKey
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function getShowOption($fieldKey)
	{
		if (strpos($fieldKey, 'client.') !== false)
		{
			return $this->ccParams->get($fieldKey, 1) > 0;
		}

		if (strpos($fieldKey, 'seller.') !== false)
		{
			return $this->scParams->get($fieldKey, 1) > 0;
		}

		return $this->ccParams->get($fieldKey, 1) > 0 || $this->scParams->get($fieldKey, 1) > 0;
	}
}
