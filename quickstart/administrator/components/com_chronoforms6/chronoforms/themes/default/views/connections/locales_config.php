<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab locale-tab <?php if($name == 'en_GB'): ?>active<?php endif; ?>" data-tab="locale-<?php echo $name; ?>">
	
	<div class="two fields">
		<div class="field">
			<label><?php el('Name'); ?></label>
			<input type="text" value="<?php echo $name; ?>" name="Connection[locales][<?php echo $name; ?>][name]" readonly="true">
		</div>
	</div>
	
	<div class="field">
		<label><?php el('Content'); ?></label>
		<textarea placeholder="<?php el('Multiline list of locale_string=translation'); ?>" name="Connection[locales][<?php echo $name; ?>][content]" rows="20"></textarea>
	</div>
	
</div>