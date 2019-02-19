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

/**
 * Templates list controller class.
 *
 * @since   1.6.1
 */
class ImporterControllerImports extends SellaciousControllerAdmin
{
	/**
	 * @var	  string  The prefix to use with controller messages.
	 *
	 * @since	1.6.1
	 */
	protected $text_prefix = 'COM_IMPORTER_IMPORTS';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since	1.6.1
	 */
	public function getModel($name = 'Import', $prefix = 'ImporterModel', $config = null)
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
}
