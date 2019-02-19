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
 * Sellacious Location model.
 */
class SellaciousModelLocation extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete($record)
	{
		return $this->helper->access->check('location.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
	 *                   component.
	 *
	 * @since   12.2
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('location.edit.state');
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		if (!parent::validate($form, $data, $group))
		{
			return false;
		}

		$typesP = array(
			'continent' => array(''), // Type for root
			'country'   => array('continent'),
			'state'     => array('country'),
			'district'  => array('country', 'state'),
			'area'      => array('country', 'state', 'district', 'zip'),
			'zip'       => array('country', 'state', 'district', 'area'),
		);
		$types  = ArrayHelper::getValue($typesP, $data['type'], array(), 'array');

		$parent_id   = ArrayHelper::getValue($data, 'parent_id', 1, 'uint');
		$parent_type = $this->helper->location->loadResult(array('id' => $parent_id, 'list.select' => 'a.type'));

		if (!array_key_exists($data['type'], $typesP) || !in_array($parent_type, $types))
		{
			// Unset invalid parent
			$data['parent_id'] = 0;
			$this->setError(JText::sprintf('COM_SELLACIOUS_LOCATION_INVALID_PARENT_TYPE', $data['type']));

			return false;
		}

		return $data;
	}

	/**
	 * Method to save the record
	 *
	 * @param   array  $data  Submitted data to save
	 *
	 * @return  bool
	 * @throws  Exception
	 */
	public function save($data)
	{
		// Special handling for continent
		if ($data['type'] == 'continent')
		{
			$data['parent_id'] = 1;
		}

		$parent_id = ArrayHelper::getValue($data, 'parent_id', 1, 'uint');
		$parent    = $this->helper->location->loadObject(array('id' => $parent_id ?: 1));

		$data['continent_id'] = $parent->type == 'continent' ? $parent->id : $parent->continent_id;
		$data['country_id']   = $parent->type == 'country' ? $parent->id : $parent->country_id;
		$data['state_id']     = $parent->type == 'state' ? $parent->id : $parent->state_id;
		$data['district_id']  = $parent->type == 'district' ? $parent->id : $parent->district_id;
		$data['zip_id']       = $parent->type == 'zip' ? $parent->id : $parent->zip_id;

		if (!parent::save($data))
		{
			return false;
		}

		$id = $this->getState($this->getName() . '.id');

		if ($id > 0)
		{
			$this->helper->location->buildCache($id);
		}

		return true;
	}

	/**
	 * Method to allow derived classes to preprocess the data.
	 *
	 * @param   string  $context  The context identifier.
	 * @param   mixed   &$data    The data to be processed. It gets altered directly.
	 * @param   string  $group    The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		// Get the dispatcher and load the plugins.
		$dispatcher = $this->helper->core->loadPlugins();

		if (is_array($data))
		{
			$c_type = &$data['type'];
		}
		elseif (is_object($data))
		{
			$c_type = &$data->type;
		}

		if (empty($c_type))
		{
			$c_type = $this->app->getUserState('com_sellacious.locations.filter.type', null);
		}

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form
	 * @param   mixed   $data
	 * @param   string  $group
	 *
	 * @throws  Exception
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		// Prevent root item's parent change
		if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
			$form->setFieldAttribute('parent_id', 'required', 'false');
		}

		// Check if a valid type is selected, and extend form.
		if (empty($obj->type) || $obj->type == 'continent')
		{
			// No parent for continent, or undefined type
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
			$form->setFieldAttribute('parent_id', 'required', 'false');
		}

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Get list of suggestions filtered by id, type and search keyword
	 *
	 * @param   string    $word          Search query to match
	 * @param   string[]  $types         Type of location to search
	 * @param   int       $parents       Parent item under which the search should be limited
	 * @param   int[]     $address_type  Address type to restrict results as
	 *
	 * @return  stdClass[]
	 */
	public function suggest($word = '', $types = null, $parents = null, $address_type = null)
	{
		// Todo: implement pagination
		$filters = array(
			'list.select' => 'a.id, a.title, a.type, a.continent_title, a.country_title, a.state_title, a.district_title, a.area_title, a.zip_title',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1'),
			'list.order'  => 'a.title',
		);

		// Backend has to handle multiple parents
		if ($parents && $parents != 1)
		{
			$pCond = array(
				'a.continent_id IN (' . implode(', ', (array) $parents) . ')',
				'a.country_id IN (' . implode(', ', (array) $parents) . ')',
				'a.state_id IN (' . implode(', ', (array) $parents) . ')',
				'a.district_id IN (' . implode(', ', (array) $parents) . ')',
			);

			$filters['list.where'][] = '(' . implode(' OR ', $pCond) . ')';
		}

		switch ($address_type)
		{
			case 'billing':
				$pksB  = $this->helper->location->getBilling();
				$where = $this->getFilter($pksB);
				break;

			case 'shipping':
				$pksS  = $this->helper->location->getShipping();
				$where = $this->getFilter($pksS);
				break;

			case 'both':
			case 'any':
				$pksB  = $this->helper->location->getBilling();
				$pksS  = $this->helper->location->getShipping();
				$where = array(
					$this->getFilter($pksB),
					$this->getFilter($pksS),
				);
				$where = array_filter($where);
				$glue  = $address_type == 'both' ? ' AND ' : ' OR ';
				$where = $glue == ' AND ' || count($where) == 2 ? '(' . implode($glue, $where) . ')' : null;
				break;

			default:
				$where = null;
				break;
		}

		if ($where)
		{
			$filters['list.where'][] = $where;
		}

		if (count($types))
		{
			$filters['type'] = $types;
		}

		if (strlen($word))
		{
			$match = $this->_db->q('%' . $this->_db->escape($word) . '%', false);

			$filters['list.where'][] = 'a.title LIKE ' . $match;
		}

		$items = $this->helper->location->loadObjectList($filters);

		if (!$items)
		{
			return array();
		}

		// Title formatting
		foreach ($items as $item)
		{
			$item->full_title = $this->buildTitle($item);
		}

		return $items;
	}

	/**
	 * Get list of items for given ids
	 *
	 * @param   int[]     $pks    Restricted list of ids to limit the search range, any parent or child of these are allowed rest not allowed.
	 * @param   string[]  $types  Geolocation types
	 *
	 * @return  stdClass[]
	 *
	 * @since  1.5.3
	 */
	public function getInfo($pks = null, $types = null)
	{
		$pks   = ArrayHelper::toInteger((array) $pks);
		$types = (array) $types;

		if (count($pks) == 0)
		{
			return array();
		}

		$key     = isset($types) && $types[0] == 'zip' ? 'title' : 'id';
		$filters = array(
			'list.select' => 'a.id, a.title, a.type, a.continent_title, a.country_title, a.state_title, a.district_title, a.area_title, a.zip_title',
			'list.where'  => array('a.state = 1', 'a.parent_id >= 1'),
			'list.order'  => 'a.title',
			$key          => $pks,
		);

		$items = $this->helper->location->loadObjectList($filters);

		if (!$items)
		{
			return array();
		}

		// Title formatting
		foreach ($items as $item)
		{
			$item->full_title = $this->buildTitle($item);
		}

		return $items;
	}

	/**
	 * Build geolocation filter condition based on given selected locations
	 *
	 * @param   $pks
	 *
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	protected function getFilter($pks)
	{
		$where   = null;
		$addr_to = array_reduce($pks, 'array_merge', array());
		$parents = $this->helper->location->getParents($addr_to, true);

		if ($parents)
		{
			$where = array(
				$pks['continent'] ? 'a.continent_id IN (' . implode(', ', $pks['continent']) . ')' : null,
				$pks['country'] ? 'a.country_id IN (' . implode(', ', $pks['country']) . ')' : null,
				$pks['state'] ? 'a.state_id IN (' . implode(', ', $pks['state']) . ')' : null,
				$pks['district'] ? 'a.district_id IN (' . implode(', ', $pks['district']) . ')' : null,
				$pks['zip'] ? 'a.zip_id IN (' . implode(', ', $pks['zip']) . ')' : null,
				$parents ? 'a.id  IN (' . implode(', ', $parents) . ')' : null,
			);
			$where = '(' . implode(' OR ', array_filter($where)) . ')';
		}

		return $where;
	}

	/**
	 * Build the full title for the given geolocation
	 *
	 * @param   stdClass  $item
	 *
	 * @return  string
	 *
	 * @since   1.4.0
	 */
	protected function buildTitle($item)
	{
		switch ($item->type)
		{
			default:
			case 'continent':
			case 'country':
				$title = $item->title;
				break;

			case 'state':
				$title = sprintf('%s (%s)', $item->title, $item->country_title);
				break;

			case 'district':
				$title = sprintf('%s (%s, %s)', $item->title, $item->state_title, $item->country_title);
				break;

			case 'zip':
				$title = sprintf('%s - %s, %s, %s', $item->title, $item->district_title, $item->state_title, $item->country_title);
		}

		return $title;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete(&$pks)
	{
		if (parent::delete($pks))
		{
			// Remove child geo-locations when a parent is removed.
			foreach ($pks as $pk)
			{
				$pk    = (int) $pk;
				$query = $this->_db->getQuery(true);

				$query->delete($this->getTable()->getTableName())
					->where('parent_id = ' . $pk, 'OR')
					->where('continent_id = ' . $pk)
					->where('country_id = ' . $pk)
					->where('state_id = ' . $pk)
					->where('district_id = ' . $pk)
					->where('zip_id = ' . $pk);

				$this->_db->setQuery($query);

				try
				{
					$this->_db->execute();
				}
				catch (Exception $e)
				{
					// ?
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.2
	 */
	public function publish(&$pks, $value = 1)
	{
		if (parent::publish($pks, $value))
		{
			foreach ($pks as $pk)
			{
				$pk = (int) $pk;

				// Modify parent geo-locations when child is modified: 1 => 1, 0 => 0|1, 2 => 0|1|2, -2 => *
				$table = $this->getTable();

				if ($value == 0 || $value == 1 || $value == 2)
				{
					$table->load($pk);

					$query  = $this->_db->getQuery(true);
					$values = array(
						$table->get('parent_id'),
						$table->get('continent_id'),
						$table->get('country_id'),
						$table->get('state_id'),
						$table->get('district_id'),
						$table->get('zip_id'),
					);
					$values = ArrayHelper::toInteger($values);
					$values = array_filter($values);

					if (count($values))
					{
						$query->update($table->getTableName())
							->set('state = ' . (int) $value)
							->where('id IN (' . implode(',', $values) . ')')
							->where('parent_id > 0');

						if ($value == 1)
						{
							$query->where('state != 1');
						}
						elseif ($value == 0)
						{
							$query->where('state != 0');
							$query->where('state != 1');
						}
						elseif ($value == 2)
						{
							$query->where('state != 0');
							$query->where('state != 1');
							$query->where('state != 2');
						}

						try
						{
							$this->_db->setQuery($query);
							$this->_db->execute();
						}
						catch (Exception $e)
						{
							// ?
						}
					}
				}

				// Modify child geo-locations when a parent is modified: n => n
				$query = $this->_db->getQuery(true);

				$query->update($table->getTableName())
					->set('state = ' . (int) $value)
					->where('parent_id = ' . $pk, 'OR')
					->where('continent_id = ' . $pk)
					->where('country_id = ' . $pk)
					->where('state_id = ' . $pk)
					->where('district_id = ' . $pk)
					->where('zip_id = ' . $pk);

				try
				{
					$this->_db->setQuery($query);
					$this->_db->execute();
				}
				catch (Exception $e)
				{
					// ?
				}
			}

			return true;
		}

		return false;
	}
}
