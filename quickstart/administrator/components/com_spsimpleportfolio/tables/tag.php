<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

class SpsimpleportfolioTableTag extends JTable {

	public function __construct(&$db) {
		parent::__construct('#__spsimpleportfolio_tags', 'id', $db);
	}

	public function store($updateNulls = false) {

		// Verify that the alias is unique
		$table = JTable::getInstance('Tag', 'SpsimpleportfolioTable');
		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0)){
			$this->setError(JText::_('COM_SPSIMPLEPORTFOLIO_ERROR_UNIQUE_ALIAS'));
			return false;
		}

		return parent::store($updateNulls);
	}

	public function check() {
		// Check for valid name.
		if (trim($this->title) == '') {
			throw new UnexpectedValueException(sprintf('The title is empty'));
		}

		if (empty($this->alias)) {
			$this->alias = $this->title;
		}

		$this->alias = JApplicationHelper::stringURLSafe($this->alias, $this->language);

		if (trim(str_replace('-', '', $this->alias)) == '') {
			$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s');
		}

		return true;

	}
}
