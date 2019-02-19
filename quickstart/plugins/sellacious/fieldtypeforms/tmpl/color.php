<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

/** @var  object  $field */
$color = preg_match('/([a-f0-9]{6})/i', $field->value, $matches) ? $matches[1] : 'ffffff';
?>
<button type="button" style="background: <?php echo '#' . $color ?>; width: 24px; height: 22px; border-radius: 2px; #e6e6e6;"></button>
