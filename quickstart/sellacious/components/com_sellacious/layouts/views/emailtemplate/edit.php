<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JHtml::_('script', 'com_sellacious/view.emailtemplate.js', array('version' => S_VERSION_CORE, 'relative' => true));

JText::script('COM_SELLACIOUS_EMAILTEMPLATE_ACTUAL_RECIPIENTS_OR_ALTERNATE_REQUIRED_WARNING');

$data = array(
	'name'  => $this->getName(),
	'state' => $this->state,
	'item'  => $this->item,
	'form'  => $this->form,
);

$options = array(
	'client' => 2,
	'debug'  => 0,
);

echo JLayoutHelper::render('com_sellacious.view.edittabs', $data, '', $options);
