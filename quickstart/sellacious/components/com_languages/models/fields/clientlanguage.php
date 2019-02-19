<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Sellacious\Language\LanguageHelper;

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Supports a list of installed application languages
 *
 * @see    JFormFieldContentLanguage for a select list of content languages.
 * @since  11.1
 */
class JFormFieldClientLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'ClientLanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$languages = array();
		$clients   = JApplicationHelper::getClientInfo();

		foreach ($clients as $client)
		{
			$langList = LanguageHelper::createLanguageList($this->value, null, $client->path);

			foreach ($langList as $item)
			{
				$value = $item['value'];

				if (!isset($languages[$value]))
				{
					$languages[$value] = $item;
				}
			}
		}

		// Make sure the languages are sorted base on locale instead of random sorting
		if (count($languages) > 1)
		{
			usort(
				$languages,
				function ($a, $b)
				{
					return strcmp($a['value'], $b['value']);
				}
			);
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(
			parent::getOptions(),
			$languages
		);

		// Set the default value active language
		if ($langParams = JComponentHelper::getParams('com_languages'))
		{
			switch ((string) $this->value)
			{
				case 'site':
				case 'frontend':
				case '0':
					$this->value = $langParams->get('site', 'en-GB');
					break;
				case 'admin':
				case 'administrator':
				case 'backend':
				case '1':
					$this->value = $langParams->get('administrator', 'en-GB');
					break;
				case 'active':
				case 'auto':
					$lang = JFactory::getLanguage();
					$this->value = $lang->getTag();
					break;
				default:
				break;
			}
		}

		return $options;
	}
}
