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

JHtml::_('jquery.framework');
JHtml::_('script', 'com_languages/view.language.js', array('version' => S_VERSION_CORE, 'relative' => true));

echo JLayoutHelper::render('com_sellacious.view.edit', $data, '', $options);
