<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

JHtml::_('jquery.framework');

JHtml::_('script', 'media/com_sellacious/js/plugin/cookie/jquery.cookie.js', array('version' => S_VERSION_CORE));
JHtml::_('script', 'com_sellacious/view.permissions.js', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.component.css', array('version' => S_VERSION_CORE, 'relative' => true));
JHtml::_('stylesheet', 'com_sellacious/view.permissions.css', array('version' => S_VERSION_CORE, 'relative' => true));

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

JFactory::getDocument()->addStyleDeclaration('.select-small { width:100%; max-width:350px; }');

echo JLayoutHelper::render('com_sellacious.view.edittabs', $data, '', $options);
