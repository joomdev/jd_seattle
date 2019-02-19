<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Report list controller class.
 *
 * @since  1.6.0
 */
class SellaciousreportingControllerSreports extends SellaciousControllerAdmin
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6.0
	 */
	public function getModel($name = 'Report', $prefix = 'SellaciousreportingModel', $config = null)
	{
		return parent::getModel($name, $prefix, $config);
	}
}
