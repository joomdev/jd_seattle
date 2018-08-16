<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<input type="hidden" value="request_param" name="Connection[conditions][<?php echo $n; ?>][type]">

<div class="ui segment active" data-tab="conditions-<?php echo $n; ?>">
	
	<div class="field">
		<div class="ui checkbox toggle">
			<input type="hidden" name="Connection[conditions][<?php echo $n; ?>][not]" data-ghost="1" value="0">
			<input type="checkbox" class="hidden" name="Connection[conditions][<?php echo $n; ?>][not]" value="1">
			<label><?php el('Inverse'); ?></label>
		</div>
	</div>
	
	<div class="two fields">
		
		<div class="field">
			<label><?php el('Parameter name'); ?></label>
			<input type="text" name="Connection[conditions][<?php echo $n; ?>][key]">
		</div>
		
		<div class="field">
			<label><?php el('Parameter value'); ?></label>
			<input type="text" name="Connection[conditions][<?php echo $n; ?>][value]">
		</div>
		
	</div>
	
</div>