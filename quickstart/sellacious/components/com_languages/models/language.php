<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Language\LanguagePack;

/**
 * Languages Component Language Model
 *
 * @since  1.6.0
 */
class LanguagesModelLanguage extends SellaciousModelAdmin
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    Table name
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for table. Optional.
	 *
	 * @return  JTable
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getTable($type = 'Language', $prefix = 'LanguagesTable', $config = array())
	{
		JTable::addIncludePath(dirname(__DIR__) . '/tables');

		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function populateState()
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_languages');

		// Load the User state.
		$langId = $app->input->getInt('id');
		$this->setState('language.id', $langId);

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a member item.
	 *
	 * @param   integer $pk The id of the member to get.
	 *
	 * @return  mixed  User data object on success, false on failure.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0
	 */
	public function getItem($pk = null)
	{
		$pk = !empty($pk) ? $pk : (int) $this->getState('language.id');

		// Get a member row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());

			return false;
		}

		$properties = $table->getProperties(1);
		$value      = ArrayHelper::toObject($properties, 'JObject');

		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   1.6.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JFormHelper::addFormPath(__DIR__ . '/forms');

		$form = $this->loadForm('com_languages.language', 'language', array('control' => 'jform', 'load_data' => $loadData));

		return empty($form) ? false : $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_languages.edit.language.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_languages.language', $data);

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function save($data)
	{
		$langId = !empty($data['lang_id']) ? $data['lang_id'] : (int) $this->getState('language.id');
		$isNew  = true;

		$dispatcher = $this->helper->core->loadPlugins();

		$table   = $this->getTable();
		$context = $this->option . '.' . $this->name;

		// Load the row if saving an existing item.
		if ($langId > 0)
		{
			$table->load($langId);
			$isNew = false;
		}

		// Prevent white spaces, including East Asian double bytes.
		$spaces = array('/\xE3\x80\x80/', ' ');

		$data['lang_code'] = str_replace($spaces, '', $data['lang_code']);

		// Prevent saving an incorrect language tag
		if (!preg_match('#\b([a-z]{2,3})[-]([A-Z]{2})\b#', $data['lang_code']))
		{
			$this->setError(JText::_('COM_LANGUAGES_ERROR_LANG_TAG'));

			return false;
		}

		$data['sef'] = str_replace($spaces, '', $data['sef']);
		$data['sef'] = JApplicationHelper::stringURLSafe($data['sef']);

		// Bind the data.
		if (!$table->bind($data))
		{
			$this->setError($table->getError());

			return false;
		}

		// Check the data.
		if (!$table->check())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the before save event.
		$result = $dispatcher->trigger($this->event_before_save, array($context, &$table, $isNew));

		// Check the event responses.
		if (in_array(false, $result, true))
		{
			$this->setError($table->getError());

			return false;
		}

		// Store the data.
		if (!$table->store())
		{
			$this->setError($table->getError());

			return false;
		}

		// Trigger the after save event.
		$dispatcher->trigger($this->event_after_save, array($context, &$table, $isNew));

		$this->setState('language.id', $table->get('lang_id'));

		// Create and install the language pack
		$lang = (object) $table->getProperties();

		$pack = new LanguagePack($lang);
		$pack->install();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 * NOTE: The content language as well as the languages extensions will be unpublished
	 *
	 * @param   array  &$pks   A list of the primary keys to change.
	 * @param   int    $value  The value of the published state.
	 *
	 * @return  bool  True on success.
	 *
	 * @since   1.6.0
	 */
	public function publish(&$pks, $value = 1)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;

		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				$table->set('published', $value);

				$table->store();

				// $o = (object) array('type' => 'language', 'element' => $table->get('lang_code'), 'enabled' => $value);

				// $this->_db->updateObject('#__extensions', $o, array('type', 'element'));
			}
		}

		return true;
	}
}
