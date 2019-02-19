<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Config controller class.
 */
class SellaciousControllerConfig extends SellaciousControllerForm
{
	/**
	 * @var		string	The name of the list view related to this
	 *
	 * @todo	Temporary fix. Actual redirect should be to the view that sent us here.
	 *
	 * @since	1.6
	 */
	protected $view_list = 'config';

	/**
	 * @var		string	The prefix to use with controller messages
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_SELLACIOUS_CONFIG';

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   12.2
	 */
	protected function allowSave($data, $key = 'id')
	{
		return $this->helper->access->check('config.edit');
	}
}
