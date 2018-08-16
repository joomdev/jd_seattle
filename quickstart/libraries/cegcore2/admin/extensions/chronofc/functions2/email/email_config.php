<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-template"><?php el('Auto template'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-encryption"><?php el('Encryption'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="email" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="field forms_conf easy_disabled">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][label]">
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" value="1">
					<label><?php el('Enabled'); ?></label>
				</div>
			</div>
			
			<div class="field required">
				<label><?php el('Recipients list'); ?></label>
				<input type="text" placeholder="<?php el('comma separated, no spaces'); ?>" value="" name="Connection[functions][<?php echo $n; ?>][recipients]">
				<small><?php el('List of email addresses or chrono commands to provide the addresses where you will receive the email, example: you@domain.com,support@domain.com'); ?></small>
			</div>
			
			<div class="field required">
				<label><?php el('Subject'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][subject]">
				<small><?php el('Email subject is required.'); ?></small>
			</div>
			
			<div class="field forms_conf">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][autoemail]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][autoemail]" value="1" <?php if($this->extension == 'chronoforms' && is_null($this->data('Connection.functions.'.$n.'.type'))): ?>checked<?php endif; ?>>
					<label><?php el('Auto add fields data'); ?></label>
					<small><?php el('Auto append email enabled form fields labels and values to the email body, or use {AUTO_FIELDS} where you want the data to be placed.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Body'); ?>
				<i class="icon green write circular" onclick="jQuery.G2.tinymce.init('#email_editor<?php echo $n; ?>');" data-hint="<?php el('Enable WYSIWYG editor'); ?>"></i>
				<i class="icon red cancel circular" onclick="jQuery.G2.tinymce.remove('#email_editor<?php echo $n; ?>');" data-hint="<?php el('Disable WYSIWYG editor'); ?>"></i>
				</label>
				<textarea placeholder="" name="Connection[functions][<?php echo $n; ?>][body]" rows="10" data-editor="0" data-editormode="email" data-eheight="400" id="email_editor<?php echo $n; ?>"></textarea>
				<small><?php el('You can use HTML, text and chrono commands, e.g: {data:field_name}'); ?></small>
			</div>
			
			<div class="two fields easy_disabled">
				<div class="field">
					<label><?php el('Send as'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][mode]" class="ui fluid dropdown">
						<option value="html"><?php el('HTML'); ?></option>
						<option value="text"><?php el('Text'); ?></option>
					</select>
				</div>
			</div>
			
			<div class="field easy_disabled">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][advanced_template]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][advanced_template]" value="1">
					<label><?php el('Apply advanced template parsing and styles'); ?></label>
				</div>
			</div>
			
			<div class="ui header dividing small easy_disabled"><?php el('Attachments settings'); ?></div>
			<div class="field forms_conf easy_disabled">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" data-ghost="1" value="">
					<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][autofields]" value="1" <?php if($this->extension == 'chronoforms' && is_null($this->data('Connection.functions.'.$n.'.type'))): ?>checked<?php endif; ?>>
					<label><?php el('Auto Attach file fields'); ?></label>
					<small><?php el('Auto attach attachment enabled fields to this email ?'); ?></small>
				</div>
			</div>
			
			<div class="field easy_disabled easy_disabled">
				<label><?php el('Attachments list'); ?></label>
				<textarea placeholder="<?php el('Multi line, full file path or a var call'); ?>" name="Connection[functions][<?php echo $n; ?>][attachments]" rows="3"></textarea>
			</div>
			
			<div class="ui header dividing small easy_disabled"><?php el('Send settings'); ?></div>
			<div class="two fields easy_disabled">
				
				<div class="field">
					<label><?php el('Reply email'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][reply_email]">
				</div>
				
				<div class="field">
					<label><?php el('Reply name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][reply_name]">
				</div>
				
			</div>
			
			<div class="two fields easy_disabled">
				
				<div class="field private_config">
					<label><?php el('From email'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][from_email]">
					<small style="color:red;"><?php el('If left empty then the site from email will be used.'); ?></small>
				</div>
				
				<div class="field">
					<label><?php el('From name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][from_name]">
					<small style="color:red;"><?php el('If left empty then the site from name will be used.'); ?></small>
				</div>
				
			</div>
			
			<div class="two fields easy_disabled">
				
				<div class="field">
					<label><?php el('CC'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][cc]">
				</div>
				
				<div class="field">
					<label><?php el('BCC'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][bcc]">
				</div>
				
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-template">
		<div class="field">
			<label><?php el('Header'); ?></label>
			<textarea name="Connection[functions][<?php echo $n; ?>][template][header]" rows="3"></textarea>
			<small><?php el('The header code used in the auto generated template'); ?></small>
		</div>
		<div class="field">
			<label><?php el('Body'); ?></label>
			<textarea name="Connection[functions][<?php echo $n; ?>][template][body]" rows="5"></textarea>
			<small><?php el('The body code used in the auto generated template, use {label} and {value} as placeholders for field label and value.'); ?></small>
		</div>
		<div class="field">
			<label><?php el('Footer'); ?></label>
			<textarea name="Connection[functions][<?php echo $n; ?>][template][footer]" rows="3"></textarea>
			<small><?php el('The footer code used in the auto generated template'); ?></small>
		</div>
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-encryption">
		<?php if(!class_exists('Crypt_GPG')): ?>
		<div class="ui message red"><?php el('The Crypt_GPG class is not loaded'); ?></div>
		<?php endif; ?>
		<div class="ui checkbox toggle">
			<input type="hidden" name="Connection[functions][<?php echo $n; ?>][encrypted]" data-ghost="1" value="">
			<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][encrypted]" value="1">
			<label><?php el('Encrypt the email content'); ?></label>
			<small><?php el('You must have the Crypt GPG class loaded on your server.'); ?></small>
		</div>
		<div class="field">
			<label><?php el('GPG security key'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][gpg_sec_key]">
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[functions]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>