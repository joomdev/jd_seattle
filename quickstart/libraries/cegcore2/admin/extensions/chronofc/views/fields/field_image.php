<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" value="img" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][tag]">

<div class="field">
	<label><?php el('Source url'); ?></label>
	<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][src]">
</div>

<div class="field">
	<label><?php el('Extra attributes'); ?></label>
	<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][attrs]" rows="3"></textarea>
</div>