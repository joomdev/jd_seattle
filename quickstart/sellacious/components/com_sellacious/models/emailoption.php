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
 * Sellacious model.
 *
 * @since   1.6.0
 *
 */
class SellaciousModelEmailOption extends SellaciousModelAdmin
{
	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 * 
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getTable($name = 'Config', $prefix = 'SellaciousTable', $options = array())
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.6.0
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('emailoption.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.6.0
	 */
	protected function canEditState($record)
	{

		return $this->helper->access->check('emailoption.edit.state');
	}

	/**
	 * Method to save the record
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean
	 *
	 * @since   1.6.0
	 */
	public function save($data)
	{
		return $this->helper->config->save($data, 'com_sellacious', 'emailtemplate_options');
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   integer $pk The id of the primary key.
	 *
	 * @return  stdClass
	 * 
	 * @since   1.6.0
	 */
	public function getItem($pk = null)
	{
		$params = $this->helper->config->getParams('com_sellacious', 'emailtemplate_options');

		$data = (object) array($params->toArray());

		return $data;
	}

}
