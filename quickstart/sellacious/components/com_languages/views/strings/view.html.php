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

/**
 * View for language overrides list.
 *
 * @since   1.6.0
 */
class LanguagesViewStrings extends SellaciousViewList
{
	/**
	 * Adds the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		if ($this->helper->access->check('core.admin', null, 'com_languages'))
		{
			JToolbarHelper::custom('strings.reindex', 'refresh.png', 'refresh.png', 'COM_LANGUAGES_TOOLBAR_BTN_REFRESH_INDEX', false);

			if ($this->state->get('list.language'))
			{
				if ($this->pagination->get('pagesTotal'))
				{
					JToolbarHelper::link('index.php?option=com_languages&view=strings&format=xlsx', 'COM_LANGUAGES_TOOLBAR_BTN_EXPORT_EXCEL', 'download');
				}

				JToolBarHelper::custom('', 'upload', 'upload', 'COM_LANGUAGES_STRINGS_IMPORT_TOOLBAR_LABEL', false);
				JToolbarHelper::custom('strings.autoTranslate', 'fa-language.png', 'fa-language.png', 'COM_LANGUAGES_TOOLBAR_BTN_TRANSLATE', true);
			}
		}
	}
}
