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
 * Sellacious model.
 */
class SellaciousModelVariant extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		$me       = JFactory::getUser();
		$owned_by = ArrayHelper::getValue((array)$record, 'owned_by');

		return $this->helper->access->check('variant.delete')
			|| ($owned_by == $me->get('id') && $this->helper->access->check('variant.delete.own'));
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('variant.edit.state');
	}

	/**
	 * Method to save the form data
	 *
	 * @param   array  $data  The form data
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function save($data)
	{
		$me        = JFactory::getUser();
		$pk        = ArrayHelper::getValue($data, 'id', $this->getState('variant.id'), 'int');
		$fields    = ArrayHelper::getValue($data, 'fields', array(), 'array');
		$eProducts = ArrayHelper::getValue($data, 'eproduct', array(), 'array');

		$this->helper->core->loadPlugins('sellacious');

		unset($data['fields']);

		$isNew = $pk == 0;
		$table = $this->getTable();

		if (!$isNew)
		{
			$table->load($pk);
		}

		/**
		 * If the product is electronic also save the e-products
		 * Product Id etc is already bound to each of these as the rows are created beforehand. Maybe we should unset those to prevent changes?
		 *
		 * Todo: Add validation check whether its permitted and applicable for this variant
		 */
		$eProducts = array_filter($eProducts);

		foreach ($eProducts as $eproduct)
		{
			$tableM = $this->getTable('EProductMedia');

			$eproduct['is_latest'] = isset($eproduct['is_latest']) ? $eproduct['is_latest'] : 0;
			$eproduct['state']     = isset($eproduct['state']) ? $eproduct['state'] : 0;

			$tableM->load($eproduct['id']);
			$tableM->bind($eproduct);
			$tableM->check();
			$tableM->store();
		}

		$table->bind($data);

		// Assign ownership if a new product and the creator cannot add/modify shop (global) owned products
		if ($isNew && !$this->helper->access->check('variant.edit'))
		{
			$table->set('owned_by', $me->id);
		}

		$table->check();
		$table->store();

		// Update state beforehand
		$this->state->set($this->getName() . '.id', $table->get('id'));

		$vFields = $this->helper->product->getFields($table->get('product_id'), array('variant'));
		$pFid    = ArrayHelper::getColumn($vFields, 'id');
		$values  = array();

		foreach ($pFid as $fid)
		{
			$values[$fid] = ArrayHelper::getValue($fields, $fid);
		}

		$this->helper->variant->setSpecifications($table->get('id'), $values);

		$dispatcher = $this->helper->core->loadPlugins();
		$dispatcher->trigger('onContentAfterSave', array('com_sellacious.variant', $table, $isNew));

		return true;
	}
}
