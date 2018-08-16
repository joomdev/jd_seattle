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
		<input type="hidden" value="widget_google_address" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="field required">
			<label><?php el('API key'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][api_key]">
			<small><?php el('Your GMaps API key provided by Google'); ?></small>
		</div>
		
		<div class="field required">
			<label><?php el('Address field ID'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][field_id]">
			<small><?php el('The id of the field used to load the address information from Google.'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Formatted result address field ID'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][formatted_field_id]">
			<small><?php el('The id of the field to receive the full formatted address result.'); ?></small>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Street number field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][street_number]">
				<small><?php el('The id of the street number result.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Street address field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][route]">
				<small><?php el('The id of the street address result.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('City name field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][locality]">
				<small><?php el('The id of the city name result.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('State name field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][administrative_area_level_1]">
				<small><?php el('The id of the state name result.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Sub state name field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][administrative_area_level_2]">
				<small><?php el('The id of the sub state name result.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Zip code field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][postal_code]">
				<small><?php el('The id of the zip code result.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Country name field ID'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][address][country]">
				<small><?php el('The id of the country name result.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox toggle red">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][geolocate]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][geolocate]" value="1">
				<label><?php el('GeoLocate ?'); ?></label>
				<small><?php el('If enabled then the user location data will be used, user will get a prompt asking for acceptance.'); ?></small>
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
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>