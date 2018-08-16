<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext='.$this->get('ext_name').'&cont=languages'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
	
	<h2 class="ui header"><?php el('Languages manager'); ?></h2>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		<div class="grouped two fields">
			<div class="field">
				<label><?php el('Language'); ?></label>
				<input type="text" placeholder="<?php el('The language tag, e.g: en_GB or fr_FR'); ?>" name="lang">
			</div>
			<div class="field">
				<button class="compact ui button green icon labeled" name="build"><i class="checkmark icon"></i><?php el('Build'); ?></button>
			</div>
		</div>
		
		<?php if(!empty($this->data['language_strings'])): ?>
			<h2 class="ui header dividing">
				<?php el('Update and save'); ?>
				<div class="sub header"><?php el('You can update any of the language strings below then save.'); ?></div>
			</h2>
			<div class="field">
			<button class="compact ui button blue icon labeled" name="save"><i class="checkmark icon"></i><?php el('Save as custom'); ?></button>
			<button class="compact ui button purple icon labeled" name="update"><i class="warning icon"></i><?php el('Update existing file'); ?></button>
			</div>
			<div class="field">
				<label><?php el('Language strings'); ?></label>
				<textarea name="language_strings" rows="30"></textarea>
			</div>
		<?php endif; ?>
		
	</div>
	
</form>
