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
use Sellacious\Media\Upload\Uploader;

/**
 * Sellacious Category model.
 *
 * @since   1.0.0
 */
class SellaciousModelCategory extends SellaciousModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canDelete($record)
	{
		if ($count = $this->helper->category->countItems($record->id, false))
		{
			$this->setError(JText::sprintf('COM_SELLACIOUS_CATEGORY_HAS_ITEMS_DELETE_NOT_ALLOWED', $record->title, $count));

			return false;
		}

		return $this->helper->access->check('category.delete');
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object $record A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.0.0
	 */
	protected function canEditState($record)
	{
		return $this->helper->access->check('category.edit.state');
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
	 * @since   1.0.0
	 */
	public function save($data)
	{
		// Initialise variables
		$dispatcher = JEventDispatcher::getInstance();

		/** @var SellaciousTableCategory $table */
		$table = $this->getTable();
		$pk    = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('sellacious');

		// Load the row if saving an existing category.
		if ($pk > 0)
		{
			$table->load($pk);
			$isNew = false;
		}

		if (isset($data['seller_commission']))
		{
			$sellerCommission = $data['seller_commission'];

			unset($data['seller_commission']);
		}

		// Set the new parent id if parent id not matched OR while New/Save as Copy .
		if ($table->get('parent_id') != $data['parent_id'] || $data['id'] == 0)
		{
			$table->setLocation($data['parent_id'], 'last-child');
		}

		// Alter the title for save as copy
		if ($this->app->input->get('task') == 'save2copy')
		{
			list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
			$data['title']      = $title;
			$data['alias']      = $alias;
			$data['is_default'] = false;
		}

		if (!isset($data['core_fields']))
		{
			$data['core_fields'] = array();
		}

		if (!isset($data['variant_fields']))
		{
			$data['variant_fields'] = array();
		}

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

		// Trigger the onBeforeSave event.
		$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));

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

		$this->setState($this->getName() . '.id', $table->get('id'));

		try
		{
			$_control    = 'jform.images';
			$_tableName  = 'categories';
			$_context    = 'images';
			$_recordId   = $table->get('id');
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = ArrayHelper::getValue($data, 'images', array(), 'array');

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		try
		{
			$_control    = 'jform.banners';
			$_tableName  = 'categories';
			$_context    = 'banners';
			$_recordId   = $table->get('id');
			$_extensions = array('jpg', 'png', 'jpeg', 'gif');
			$_options    = ArrayHelper::getValue($data, 'banners', array(), 'array');

			$this->helper->media->handleUploader($_control, $_tableName, $_context, $_recordId, $_extensions, $_options);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (isset($sellerCommission))
		{
			if ($table->get('type') == 'seller')
			{
				$this->helper->category->setSellerCommissionBySellerCategory($table->get('id'), $sellerCommission);
			}
			if (strpos($table->get('type'), 'product/') !== false)
			{
				$this->helper->category->setSellerCommissionByProductCategory($table->get('id'), $sellerCommission);
			}
		}

		// Save Translations if any
		$translations = isset($data['translations']) ? $data['translations'] : array();
		$this->helper->translation->saveTranslations($translations, $table->get('id'), 'sellacious_categories');

		// Trigger the onAfterSave event.
		$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));

		// now update concerned users' usergroups according to this category
		$this->helper->user->updateUsersGroupsByCategory($table->get('id'));

		// Rebuild the path for the category
		if (!$table->rebuildPath($table->get('id')))
		{
			$this->setError($table->getError());

			return false;
		}

		// Rebuild the paths of the category's children
		if (!$table->rebuild($table->get('id'), $table->lft, $table->level, $table->get('path')))
		{
			$this->setError($table->getError());

			return false;
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
	 * @since   1.0.0
	 */
	protected function preprocessData($context, &$data, $group = 'content')
	{
		// Get the dispatcher and load the plugins.
		$dispatcher = $this->helper->core->loadPlugins();

		if (is_array($data))
		{
			$cType = &$data['type'];
		}
		elseif (is_object($data))
		{
			$cType = &$data->type;
		}

		if (empty($cType))
		{
			$filterState = $this->app->getUserState('com_sellacious.categories.filter', array());
			$cType       = ArrayHelper::getValue($filterState, 'type');
		}

		// Trigger the data preparation event.
		$results = $dispatcher->trigger('onContentPrepareData', array($context, $data));

		// Check for errors encountered while preparing the data.
		if (count($results) > 0 && in_array(false, $results, true))
		{
			$this->setError($dispatcher->getError());
		}

		if (is_object($data))
		{
			// Only modify if object
			if ($cType == 'seller')
			{
				$rates = $this->helper->category->getSellerCommissionsBySellerCategory($data->id);

				$data->seller_commission = $rates;
			}
			elseif (strpos($cType, 'product/') !== false)
			{
				$rates = $this->helper->category->getSellerCommissionsByProductCategory($data->id);

				$data->seller_commission = $rates;
			}

			// Load Translations to form
			if ($data->id)
			{
				$data->translations = $this->helper->translation->getTranslations($data->id, 'sellacious_categories');
			}

			$data = ArrayHelper::fromObject($data);
		}
	}

	/**
	 * Override preprocessForm to load the sellacious plugin group instead of content.
	 *
	 * @param   JForm   $form   A form object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  Plugin Group
	 *
	 * @return  void
	 * @throws  Exception  If there is an error in the form event.
	 *
	 * @since   1.0.0
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'sellacious')
	{
		$obj = is_array($data) ? ArrayHelper::toObject($data) : $data;

		// prevent root item's parent change
		if (isset($obj->parent_id) && $obj->parent_id == 0 && $obj->id > 0)
		{
			$form->setFieldAttribute('parent_id', 'type', 'hidden');
			$form->setFieldAttribute('parent_id', 'hidden', 'true');
		}

		if (!empty($obj->id))
		{
			$form->setFieldAttribute('type', 'readonly', 'true');
		}

		// Check if a valid category type is selected, and extend form.
		$types = $this->helper->category->getTypes(true);

		if (!empty($obj->type) && array_key_exists($obj->type, $types))
		{
			$form->loadFile('category/' . $obj->type, false);

			if (strpos($obj->type, 'product/') === false || !$this->helper->config->get('product_compare'))
			{
				$form->removeField('compare');

				$form->loadFile('category/params', false);
			}

			if (strpos($obj->type, 'product/') !== false)
			{
				$form->loadFile('category/product');
			}

			if (!$this->helper->config->get('multi_seller'))
			{
				if ($obj->type == 'seller')
				{
					$form->removeField('seller_commission');
				}

				if (strpos($obj->type, 'product/') !== false)
				{
					$form->removeField('seller_commission');
				}

				if ($obj->type == 'staff')
				{
					$form->removeGroup('commission');
				}
			}

			if (strpos($obj->type, 'product/') === false)
			{
				$form->removeField('banners_on_product_listing', 'params');
				$form->removeField('banners');
			}

			if (strpos($obj->type, 'product/') !== false)
			{
				$handling = $this->helper->config->get('stock_management', 'product');

				if ($handling == 'global')
				{
					$form->removeField('stock_management', 'params');
					$form->removeField('stock_over_default', 'params');
					$form->removeField('stock_default', 'params');
					$form->removeField('frontend_stock_check', 'params');
				}
			}
		}

		// Show Translation fields
		$this->setTranslationFields($form);

		$form->setFieldAttribute('images', 'recordId', $obj->id);
		$form->setFieldAttribute('banners', 'recordId', $obj->id);

		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Method to set a category as default for its type.
	 *
	 * @param   int  $id  The primary key ID for the category.
	 *
	 * @return  boolean  True if successful.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function setDefault($id)
	{
		// Initialise variables.
		$db = $this->getDbo();

		$category = $this->getTable();

		if (!$category->load((int) $id))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CATEGORY_NOT_FOUND'));
		}

		// Detect disabled category
		if ($category->get('state') != 1)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_CATEGORY_DISABLED_CANNOT_SET_DEFAULT'));
		}

		// Reset the default fields for the category type.
		$query = $db->getQuery(true);

		$query->update('#__sellacious_categories')
			->set('is_default = 0')
			->where('type = ' . $db->q($category->get('type')));

		$db->setQuery($query);
		$db->execute();

		// Set the new default category.
		$category->set('is_default', 1);
		$category->store();
		$category->store();

		// Clean the cache.
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to translations fields to form
	 *
	 * @param   \JForm  $form  The category form
	 *
	 * @return  void
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function setTranslationFields(&$form)
	{
		$defLanguage = JFactory::getLanguage();
		$tag         = $defLanguage->getTag();
		$languages   = JLanguageHelper::getContentLanguages();

		$languages = array_filter($languages, function ($item) use ($tag){
			return ($item->lang_code != $tag);
		});

		if (!empty($languages))
		{
			$form->loadFile('category/translations', false);

			// Language Tabs
			$spacer = htmlentities('<div class="container">');

			foreach ($languages as $language)
			{
				$spacer .= htmlentities('<a class="btn btn-primary margin-right-5" href="#jform_translations_' . str_replace('-', '_', $language->lang_code) . '_language_title-lbl">' . '<img src="' . JUri::root() . 'media/mod_languages/images/'. $language->image . '.gif" alt="'. $language->image . '"> ') . $language->title . htmlentities('</a>');
			}

			$spacer .= htmlentities('</div>');

			$spacerElement = new SimpleXMLElement('
				<field type="spacer" name="language_tab" label="' . $spacer . '" />
			');

			$form->setField($spacerElement, 'translations', true, 'translations');

			// Language Translation fields
			foreach ($languages as $language)
			{
				$spacer = htmlentities('<b>') . $language->title . htmlentities('</b>');

				$element = new SimpleXMLElement('
				<fields name="' . $language->lang_code . '">
					<field type="spacer" name="language_title" label="' . $spacer . '" />
					<field
						name="title"
						type="text"
						label="COM_SELLACIOUS_CATEGORY_FIELD_TITLE_LABEL"
						description="COM_SELLACIOUS_CATEGORY_FIELD_TITLE_DESC"
						class="inputbox"
					/>
					<field
						name="description"
						type="editor"
						label="COM_SELLACIOUS_CATEGORY_FIELD_DESCRIPTION_LABEL"
						description="COM_SELLACIOUS_CATEGORY_FIELD_DESCRIPTION_DESC"
						rows="5"
						height="200"
						filter="safehtml"
						class="inputbox"
					/>
				</fields>');

				$form->setField($element, 'translations', true, 'translations');
			}
		}
		else
		{
			$form->removeGroup('translations');
		}
	}
}
