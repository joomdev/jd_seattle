<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  stdClass   $displayData */
/** @var  stdClass[] $methods */
$methods = $displayData->methods;
?>
<div id="payment-methods">
	<?php
	if (count($methods))
	{
		$active = reset($methods);

		echo JHtml::_('bootstrap.startAccordion', 'payment_accordion', array('parent' => true, 'toggle' => false, 'active' => 'tab' . $active->id));

		foreach ($methods as $i => $method)
		{
			if (isset($method->form) && $method->form instanceof JForm)
			{
				echo JHtml::_('bootstrap.addSlide', 'payment_accordion', $method->title, 'tab' . $method->id, 'panel');

				// A plugin may need additional script, logic, validation etc. In such case they may require layout override.
				$override = isset($method->layout) && is_file($method->layout);
				$file     = $override ? basename($method->layout, '.php') : 'com_sellacious.payment.form';
				$dir      = $override ? dirname($method->layout) : '';

				echo JLayoutHelper::render($file, $method, $dir);

				echo JHtml::_('bootstrap.endSlide');
			}
		}

		echo JHtml::_('bootstrap.endAccordion');
	}
	else
	{
		echo '<div class="center">';
		echo JText::_('COM_SELLACIOUS_CART_PAYMENT_METHOD_NOT_AVAILABLE');
		echo '</div>';
	}
	?>
</div>
