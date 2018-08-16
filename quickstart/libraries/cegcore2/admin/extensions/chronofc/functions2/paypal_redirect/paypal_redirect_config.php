<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">
	<?php
		//$settings = \GApp::extension()->settings();
		if(\GApp::extension()->valid('paypal') OR \GApp::extension()->valid('extras')):
	?>
		<div class="ui message green">The PayPal function is validated, thank you.</div>
	<?php else: ?>
		<div class="ui message red">The PayPal function is in trial mode and will always redirect to the sandbox website, please validate it after testing.</div>
	<?php endif; ?>
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="paypal_redirect" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
		
			<div class="three fields">
				<div class="field">
					<label><?php el('Payment type'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][cmd]" class="ui fluid dropdown">
						<option value="_cart"><?php el('Shopping cart'); ?></option>
						<option value="_ext-enterd"><?php el('Single checkout'); ?></option>
						<?php /*<option value="_xclick-subscriptions"><?php el('Recurring payment'); ?></option>*/ ?>
					</select>
				</div>
				<div class="field required">
					<label><?php el('Business email'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][business]">
				</div>
				<div class="field">
					<label><?php el('Mode'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][sandbox]" class="ui fluid dropdown">
						<option value="0"><?php el('Live'); ?></option>
						<option value="1"><?php el('Sandbox testing'); ?></option>
					</select>
					<small><?php el('Select Sandbox for payment tests.'); ?></small>
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Currency code'); ?></label>
					<input type="text" value="USD" name="Connection[functions][<?php echo $n; ?>][currency_code]">
				</div>
				<div class="field">
					<label><?php el('Quantity'); ?></label>
					<input type="text" value="1" name="Connection[functions][<?php echo $n; ?>][quantity]">
				</div>
				<div class="field">
					<label><?php el('Debug'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][debug]" class="ui fluid dropdown">
						<option value="0"><?php el('Disabled'); ?></option>
						<option value="1"><?php el('Enabled'); ?></option>
					</select>
					<small><?php el('Display the full redirect url and do not redirect to the PayPal website.'); ?></small>
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Items data'); ?></div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Item name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][item_name]">
				</div>
				<div class="field">
					<label><?php el('Item number'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][item_number]">
				</div>
				<div class="field">
					<label><?php el('Amount'); ?></label>
					<input type="text" value="1" name="Connection[functions][<?php echo $n; ?>][amount]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Shipping costs'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][shipping]">
				</div>
				<div class="field">
					<label><?php el('2nd item shipping costs'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][shipping2]">
				</div>
				<div class="field">
					<label><?php el('Handling'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][handling]">
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Extra settings'); ?></div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('No shipping address'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][no_shipping]">
				</div>
				<div class="field">
					<label><?php el('No note field'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][no_note]">
				</div>
				<div class="field">
					<label><?php el('Note field label'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][cn]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Return url after completion'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][return]">
					<small><?php el('User full url or {url.full:event}'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Return url after Cancel'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][cancel_return]">
					<small><?php el('User full url or {url.full:event}'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('IPN notify URL'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][notify_url]">
				<small><?php el('User full url or {url.full:event}'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Logo Image URL'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][image_url]">
			</div>
			
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Custom parameter'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][custom]">
				</div>
				<div class="field">
					<label><?php el('Invoice#'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][invoice]">
				</div>
				<div class="field">
					<label><?php el('Tax amount'); ?></label>
					<input type="text" value="0" name="Connection[functions][<?php echo $n; ?>][tax]">
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Customer info'); ?></div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Email'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][email]">
				</div>
				<div class="field">
					<label><?php el('First name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][first_name]">
				</div>
				<div class="field">
					<label><?php el('Last name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][last_name]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Address 1'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][address1]">
				</div>
				<div class="field">
					<label><?php el('Address 2'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][address2]">
				</div>
				<div class="field">
					<label><?php el('City'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][city]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('State'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][state]">
				</div>
				<div class="field">
					<label><?php el('Zip'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][zip]">
				</div>
				<div class="field">
					<label><?php el('Country'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][country]">
					<small><?php el('2 characters country code or a provider shortcode.'); ?></small>
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Locale'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][lc]">
					<small><?php el('2 characters language code or a provider shortcode.'); ?></small>
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Options'); ?></div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Option 1 name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][on0]">
				</div>
				<div class="field">
					<label><?php el('Option 1 value'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][os0]">
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Option 2 name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][on1]">
				</div>
				<div class="field">
					<label><?php el('Option 2 value'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][os1]">
				</div>
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