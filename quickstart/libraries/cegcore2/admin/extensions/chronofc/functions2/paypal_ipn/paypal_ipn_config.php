<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">
	<?php
		if(\GApp::extension()->valid('paypal') OR \GApp::extension()->valid('extras')):
	?>
		<div class="ui message green">The PayPal function is validated, thank you.</div>
	<?php else: ?>
		<div class="ui message red">The PayPal function is in trial mode and will always redirect to the sandbox website, please validate it after testing.</div>
	<?php endif; ?>
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-events"><?php el('Events'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="paypal_ipn" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
		
			<div class="three fields">
				<div class="field">
					<label><?php el('Mode'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][sandbox]" class="ui fluid dropdown">
						<option value="0"><?php el('Live'); ?></option>
						<option value="1"><?php el('Sandbox testing'); ?></option>
					</select>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Receiver email'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][receiver_email]">
				<small><?php el('Your PayPal business email, it will be checked against the payment receiver email.'); ?></small>
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-events">
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="success" data-event_switcher="1">
				<label><?php el('Enable the payment Completed event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="pending" data-event_switcher="1">
				<label><?php el('Enable the payment Pending event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="denied" data-event_switcher="1">
				<label><?php el('Enable the payment Denied event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="expired" data-event_switcher="1">
				<label><?php el('Enable the payment Expired event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="failed" data-event_switcher="1">
				<label><?php el('Enable the payment Failed event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="refunded" data-event_switcher="1">
				<label><?php el('Enable the payment Refunded event'); ?></label>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="reversed" data-event_switcher="1">
				<label><?php el('Enable the payment Reversed event'); ?></label>
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