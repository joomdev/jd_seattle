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

use Joomla\Utilities\ArrayHelper;

/**
 * Sellacious helper
 *
 * @since   1.0.0
 */
class SellaciousHelperField extends SellaciousHelperBase
{
	/**
	 * Returns the application supported field types (plugins can add)
	 *
	 * @param   bool  $associative  Whether to return associative array or list of objects
	 *
	 * @return  array  List of field groups in requested format
	 *
	 * @since   1.0.0
	 */
	public function getTypes($associative = false)
	{
		$assoc = array();

		$dispatcher = $this->helper->core->loadPlugins('sellacious');
		$result     = $dispatcher->trigger('onFetchFieldTypes', array('com_sellacious.field'));

		foreach ($result as $res)
		{
			$assoc = array_merge($assoc, (array) $res);
		}

		if ($associative)
		{
			return $assoc;
		}

		$options = array();

		foreach ($assoc as $value => $text)
		{
			$options[] = (object) array('value' => $value, 'text' => $text);
		}

		return $options;
	}

	/**
	 * Get a list of fields including parent groups
	 *
	 * @param   int[]  $pks  Flat fields id list
	 *
	 * @return  int[]
	 *
	 * @since   1.0.0
	 */
	public function getListWithGroup($pks)
	{
		$fields  = $this->getParents($pks, true);
		$filters = array(
			'list.select' => 'a.id',
			'list.order'  => 'a.lft',
			'list.where'  => array(
				'a.level > 0',
			),
			'id'          => $fields,
			'state'       => 1,
		);

		// List returns level always, used load column to ignore that
		$list = $this->loadColumn($filters);

		return $list;
	}

	/**
	 * Set field value for custom field in the storage table
	 *
	 * @param   string  $table_name  Name of the table to which this field is associated
	 * @param   int     $rec_id      Record_id for which the field value to associate
	 * @param   int     $field_id    Field_id for which the value is given
	 * @param   mixed   $value       Value to store
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setValue($table_name, $rec_id, $field_id, $value)
	{
		/** @var  SellaciousTableFieldValues  $table */
		$table = $this->getTable('FieldValues');
		$data  = array(
			'table_name' => $table_name,
			'record_id'  => $rec_id,
			'field_id'   => $field_id,
		);

		// If some row matched unique keys we'd overwrite it
		$table->load($data);

