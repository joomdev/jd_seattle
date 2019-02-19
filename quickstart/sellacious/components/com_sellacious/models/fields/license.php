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

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_sellacious
 *
 * @since       1.6
 */
class JFormFieldLicense extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'License';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$helper  = SellaciousHelper::getInstance();
		$filter  = array('list.select' => 'a.id AS value, a.title AS text', 'list.where' => 'a.state = 1', 'list.order' => 'a.title');
		$options = $helper->license->loadObjectList($filter);

		return array_merge(parent::getOptions(), (array) $options);
	}
}
