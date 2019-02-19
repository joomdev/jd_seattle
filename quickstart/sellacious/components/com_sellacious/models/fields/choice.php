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

JFormHelper::loadFieldClass('Checkboxes');

class JFormFieldChoice extends JFormFieldCheckboxes
{
	/**
	 * The field type.
	 *
	 * @var  string
	 */
	protected $type = 'Choice';

	protected $layout = 'joomla.formfield.choice.input';

}
