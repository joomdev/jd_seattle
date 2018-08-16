<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" value="site" name="Connection[conditions][<?php echo $n; ?>][type]">
<input type="hidden" value="area" name="Connection[conditions][<?php echo $n; ?>][key]">

<div class="ui segment active" data-tab="conditions-<?php echo $n; ?>">
	
	<div class="field">
		<label><?php el('Site area'); ?></label>
		<select name="Connection[conditions][<?php echo $n; ?>][value]" class="ui fluid dropdown">
			<option value="front"><?php el('Front end'); ?></option>
			<option value="admin"><?php el('Administrator'); ?></option>
		</select>
	</div>
	
</div>