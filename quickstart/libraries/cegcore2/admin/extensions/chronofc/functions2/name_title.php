<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" value="" name="Connection[functions][<?php echo $n; ?>][id]">
<div class="field">
	<label><?php el('Name'); ?></label>
	<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
</div>

<div class="field">
	<label><?php el('Title'); ?></label>
	<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][title]">
</div>