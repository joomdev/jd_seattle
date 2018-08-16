<?php

/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.application.component.helper');

class SpsimpleportfolioControllerItem extends JControllerForm {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

	protected function allowEdit($data = array(), $key = 'id') {
		$id = isset( $data[ $key ] ) ? $data[ $key ] : 0;
		if( !empty( $id ) ) {
			return JFactory::getUser()->authorise( "core.edit", "com_spsimpleportfolio.item." . $id );
		}
	}

	protected function postSaveHook(JModelLegacy $model, $validData = array()) {

		$item = $model->getItem();
		$image = JPATH_ROOT . '/' . $item->image;
		$alias = $item->alias;
		$folder = JPATH_ROOT . '/images/spsimpleportfolio/' . $alias;
		$base_name = JFile::stripExt(basename($item->image));
		$ext = JFile::getExt($image);

		$params = JComponentHelper::getParams('com_spsimpleportfolio');
		$sizes = array();

		// Square
		$square = strtolower($params->get('square', '600x600'));
		$squareArray = explode('x', $square);
		$sizes[$base_name . '_' .$square] = array($squareArray[0], $squareArray[1]);

		// Rectangle
		$rectangle = strtolower($params->get('rectangle', '600x400'));
		$rectangleArray = explode('x', $rectangle);
		$sizes[$base_name . '_' .$rectangle] = array($rectangleArray[0], $rectangleArray[1]);

		// Tower
		$tower = strtolower($params->get('tower', '600x800'));
		$towerArray = explode('x', $tower);
		$sizes[$base_name . '_' .$tower] = array($towerArray[0], $towerArray[1]);

		if(JFile::exists($image)) {
			if(!JFolder::exists($folder)) {
				JFolder::create($folder, 0755);
			}
			SpsimpleportfolioHelper::createThumbs($image, $sizes, $folder, '', $ext);
		}

		return true;
	}
}
