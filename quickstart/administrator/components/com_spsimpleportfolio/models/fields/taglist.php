<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('JPATH_PLATFORM') or die;

class JFormFieldTaglist extends JFormField {

	public $type = 'Taglist';

	protected function getInput() {

		$doc = JFactory::getDocument();
		$doc->addScript(JURI::base(true) . '/components/com_spsimpleportfolio/assets/js/tags.js');

		$html = array();
		$attr = '';
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		$options = $this->getTags();

		$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

		return implode($html);
	}

	private function getTags() {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.id AS value, a.title AS text')
			->from('#__spsimpleportfolio_tags AS a');

		$query->order('a.id ASC');
		$db->setQuery($query);
		$options = $db->loadObjectList();

		return $options;
	}

}
