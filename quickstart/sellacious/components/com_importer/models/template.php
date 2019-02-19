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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Importer template model.
 *
 * @since   1.5.2
 */
class ImporterModelTemplate extends SellaciousModelAdmin
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
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable($name = 'Template', $prefix = 'ImporterTable', $options = array())
	{
		JTable::addIncludePath(dirname(__DIR__) . '/tables');

		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not
	 *
	 * @return  JForm|bool  A JForm object on success, false on failure
	 *
	 * @since   1.5.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JFormHelper::addFieldPath(__DIR__ . '/fields');
		JFormHelper::addFormPath(__DIR__ . '/forms');

		return parent::getForm($data, $loadData);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @throws  Exception
	 * @return  bool
	 *
	 * @since   1.5.2
	 */
	public function save($data)
	{
		// Clean-up mapping columns
		if (!is_array($data['mapping']))
		{
			$data['mapping'] = array();
		}
		else
		{
			$mapping = array();

			foreach ($data['mapping'] as $key => $col)
			{
				if (trim($col))
				{
					$mapping[$key] = $col;
				}
			}

			$data['mapping'] = $mapping;
		}

		$uCats = ArrayHelper::getValue($data, 'user_categories', array(), 'array');
		$saved = parent::save($data);

		if ($saved)
		{
			// Add allowed user groups
			$templateId = $this->getState('template.id');

			$query = $this->_db->getQuery(true);
			$query->select('a.user_cat_id')
				->from($this->_db->qn('#__importer_template_usercategories', 'a'))
				->where('a.template_id = ' . (int) $templateId);

			$current = (array) $this->_db->setQuery($query)->loadColumn();

			$add    = array_diff($uCats, $current);
			$remove = array_diff($current, $uCats);

			if ($remove)
			{
				$query->clear()
					->delete($this->_db->qn('#__importer_template_usercategories'))
					->where('template_id = ' . (int) $templateId)
					->where('user_cat_id IN (' . implode(',', array_map('intval', $remove)) . ')');

				$this->_db->setQuery($query)->execute();
			}

			if ($add)
			{
				$o = new stdClass;

				$o->template_id = $templateId;

				foreach ($add as $uCat)
				{
					$o->user_cat_id = $uCat;

					$this->_db->insertObject('#__importer_template_usercategories', $o, null);
				}
			}
		}

		return $saved;
	}

	/**
	 * Pre-process loaded item before returning if needed
	 *
	 * @param   object  $item
	 *
	 * @return  object
	 *
	 * @since   1.5.2
	 */
	protected function processItem($item)
	{
		$item = parent::processItem($item);

		$query = $this->_db->getQuery(true);
		$query->select('a.user_cat_id')
			->from($this->_db->qn('#__importer_template_usercategories', 'a'))
			->where('a.template_id = ' . (int) $item->id);

		$current = (array) $this->_db->setQuery($query)->loadColumn();

		$item->user_categories = $current;

		return $item;
	}

	/**
	 * Method to save an import template
	 *
	 * @param   string  $source   The import handler name for which this template will be used
	 * @param   string  $name     The user assigned name
	 * @param   array   $aliases  The columns alias mapping
	 * @param   int     $userId   The user for which this template will be assigned
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function saveTemplate($source, $name, $aliases, $userId = null)
	{
		array_walk($aliases, 'trim');

		/** @var  ImporterTableTemplate  $table */
		$table = $this->getTable();
		$data  = array(
			'import_type' => $source,
			'title'       => $name,
			'user_id'     => $userId,
			'mapping'     => $aliases,
			'state'       => 1,
		);

		if (!$table->bind($data))
		{
			throw new Exception($table->getError());
		}

		if (!$table->check())
		{
			throw new Exception($table->getError());
		}

		if (!$table->store())
		{
			throw new Exception($table->getError());
		}

		return true;
	}

	/**
	 * Method to get a selected import template by id
	 *
	 * @param   int  $id     The import template id
	 * @param   int  $title  The import template new title
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function rename($id, $title)
	{
		$table = $this->getTable();
		$table->load($id);

		if (!$table->get('id'))
		{
			throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_NOT_FOUND'));
		}

		$table->set('title', $title);

		if (!$table->check())
		{
			throw new Exception($table->getError());
		}

		if (!$table->store())
		{
			throw new Exception($table->getError());
		}

		return true;
	}

	/**
	 * Method to delete a selected import template by id
	 *
	 * @param   int  $id  The import template id
	 *
	 * @return  bool
	 * @throws  Exception
	 *
	 * @since   1.5.2
	 */
	public function remove($id)
	{
		$table = $this->getTable();
		$table->load($id);

		if (!$table->get('id'))
		{
			throw new Exception(JText::_('COM_IMPORTER_IMPORT_TEMPLATE_NOT_FOUND'));
		}

		if (!$table->delete())
		{
			throw new Exception($table->getError());
		}

		return true;
	}
}
