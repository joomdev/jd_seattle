<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui container">
	<div class="ui header"><?php el('Install a new view or function'); ?></div>
	<form action="<?php echo r2('index.php?ext=chronoforms&act=install_feature'); ?>" method="post" name="admin_form" id="admin_form" enctype="multipart/form-data" class="ui form">
		
		<div class="field">
			<label><?php el('Select the feature file, files .zip, file name should include the feature type, e.g: function.my_func.zip or view.my_view.zip'); ?></label>
			<input type="file" name="upload" value="">
		</div>
		
		
		<button class="compact ui button green icon labeled">
		<i class="checkmark icon"></i>Install
		</button>
	</form>
</div>