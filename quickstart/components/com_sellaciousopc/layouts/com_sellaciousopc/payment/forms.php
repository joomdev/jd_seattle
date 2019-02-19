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

/** @var  stdClass   $displayData */
/** @var  stdClass[] $methods */
/** @var  Sellacious\Cart $cart */
$methods  = $displayData->methods;
$cart 	  = $displayData->cart;
$selected = $cart->getParam('selected_payment_id', 0);
?>
<div id="payment-methods">
	<?php
	if (count($methods))
	{
		$active = array_filter($methods, function ($item) use ($selected){
			return $item->id == $selected;
		});

		if (empty($active))
		{
			$active = reset($methods);
		}
		else
		{
			$active = reset($active);
		}

		foreach ($methods as $i => $method)
		{
			if (isset($method->form) && $method->form instanceof JForm)
			{
				$checked = $active->id == $method->id ? "checked" : "";
				$method->checked = $checked ? "" : "hidden";

				echo "<div class=\"clearfix\"></div>";
				echo "<label class=\"radio\">";
				echo "<input name=\"payment\" class=\"select-payment\" type=\"radio\" value=\"".$method->id."\" ".$checked.">";
				echo "<span class=\"payment-title\">".$method->title."</span>";
				echo "</label>";

				// A plugin may need additional script, logic, validation etc. In such case they may require layout override.
				$override = isset($method->layout) && is_file($method->layout);
				$file     = $override ? basename($method->layout, '.php') : 'com_sellaciousopc.payment.form';
				$dir      = $override ? dirname($method->layout) : '';

				echo JLayoutHelper::render($file, $method, $dir);
			}
		}
	}
	else
	{
		echo '<div class="center">';
		echo JText::_('COM_SELLACIOUSOPC_CART_PAYMENT_METHOD_NOT_AVAILABLE');
		echo '</div>';
	}
	?>
</div>
