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
/**
 * @var   string  $code      The product code to access
 * @var   string  $label     The label for the button
 * @var   string  $class     The button class attribute
 * @var   bool    $checkout  Whether this is a buy now action. False = Add to cart, True = Buy Now
 */
?>
<button type="button" class="btn-content-cart <?php echo $class ?>"
        data-item="<?php echo $code ?>" <?php echo $checkout ? 'data-checkout="true"' : '' ?>><?php
	echo htmlspecialchars($label, ENT_COMPAT, 'UTF-8'); ?></button>
