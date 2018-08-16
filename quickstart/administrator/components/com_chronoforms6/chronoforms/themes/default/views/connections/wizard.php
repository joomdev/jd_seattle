<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>

<form action="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=wizard'); ?>" method="post" name="admin_form" id="admin_form" enctype="multipart/form-data" class="ui form">
	
	<h2 class="ui header">
		<i class="settings icon"></i>
		<div class="content"><?php el('Form wizard'); ?>
		<div class="sub header">Select your form features...</div>
		</div>
	</h2>
	
	<div class="ui clearing divider"></div>
	
	<div class="ui bottom attached tab segment active" data-tab="general">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Title'); ?></label>
				<input type="text" placeholder="<?php el('Title'); ?>" name="Wizard[title]">
			</div>
			<div class="field">
				<label><?php el('Alias'); ?></label>
				<input type="text" placeholder="<?php el('Alias'); ?>" name="Wizard[alias]">
				<small style="color:red;"><?php el('Use this alias to call your form in URLs or shortcodes.'); ?></small>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="grouped fields">
			<label for="fruit"><?php el('How should your form look like ?'); ?></label>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[design]" value="design" tabindex="0" class="hidden">
					<label><?php el('I would like to build my form using the drag and drop designer.'); ?></label>
				</div>
			</div>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[design]" value="html" tabindex="0" class="hidden">
					<label><?php el('I have my form HTML code I want to use.'); ?></label>
				</div>
			</div>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[design]" value="copy" tabindex="0" class="hidden">
					<label><?php el('I would like to copy the elements from another form I already have.'); ?></label>
				</div>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="grouped fields">
			<label for="fruit"><?php el('How would you like to protect your form ?'); ?></label>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[spam]" value="fields" tabindex="0" class="hidden">
					<label><?php el('Use the basic fields validations.'); ?></label>
				</div>
			</div>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[spam]" value="google" tabindex="0" class="hidden">
					<label><?php el('Protect my forms using Google reCaptcha - this option requires Google reCaptcha keys.'); ?></label>
				</div>
			</div>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[spam]" value="none" tabindex="0" class="hidden">
					<label><?php el('I do not need any protection.'); ?></label>
				</div>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="grouped fields">
			<label for="fruit"><?php el('Would you like to load the form with some inital data from a database table ?'); ?></label>
			<div class="field">
				<div class="ui radio checkbox">
					<input type="radio" name="Wizard[dbread]" value="1" tabindex="0" class="hidden">
					<label><?php el('Yes'); ?></label>
				</div>
				<div class="ui radio checkbox">
					<input type="radio" checked="checked" name="Wizard[dbread]" value="0" tabindex="0" class="hidden">
					<label><?php el('No'); ?></label>
				</div>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[pages]" value="1">
				<label><?php el('I would like to have a multi page form.'); ?></label>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[email]" value="1">
				<label><?php el('I would like my form to email the data entered.'); ?></label>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[storage]" value="1">
				<label><?php el('I would like my form to store the data entered in the database.'); ?></label>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[message]" value="1">
				<label><?php el('I would like to display a confirmation message to the user after the form is sent.'); ?></label>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[email]" value="1">
				<label><?php el('I would like my form to store the data entered in the database.'); ?></label>
			</div>
		</div>
		<div class="ui divider"></div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Wizard[redirect]" value="1">
				<label><?php el('I would like to redirect the user to another page after the form is sent.'); ?></label>
			</div>
		</div>
		
	</div>
	
</form>
