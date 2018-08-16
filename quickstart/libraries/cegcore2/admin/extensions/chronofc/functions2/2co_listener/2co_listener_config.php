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
		<a class="item" data-tab="function-<?php echo $n; ?>-events"><?php el('Events'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="2co_listener" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
		
			<div class="two fields">
				<div class="field required">
					<label><?php el('Seller id'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][sid]">
					<small><?php el('Your 2Checkout seller id.'); ?></small>
				</div>
				<div class="field required">
					<label><?php el('Secret word'); ?></label>
					<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][secret]">
					<small><?php el('Your 2Checkout notifications secret word.'); ?></small>
				</div>
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-events">
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="order_created" data-event_switcher="1">
				<label><?php el('Enable the Order created event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="refund_issued" data-event_switcher="1">
				<label><?php el('Enable the Refund issued event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="fraud_status_changed" data-event_switcher="1">
				<label><?php el('Enable the Fraud Status Changed event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="invoice_status_changed" data-event_switcher="1">
				<label><?php el('Enable the Invoice Status Changed event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="ship_status_changed" data-event_switcher="1">
				<label><?php el('Enable the Ship Status Changed event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="recurring_stopped" data-event_switcher="1">
				<label><?php el('Enable the Recurring Stopped event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="recurring_restarted" data-event_switcher="1">
				<label><?php el('Enable the Recurring Restarted event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="recurring_installment_success" data-event_switcher="1">
				<label><?php el('Enable the Recurring Success event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="recurring_installment_failed" data-event_switcher="1">
				<label><?php el('Enable the Recurring Failed event'); ?></label>
			</div>
		</div>
		<div class="field">
			<div class="ui checkbox">
				<input type="checkbox" class="hidden" name="Connection[functions][<?php echo $n; ?>][eventsws][]" value="recurring_complete" data-event_switcher="1">
				<label><?php el('Enable the Recurring Complete event'); ?></label>
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