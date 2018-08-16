<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-modal"><?php el('Popup'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="display_section" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				<div class="field required">
					<label><?php el('Sections names'); ?></label>
					<textarea rows="3" name="Connection[functions][<?php echo $n; ?>][sections]">one</textarea>
				</div>
				
				<div class="field">
					<label><?php el('Container type'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][display_type]" class="ui fluid dropdown">
						<option value="form"><?php el('Form'); ?></option>
						<option value="div"><?php el('Div .ui.form'); ?></option>
						<option value="pure"><?php el('None'); ?></option>
					</select>
					<small><?php el('In all cases this should be set to "Form", unless you do not want a form element added, for example when loading dynamic content into an existing form.'); ?></small>
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Form settings'); ?></div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][data_provider]">
				</div>
				<div class="field">
					<label><?php el('Validation messages'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][validation][type]" class="ui fluid dropdown">
						<option value="inline"><?php el('Inline tooltips'); ?></option>
						<option value="inlinetext"><?php el('Inline error messages'); ?></option>
						<option value="message"><?php el('Errors list below form'); ?></option>
					</select>
				</div>
			</div>
			
			<div class="two fields">
				<div class="four wide field">
					<label><?php el('Event'); ?></label>
					<input type="text" value="submit" name="Connection[functions][<?php echo $n; ?>][event]">
				</div>
				
				<div class="twelve wide field">
					<label><?php el('Action URL and/or parameters'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][parameters]">
				</div>
			</div>
			
			<div class="three fields">
				
				<div class="field">
					<label><?php el('AJAX submit'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][dynamic]" class="ui fluid dropdown">
						<option value=""><?php el('No'); ?></option>
						<option value="1"><?php el('Yes'); ?></option>
					</select>
				</div>
				
				<div class="field">
					<label><?php el('Invisible form'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][invisible]" class="ui fluid dropdown">
						<option value=""><?php el('No'); ?></option>
						<option value="1"><?php el('Yes'); ?></option>
					</select>
					<small class="field-desc"><?php el('When enabled, the form tag will not be available until the page is loaded.'); ?></small>
				</div>
				
				<div class="field">
					<label><?php el('KeepAlive'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][keepalive]" class="ui fluid dropdown">
						<option value=""><?php el('No'); ?></option>
						<option value="1"><?php el('Yes'); ?></option>
					</select>
					<small class="field-desc"><?php el('When enabled, the user session will not expire when the form is opened.'); ?></small>
				</div>
			</div>
			<div class="two fields">
				<div class="field">
					<label><?php el('Submit animation'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][submit_animation]" class="ui fluid dropdown">
						<option value="1"><?php el('Yes'); ?></option>
						<option value=""><?php el('No'); ?></option>
					</select>
					<small><?php el('When enabled, the form will display a loading icon when its submitting the data to server.'); ?></small>
				</div>
			</div>
			<div class="two fields">
				<div class="field">
					<label><?php el('Class'); ?></label>
					<input type="text" value="ui form" name="Connection[functions][<?php echo $n; ?>][class]">
					<small class="field-desc"><?php el('A class to apply to your form, changing this may affect your form appearance.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Form ID'); ?></label>
					<input type="text" value="" placeholder="<?php el('Auto'); ?>" name="Connection[functions][<?php echo $n; ?>][formid]">
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Form tag attributes'); ?></label>
				<textarea rows="3" name="Connection[functions][<?php echo $n; ?>][attrs]" placeholder="<?php el('Multiline list of attributes'); ?>"></textarea>
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-modal">
		<div class="two fields">
			<div class="field">
				<label><?php el('Popup modal'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][enabled]" class="ui fluid dropdown">
					<option value=""><?php el('No'); ?></option>
					<option value="1"><?php el('Yes'); ?></option>
				</select>
				<small><?php el('Display form in a popup modal'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Show on page load'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][pageload]" class="ui fluid dropdown">
					<option value=""><?php el('No'); ?></option>
					<option value="1" selected="selected"><?php el('Yes'); ?></option>
				</select>
				<small><?php el('Display the form popup when the page has finished loading.'); ?></small>
			</div>
		</div>
		<div class="field">
			<label><?php el('Popup header'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][modal][header]">
			<small><?php el('The header text of the modal or leave empty.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Display after x miliseconds'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][modal][delay]">
			<small><?php el('Display the form popup after x miliseconds of page load.'); ?></small>
		</div>
		<div class="field">
			<label><?php el('Display after scroll space'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][modal][scroll]">
			<small><?php el('Display the form popup after the page has been scrolled x px.'); ?></small>
		</div>
		<div class="field">
			<label><?php el('Display on click of'); ?></label>
			<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][modal][trigger]">
			<small><?php el('The selector of the element to trigger the form popup, use #element_id or .element_class'); ?></small>
		</div>
		<div class="field">
			<label><?php el('Replacement views'); ?></label>
			<textarea name="Connection[functions][<?php echo $n; ?>][modal][replacement]" rows="5"></textarea>
			<small><?php el('Enter any code to be displayed instead of the form, the code may contain the trigger element.'); ?></small>
		</div>
		<div class="two fields">
			<div class="field">
				<label><?php el('Modal size'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][size]" class="ui fluid dropdown">
					<option value="fullscreen"><?php el('Full screen'); ?></option>
					<option value="small"><?php el('Small'); ?></option>
					<option value="tiny"><?php el('Smaller'); ?></option>
					<option value="mini"><?php el('Smallest'); ?></option>
				</select>
				<small><?php el('The width of the popup modal.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Basic layout'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][basic]" class="ui fluid dropdown">
					<option value=""><?php el('No'); ?></option>
					<option value="1"><?php el('Ye'); ?></option>
				</select>
				<small><?php el('A basic layout has no popup frame.'); ?></small>
			</div>
		</div>
		<div class="two fields">
			<div class="field">
				<label><?php el('Close icon'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][close_icon]" class="ui fluid dropdown">
					<option value="1"><?php el('Yes'); ?></option>
					<option value=""><?php el('No'); ?></option>
				</select>
				<small><?php el('Display a close button ?'); ?></small>
			</div>
		</div>
		<div class="two fields">
			<div class="field">
				<label><?php el('Closable'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][closable]" class="ui fluid dropdown">
					<option value="1"><?php el('Yes'); ?></option>
					<option value=""><?php el('No'); ?></option>
				</select>
				<small><?php el('Will close when the background is clicked.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Light background'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][modal][inverted]" class="ui fluid dropdown">
					<option value="1"><?php el('Yes'); ?></option>
					<option value=""><?php el('No'); ?></option>
				</select>
				<small><?php el('The popup background will be white.'); ?></small>
			</div>
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