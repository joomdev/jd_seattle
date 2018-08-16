<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" value="<?php echo $page['Page']['id']; ?>" name="Page[<?php echo $pn; ?>][id]">
<div class="field">
	<label><?php el('Name'); ?></label>
	<input type="text" value="<?php echo $page['Page']['name']; ?>" name="Page[<?php echo $pn; ?>][name]">
</div>

<div class="field">
	<label><?php el('Title'); ?></label>
	<input type="text" value="<?php echo $page['Page']['title']; ?>" name="Page[<?php echo $pn; ?>][title]">
</div>