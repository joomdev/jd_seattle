<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="four wide field">
		<label><?php el('Tag'); ?></label>
		<input type="text" value="div" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][tag]">
	</div>
	<div class="twelve wide field">
		<label><?php el('Class'); ?></label>
		<input type="text" value="ui header dividing" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][class]">
	</div>
</div>

<div class="field">
	<label><?php el('Content'); ?></label>
	<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][content]">
</div>

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Extra attributes'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][attrs]" rows="3"></textarea>
			</div>
		</div>

	</div>
</div>