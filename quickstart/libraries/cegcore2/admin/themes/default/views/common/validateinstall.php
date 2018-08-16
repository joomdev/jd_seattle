<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui container fluid">
	<h1 class="ui header dividing"><?php el('Validate your installation'); ?></h1>
	<form action="<?php echo r2('index.php?ext='.$this->get('ext_name').'&act=validateinstall'); ?>" method="post" name="admin_form" id="admin_form" class="ui form">
		<div class="field">
			<label>Domain name detected: <input type="text" readonly value="<?php echo $domain; ?>">
			<input type="hidden" name="domain_name" value="<?php echo $domain; ?>">
		</div>
		
		<div class="ui grid two columns segment divided">
			<div class="column">
				<h2 class="ui header blue dividing"><?php el('Elastic'); ?></h2>
				<div class="field">
					<label><?php el('Order number'); ?></label>
					<input type="text" name="order_number" value="">
					<small><?php el('Your 26 digits order number received by email after purchasing'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Days'); ?></label>
					<input type="text" name="days" value="365">
					<small><?php el('The number of days you need to add to this validation, or enter 0 to load existing validation.'); ?></small>
				</div>
			</div>
			
			<div class="column">
				<h2 class="ui header orange dividing"><?php el('Classic'); ?></h2>
				<div class="field">
					<label><?php el('Validation key'); ?></label>
					<input type="text" name="license_key" value="">
					<small><?php el('Your validation key generated on ChronoEngine.com'); ?></small>
				</div>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Serial number (optional)'); ?></label>
			<input type="text" name="serial_number" value="">
		</div>
		
		<button class="compact ui button green icon labeled">
			<i class="checkmark icon"></i><?php el('Validate'); ?>
		</button>
		
		<button class="compact ui button yellow icon labeled" name="trial">
			<i class="clock icon"></i><?php el('Activate one time trial validation'); ?>
		</button>
		
		<a class="ui button blue compact" target="_blank" href="https://www.chronoengine.com/purchase">
			<i class="external icon"></i><?php el('Purchase Now'); ?>
		</a>
	</form>
</div>