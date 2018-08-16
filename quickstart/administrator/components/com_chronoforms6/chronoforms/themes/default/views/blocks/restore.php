<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext=chronoforms&cont=blocks&act=restore'); ?>" method="post" name="admin_form" id="admin_form" enctype="multipart/form-data" class="ui form">
	
	<h2 class="ui header"><?php el('Restore a blocks backup file'); ?></h2>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		
		<div class="grouped two fields">
			<div class="field">
				<label><?php el('File'); ?></label>
				<input type="file" name="backup">
			</div>
			<div class="field">
				<button class="compact ui button green icon labeled" name="restore"><i class="checkmark icon"></i><?php el('Upload & Restore'); ?></button>
			</div>
		</div>
		
	</div>
	
</form>
