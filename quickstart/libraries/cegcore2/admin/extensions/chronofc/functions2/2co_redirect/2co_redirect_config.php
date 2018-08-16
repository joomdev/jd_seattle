<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">
	<?php
		if(\GApp::extension()->valid('2checkout') OR \GApp::extension()->valid('extras')):
	?>
		<div class="ui message green">The 2Checkout function is validated, thank you.</div>
	<?php else: ?>
		<div class="ui message red">The 2Checkout function is in trial mode and will create demo purchases, please validate it for real purchases.</div>
	<?php endif; ?>
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="2co_redirect" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
		
			<div class="three fields">
				<div class="field required">
					<label><?php el('Seller id'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][sid]">
					<small><?php el('Your 2Checkout seller id.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Parameter set'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][mode]" class="ui fluid dropdown">
						<option value="2CO"><?php el('2CO'); ?></option>
					</select>
				</div>
				<div class="field">
					<label><?php el('Mode'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][sandbox]" class="ui fluid dropdown">
						<option value="0"><?php el('Live'); ?></option>
						<option value="1"><?php el('Sandbox testing'); ?></option>
					</select>
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Currency code'); ?></label>
					<input type="text" value="USD" name="Connection[functions][<?php echo $n; ?>][currency_code]">
					<small><?php el('3 characters currency code.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Language'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][lang]">
					<small><?php el('2 characters language code.'); ?></small>
				</div>
				<div class="field">
					<label><?php el('Debug'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][debug]" class="ui fluid dropdown">
						<option value="0"><?php el('Disabled'); ?></option>
						<option value="1"><?php el('Enabled'); ?></option>
					</select>
					<small><?php el('Display the full redirect url and do not redirect to the 2Checkout website.'); ?></small>
				</div>
			</div>
			<div class="two fields">
				<div class="field">
					<label><?php el('Demo mode'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][demo]" class="ui fluid dropdown">
						<option value=""><?php el('No'); ?></option>
						<option value="Y"><?php el('Yes'); ?></option>
					</select>
				</div>
				<div class="field required">
					<label><?php el('Hash key'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][hash]">
					<small><?php el('The hash is used to secure the product id, price and quantity values from being changed, use the same hash in your your 2CO listener.'); ?></small>
				</div>
			</div>
			
			<div class="field required">
				<label><?php el('Products provider'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][products_provider]">
				<small><?php el('The products array provider, each array item is a product array which may contain the following values:'); ?></small>
				<small><?php el('type,name,quantity,price,tangible,product_id,description,recurrence,duration,startup_fee'); ?></small>
			</div>
			
			<div class="ui header dividing"><?php el('Billing information'); ?></div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Card holder name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][card_holder_name]">
				</div>
				<div class="field">
					<label><?php el('Email'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][email]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Street address 1'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][street_address]">
				</div>
				<div class="field">
					<label><?php el('Street address 2'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][street_address2]">
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
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Phone'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][phone]">
				</div>
				<div class="field">
					<label><?php el('Phone extension'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][phone_extension]">
				</div>
			</div>
			
			<div class="ui header dividing"><?php el('Extra information'); ?></div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Merchant Order id'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][merchant_order_id]">
				</div>
				<div class="field">
					<label><?php el('Coupon'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][coupon]">
				</div>
				<div class="field">
					<label><?php el('PayPal Direct'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][paypal_direct]">
					<small><?php el('Return any value to redirect users to pay using PayPal, your 2CO account must have API enabled.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Approved URL'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][x_receipt_link_url]">
				<small><?php el('A url on your website to return the user to after the purchase'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Purchase step'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][purchase_step]" class="ui fluid dropdown">
					<option value="review-cart"><?php el('Review cart'); ?></option>
					<option value="shipping-information"><?php el('Shipping information'); ?></option>
					<option value="shipping-method"><?php el('Shipping method'); ?></option>
					<option value="billing-information"><?php el('Billing information'); ?></option>
					<option value="payment-method"><?php el('Payment method'); ?></option>
				</select>
			</div>
			<div class="field">
				<label><?php el('Custom parameters'); ?></label>
				<textarea placeholder="<?php el('Multiline list'); ?>" name="Connection[functions][<?php echo $n; ?>][parameters]" rows="5"></textarea>
				<small><?php el('Multi line list of parame_name=value to be passed to the gateway.'); ?></small>
			</div>
			
			<div class="ui header dividing"><?php el('Shipping information'); ?></div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Name'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_name]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('Street address 1'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_street_address]">
				</div>
				<div class="field">
					<label><?php el('Street address 2'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_street_address2]">
				</div>
				<div class="field">
					<label><?php el('City'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_city]">
				</div>
			</div>
			
			<div class="three fields">
				<div class="field">
					<label><?php el('State'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_state]">
				</div>
				<div class="field">
					<label><?php el('Zip'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_zip]">
				</div>
				<div class="field">
					<label><?php el('Country'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][ship_country]">
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