		if (!empty($value))
		{
			$data['is_json']     = is_scalar($value) ? 0 : 1;
			$data['field_value'] = is_scalar($value) ? $value : json_encode($value);

			$table->save($data);
		}
		elseif ($table->get('id'))
		{
			$table->delete();
		}
	}

	/**
	 * Remove field value for custom field in the storage table for the given field ids
	 *
	 * @param   string  $table_name  Name of the table to which field(s) are associated
	 * @param   int     $rec_id      Record_id for which the field value to associate
	 * @param   int[]   $field_ids   Field ids to remove
	 * @param   bool    $invert      True means remove all fields except the given ones (default = false)
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function clearValue($table_name, $rec_id, array $field_ids, $invert = false)
	{
		if (!$invert && empty($field_ids))
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->delete($db->qn('#__sellacious_field_values'))
			->where($db->qn('table_name') . ' = ' . $db->q($table_name))
			->where($db->qn('record_id') . ' = ' . $db->q($rec_id));

		$field_ids = ArrayHelper::toInteger($field_ids);

		if (!$invert)
		{
			$query->where($db->qn('field_id') . ' IN (' . implode(', ', $db->q($field_ids)) . ')');
		}
		elseif (!empty($field_ids))
		{
			$query->where($db->qn('field_id') . ' NOT IN (' . implode(', ', $db->q($field_ids)) . ')');
		}

		$db->setQuery($query);

		$db->execute();
	}

	/**
	 * Get field value from custom field storage table
	 *
	 * @param   string  $table        name of the table to which this field is associated
	 * @param   int     $rec_id       record_id for which the field value to retrieve
	 * @param   int     $field_id     optional, field_id for which the value to retrieve
	 * @param   bool    $full_object  Whether to return entire object or field_id => value pair
	 *
	 * @return  mixed   value if field_id is provided, values for all fields for desired record
	 *
	 * @since   1.0.0
	 */
	public function getValue($table, $rec_id, $field_id = null, $full_object = false)
	{
		$results = array();

		try
		{
			$filter = array(
				'list.select' => 'a.field_id, a.field_value, f.title AS field_title, a.is_json',
				'list.from'   => '#__sellacious_field_values',
				'list.join'   => array(
					array('INNER', $this->db->qn($this->table, 'f') . ' ON f.id = a.field_id')
				),
				'list.where'  => array(
					'a.table_name = ' . $this->db->q($table),
					'a.record_id = ' . (int) $rec_id,
					'f.state = 1',
				),
			);

			if ($field_id)
			{
				$filter['list.where'][] = 'a.field_id = ' . (int) $field_id;
			}

			$rows = $this->loadObjectList($filter);

			if ($rows)
			{
				foreach ($rows as $row)
				{
					$row->field_value        = $row->is_json ? json_decode($row->field_value) : $row->field_value;
					$results[$row->field_id] = $full_object ? $row : $row->field_value;
				}
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$results = array();
		}

		return $field_id ? ArrayHelper::getValue($results, $field_id) : $results;
	}

	/**
	 * Render the hold value using appropriate rendered plugin call
	 *
	 * @param   mixed   $value
	 * @param   string  $type
	 * @param   object  $field
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function renderValue($value, $type, $field = null)
	{
		$text       = '';
		$dispatcher = $this->helper->core->loadPlugins();

		if (!is_object($field))
		{
			$field = new stdClass;
		}

		$field->type = $type;

		// Todo: Do consistent fix
		if ($type == 'checkbox')
		{
			$field->checked = $value;
		}
		else
		{
			$field->value = $value;
		}

		try
		{
			$dispatcher->trigger('onRenderCustomFieldValue', array('com_sellacious.field', $field, &$text));
		}
		catch (Exception $e)
		{
			$text = $value;

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $text;
	}

	/**
	 * Remove field value from custom field storage table
	 *
	 * @param   string     $table    Name of the table to which this field is associated
	 * @param   int|int[]  $rec_id   Record_id for which the field value to remove
	 * @param   int|int[]  $fieldId  Optional, field_id for which the value to remove
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function deleteValue($table, $rec_id, $fieldId = null)
	{
		$filter = array('list.from' => '#__sellacious_field_values', 'table_name' => $table, 'record_id'  => $rec_id);

		if ($fieldId)
		{
			$filter['field_id'] = $fieldId;
		}

		return $this->deleteRecords($filter);
	}

	/**
	 * Method to get xml for element for the set of custom fields in a category
	 * multiple forms returned from this functions can be merged directly using JForm::load
	 *
	 * @param   int[]   $pks            Ids of the fields that should be added to the form
	 * @param   string  $group          Name for the fieldset generated
	 * @param   string  $fieldsetName   Name for the fields group generated (use NULL to create fieldsets based on field group)
	 * @param   string  $fieldsetLabel  Name for the fields group generated (use NULL to create fieldsets based on field group)
	 *
	 * @return  SimpleXMLElement[]  XML for the form thus generated
	 *
	 * @since   1.5.3
	 */
	public function getFieldsXml($pks, $group = null, $fieldsetName = null, $fieldsetLabel = null)
	{
		$items = array();
		$forms = array();

		if ($fieldsetName)
		{
			$items[1] = new stdClass;

			$items[1]->title = $fieldsetLabel;
			$items[1]->group = $fieldsetName;
			$items[1]->items = $this->getListWithGroup($pks);
		}
		else
		{
			$filters = array(
				'list.select' => 'a.id, a.type, c.id AS group_id, c.title AS group_title',
				'list.where'  => array('a.level > 1'),
				'id'          => $pks,
				'state'       => 1,
			);
			$fields  = $this->loadObjectList($filters);

			foreach ($fields as $field)
			{
				if (!isset($items[$field->group_id]))
				{
					$items[$field->group_id] = new stdClass;

					$items[$field->group_id]->title = $field->group_title;
					$items[$field->group_id]->group = 'fs_' . $field->group_id;
					$items[$field->group_id]->items = array();
				}

				$items[$field->group_id]->items[] = $field->id;
			}
		}

		$language = JFactory::getLanguage()->getTag();

		foreach ($items as $group_id => $item)
		{
			if ($fieldsetLabel)
			{
				$groupLabel = $fieldsetLabel;
			}
			else
			{
				$fieldGroup = $this->getTable();
				$fieldGroup->load($group_id);
				$fGroup     = (object) $fieldGroup->getProperties(1);

				$this->helper->translation->translateRecord($fGroup, 'sellacious_fields', $language);

				$groupLabel = $fGroup->title;
			}

			$forms[] = $this->createFormXml($item->items, $item->group, $group, $groupLabel);
		}

		return $forms;
	}

	/**
	 * Method to create a form xml element for the set of custom form fields
	 * Tip: Multiple forms returned from this functions (with multiple calls) can be merged directly using JForm::load
	 *
	 * @param   int[]   $fields    Ids of the fields to be added to the form
	 * @param   string  $fieldset  Name for the fieldset generated
	 * @param   string  $group     Name for the fields group generated
	 * @param   string  $label     Label for the fieldset generated
	 *
	 * @return  SimpleXMLElement  XML object for the form generated
	 *
	 * @since   1.0.0
	 */
	public function createFormXml($fields, $fieldset, $group = null, $label = null)
	{
		$xml = new SimpleXMLElement('<form/>');

		$fieldsetXml = $xml->addChild('fieldset');
		$fieldsetXml->addAttribute('name', $fieldset);

		if ($label)
		{
			$fieldsetXml->addAttribute('label', $label);
		}

		if (isset($group))
		{
			$fieldsXml = $fieldsetXml->addChild('fields');
			$fieldsXml->addAttribute('name', $group);

			$parent = $fieldsXml;
		}
		else
		{
			$parent = $fieldsetXml;
		}

		if (!empty($fields))
		{
			foreach ($fields as $field)
			{
				$this->addXmlElement($field, $parent);
			}
		}

		return $xml;
	}

	/**
	 * Get custom field in xml_element form ready to be injected into a JForm object
	 *
	 * @param   int               $field_id  Field id or the field object for which the xml_element to retrieve
	 * @param   SimpleXmlElement  $parent    Source xml_element into which the node will be appended
	 *
	 * @return  SimpleXmlElement  XMLElement representation of the field OR the parent (if given) with field node added
	 *
	 * @since   1.0.0
	 */
	public function addXmlElement($field_id, SimpleXMLElement &$parent = null)
	{
		$field = $this->getTable();
		$field->load($field_id);

		$fld      = (object) $field->getProperties(1);
		$language = JFactory::getLanguage()->getTag();
		$this->helper->translation->translateRecord($fld, 'sellacious_fields', $language);

		$field->set('title', $fld->title);

		if ($field->get('level') == 0)
		{
			return null;
		}

		$xml = $field->get('xml_cache');

		if ($xml == '')
		{
			$element = $parent instanceOf SimpleXMLElement ? $parent->addChild('field') : new SimpleXMLElement('<field/>');

			// Core attributes exist for all field types
			$attributes = array(
				'label'       => 'title',
				'type'        => 'type',
				'message'     => 'message',
				'description' => 'description',
				'class'       => 'class',
				'validate'    => 'validate',
				'required'    => 'required',
			);

			$element->addAttribute('name', $field->get('id'));

			foreach ($attributes as $attribute => $property)
			{
				if (!empty($field->$property))
				{
					$element->addAttribute($attribute, $field->$property);
				}
			}

			$dispatcher = $this->helper->core->loadPlugins('sellacious');
			$dispatcher->trigger('onRenderCustomField', array('com_sellacious.field', $field, &$element));

			// $tblField->set('xml_cache', $fieldXml->asXML());
			// $tblField->store();
		}
		else
		{
			$element = simplexml_load_string($xml);

			if ($parent instanceOf SimpleXMLElement)
			{
				/*
				 * We need a way to cache the built xml so that we can use it again without rebuilding all over again every-time.
				 * But no way to append a xml string to an existing SimpleXMLElement node.
				 * So we do not store any cache and hence this "else" block never executes, as of now.
				 *
				 * After we are done implementing this, we MUST
				 * Ensure that on every record update this cache is flushed.
				 */
			}
		}

		return $element;
	}

	/**
	 * Get all available filter values for the given fields.
	 *
	 * @param   stdClass  $field   The array of filterable fields, choices list will be added as a property
	 * @param   array     $tables  An array of table names for 'table_name' key for which to load the filters.
	 *                             If a table name is not specified then value across all tables will be loaded.
	 *                             All options may be displayed if the options are directly bound to the field,
	 *                             such as select list type inputs.
	 *
	 * @return  array
	 *
	 * @throws  Exception
	 *
	 * @since   1.3.0
	 */
	public function getFilterChoices($field, $tables)
	{
		$dispatcher = $this->helper->core->loadPlugins('sellacious');
		$options    = $dispatcher->trigger('onFieldFilterOptions', array('com_sellacious.field', $field, $tables));

		return array_reduce($options, 'array_merge', array());
	}

	/**
	 * Add category tags for the selected field
	 *
	 * @param   int    $fieldId
	 * @param   int[]  $tags
	 *
	 * @return  bool
	 *
	 * @since   1.3.0
	 */
	public function setTags($fieldId, $tags)
	{
		$query = $this->db->getQuery(true);

		$current = $this->getTags($fieldId, true);

		// If something is in current by inheritance, we don't need to add it directly anyway.
		$add = array_diff((array) $tags, (array) $current);
		$sub = array_diff((array) $current, (array) $tags);

		if (count($add))
		{
			$query->clear()->insert('#__sellacious_field_tags')
				->columns(array('field_id', 'category_id'));

			foreach ($add as $item)
			{
				$query->values(sprintf('%d, %d', $fieldId, $item));
			}

			try
			{
				$this->db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		if (count($sub))
		{
			$query->clear()
				->delete('#__sellacious_field_tags')
				->where('field_id = ' . (int) $fieldId)
				->where('category_id IN (' . implode(', ', $this->db->quote($sub)) . ')');

			try
			{
				$this->db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
			}
		}

		return true;
	}

	/**
	 * Get assigned category tags for the selected field. Includes tags from parent group as well
	 *
	 * @param   int   $fieldId
	 * @param   bool  $id_only
	 * @param   bool  $inherit
	 *
	 * @return  stdClass[]|int[]
	 *
	 * @since   1.3.0
	 */
	public function getTags($fieldId, $id_only = false, $inherit = true)
	{
		$pks = array($fieldId);

		if ($inherit)
		{
			$field = $this->getItem($fieldId);

			if ($field->parent_id > 1)
			{
				$pks[] = $field->parent_id;
			}
		}

		$query = $this->db->getQuery(true);
		$query->select($this->db->qn('a.category_id', 'tag_id'))
			->from($this->db->qn('#__sellacious_field_tags', 'a'))
			->where('a.field_id IN (' . implode(', ', $this->db->quote($pks)) . ')');

		if (!$id_only)
		{
			$query->select($this->db->qn('c.title', 'tag_title'))
				->join('left', '#__sellacious_categories AS c ON c.id = a.category_id');
		}

		try
		{
			$this->db->setQuery($query);

			$tags = $id_only ? $this->db->loadColumn() : $this->db->loadObjectList();
		}
		catch (Exception $e)
		{
			$tags = array();

			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');
		}

		return $tags;
	}

	/**
	 * Form fields id ~ value associative array to prepare rendered data per field
	 *
	 * @param   array  $formData
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function buildData($formData)
	{
		$values = array();
		$filter = array(
			'list.select' => 'a.id, a.title, a.type, a.parent_id, c.title AS parent_title',
			'list.order'  => 'c.lft, a.lft',
			'id'          => array_keys($formData),
		);
		$fields = $this->helper->field->loadObjectList($filter);

		// NOTE: Do not remove this, PHP sometimes gets confused bet'n string and numeric keys and can't see the value
		$formData = ArrayHelper::fromObject((object) $formData);

		foreach ($fields as $field)
		{
			$input = new stdClass;

			$input->field_id = $field->id;
			$input->group_id = $field->parent_id;
			$input->label    = $field->title;
			$input->group    = $field->parent_title;
			$input->value    = ArrayHelper::getValue($formData, $field->id);
			$input->html     = $this->helper->field->renderValue($input->value, $field->type);

			$values[] = $input;
		}

		return $values;
	}
}
