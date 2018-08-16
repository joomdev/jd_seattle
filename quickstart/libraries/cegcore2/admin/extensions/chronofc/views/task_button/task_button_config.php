<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">
	
	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="task_button" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="six wide field">
				<label><?php el('Event'); ?></label>
				<input type="text" value="view" name="Connection[views][<?php echo $n; ?>][event]">
				<small><?php el('The event to swhich the button will send the data.'); ?></small>
			</div>
			<div class="ten wide field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui button icon compact" name="Connection[views][<?php echo $n; ?>][class]">
				<small><?php el('Styling class for the button'); ?></small>
			</div>
		</div>
		<div class="field">
			<label><?php el('Content'); ?></label>
			<input type="text" value="Apply" name="Connection[views][<?php echo $n; ?>][content]">
			<small><?php el('The content of the button'); ?></small>
		</div>
		<div class="field">
			<label><?php el('URL and/or URL parameters'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][parameters]">
			<small><?php el('A full qualified url to send the data to or a list of key=value pairs to pass with the default url.'); ?></small>
		</div>
		
		<div class="ui header dividing"><?php el('Advanced settings'); ?></div>
		
		<div class="ui header dividing small"><?php el('Dynamic features'); ?></div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][dynamic][enabled]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][dynamic][enabled]" value="1">
				<label><?php el('Dynamic AJAX features?'); ?></label>
				<small><?php el('Should the button sends the data using AJAX without a page change ?'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Dynamic task'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][dynamic][task]">
				<small><?php el('A task to be executed when the button is clicked, example: send:#form_id'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Dynamic result'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][dynamic][result]">
				<small><?php el('A task to do with the result of the request, example: append:#element_id'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Counter start'); ?></label>
				<input type="text" value="0" name="Connection[views][<?php echo $n; ?>][counter]">
				<small><?php el('The request passes an inceremental integer value, set the start of this integer.'); ?></small>
			</div>
			
		</div>
		
		<div class="ui header dividing small"><?php el('Static features'); ?></div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Custom static task'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][static][task]">
				<small><?php el('A static task for the button, example: remove/closest:.parent_class'); ?></small>
			</div>
			
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][static][popup][enabled]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][static][popup][enabled]" value="1">
				<label><?php el('Open a popup?'); ?></label>
				<small><?php el('Should this button open a popup when clicked ?'); ?></small>
			</div>
		</div>
			
		<div class="two fields">	
			<div class="field">
				<label><?php el('Popup content'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][static][popup][content]" rows="3"></textarea>
				<small><?php el('The content of the popup.'); ?></small>
			</div>
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>