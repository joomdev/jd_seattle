<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('List');

/**
 * Form field class for available client type
 *
 * @since   1.2.0
 */
class JFormFieldClientType extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.2.0
	 */
	protected $type = 'ClientType';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  An array of JHtml options.
	 *
	 * @since   1.2.0
	 */
	protected function getOptions()
	{
		try
		{
			$helper   = SellaciousHelper::getInstance();
			$catTypes = $helper->client->getTypes();

			return array_merge(parent::getOptions(), $catTypes);
		}
		catch (Exception $e)
		{
			return parent::getOptions();
		}
	}
}
