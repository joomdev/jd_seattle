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

/** @var  array  $displayData */
$split = (bool) $displayData['split'];
$label = $displayData['label'];
?>
<button type="button" class="btn btn-default dropdown-toggle <?php echo $split ? 'dropdown-toggle-split' : '' ?>"
		data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	<?php if ($split): ?>
		<span class="sr-only">Toggle Dropdown</span> <i class="fa fa-caret-down"></i>
	<?php else: ?>
		<?php echo $label ?> &nbsp;<i class="fa fa-caret-down"></i>
	<?php endif; ?>
</button>
