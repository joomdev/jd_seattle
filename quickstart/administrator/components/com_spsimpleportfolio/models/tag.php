<?php

/**
* @package     SP Simple Portfolio
*
* @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
* @license     GNU General Public License version 2 or later.
*/

defined('_JEXEC') or die();

class SpsimpleportfolioModelTag extends JModelAdmin {

	public function getTable($type = 'Tag', $prefix = 'SpsimpleportfolioTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_spsimpleportfolio.tag', 'tag', array( 'control' => 'jform', 'load_data' => $loadData ) );

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState( 'com_spsimpleportfolio.edit.tag.data', array() );

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}
}
