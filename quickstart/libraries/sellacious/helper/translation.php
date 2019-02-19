<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious helper
 *
 * @since   1.6.0
 */
class SellaciousHelperTranslation extends SellaciousHelperBase
{
	/**
	 * Get translated record
	 *
	 * @param   stdClass  $record    The reference record object
	 * @param   string    $refTable  The reference table name
	 * @param   string    $lang      The language tag
	 *
	 * @since   1.6.0
	 */
	public function translateRecord($record, $refTable, $lang = null)
	{
		if (is_object($record))
		{
			try
			{
				$lang        = $lang ?: JFactory::getLanguage()->getTag();
				$translation = $this->getTranslations($record->id, $refTable, $lang);

				// Todo: Remove hardcoded reference to 'title' and 'description' fields
				if (isset($translation[$lang]))
				{
					if (isset($record->title) && isset($translation[$lang]['title']))
					{
						$record->title = $translation[$lang]['title'];
					}

					if (isset($record->description) && isset($translation[$lang]['description']))
					{
						$record->description = $translation[$lang]['description'];
					}
				}
			}
			catch (Exception $e)
			{
				// Skip translation silently on an error
			}
		}
	}

	/**
	 * Get translated value
	 *
	 * @param   string $refId    The reference record id
	 * @param   string $refTable The reference table name
	 * @param   string $refField The reference field name
	 * @param   string $value    The value to be translated
	 * @param   string $lang     The language tag
	 *
	 * @since   1.6.0
	 */
	public function translateValue($refId, $refTable, $refField, &$value, $lang = null)
	{
		try
		{
			$lang        = $lang ?: JFactory::getLanguage()->getTag();
			$translation = $this->getTranslations($refId, $refTable, $lang);

			if (isset($translation[$lang]) && isset($translation[$lang][$refField]))
			{
				$value = $translation[$lang][$refField];
			}
		}
		catch (Exception $e)
		{
			// Skip translation silently on an error
		}
	}

	/**
	 * Method to get translations
	 *
	 * @param   int              $id         The reference id
	 * @param   string           $refTable   The reference table
	 * @param   string|string[]  $languages  The language tag or an array of tags
	 *
	 * @return  array
	 *
	 * @since   1.6.0
	 */
	public function getTranslations($id, $refTable, $languages = null)
	{
		$translations = array();

		if (!$languages)
		{
			$languages = JLanguageHelper::getContentLanguages();
			$languages = ArrayHelper::getColumn($languages, 'lang_code');
		}

		$filter = array(
			'list.select'     => 'a.language_code, a.reference_field, a.value',
			'list.from'       => '#__sellacious_translations',
			'language_code'   => $languages,
			'reference_table' => $refTable,
			'reference_id'    => (int) $id,
			'state'           => 1,
		);

		$values = $this->loadObjectList($filter);

		foreach ($values as $record)
		{
			$translations[$record->language_code][$record->reference_field] = $record->value;
		}

		return $translations;
	}

	/**
	 * Method to save translations
	 *
	 * @param   array   $data      The translations data
	 * @param   int     $id        The reference id
	 * @param   string  $refTable  The reference table name
	 *
	 * @return  bool
	 *
	 * @since   1.6.0
	 */
	public function saveTranslations($data, $id, $refTable)
	{
		if (empty($data))
		{
			return true;
		}

		foreach ($data as $lang => $translations)
		{
			foreach ($translations as $field => $translation)
			{
				$table = $this->getTable('Translation', 'SellaciousTable');
				$data  = array();

				$data['language_code']   = $lang;
				$data['reference_id']    = $id;
				$data['reference_table'] = $refTable;
				$data['reference_field'] = $field;

				$table->load($data);

				$data['value'] = $translation;

				if (!$table->get('id'))
				{
					$data['state'] = 1;
				}

				if (empty($translation))
				{
					// If translation empty, then remove existing translation record
					if ($table->get('id'))
					{
						$table->delete($table->get('id'));
					}

					// Do not save empty translation
					continue;
				}

				$table->bind($data);

				$table->check();

				$table->store();
			}
		}

		return true;
	}
}
