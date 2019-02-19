<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var stdClass[] $displayData */
foreach ($displayData as $i => $address)
{
	$body    = JLayoutHelper::render('com_sellaciousopc.user.address.form', $address);
	$options = array(
		'title'    => JText::_('COM_SELLACIOUSOPC_CART_USER_ADDRESS_FORM_EDIT_TITLE'),
		'backdrop' => 'static',
		'size'     => 'xs',
		'footer'   => '<button type="button" class="btn btn-primary btn-save-address"><i class="fa fa-save"></i> ' . JText::_('COM_SELLACIOUSOPC_PRODUCT_UPDATE') . '</button>',
	);

	echo JHtml::_('bootstrap.renderModal', 'address-form-' . (int) $address->id, $options, $body);
}
