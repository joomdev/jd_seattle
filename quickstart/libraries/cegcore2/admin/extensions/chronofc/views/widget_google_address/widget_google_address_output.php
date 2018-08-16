<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$view['api_key'].'&libraries=places&callback=initAutocomplete" async defer></script>';
	
	$address = [];
	foreach($view['address'] as $name => $value){
		if(!empty($value)){
			$address[] = $name.': "'.$value.'"';
		}
	}
	
	\GApp::document()->addJsCode('
		var placeSearch, autocomplete;
		var componentForm = {
			'.implode(',', $address).'
		};
		'.(!empty($view['geolocate']) ? 'geolocate();' : '').'

		function initAutocomplete(){
			autocomplete = new google.maps.places.Autocomplete(
				(document.getElementById("'.$view['field_id'].'")),
				{types: ["geocode"]}
			);
			
			autocomplete.addListener("place_changed", fillInAddress);
		}

		function fillInAddress() {
			var place = autocomplete.getPlace();
			'.(!empty($view['formatted_field_id']) ? 'jQuery("#'.$view['formatted_field_id'].'").val(place.formatted_address);' : '').'
			
			for(var i = 0; i < place.address_components.length; i++){
				var addressType = place.address_components[i].types[0];
				if(componentForm[addressType]){
					jQuery("#"+componentForm[addressType]).val(place.address_components[i]["long_name"]);
				}
			}
		}
		
		function geolocate(){
			if (navigator.geolocation){
				navigator.geolocation.getCurrentPosition(function(position) {
					var geolocation = {
						lat: position.coords.latitude,
						lng: position.coords.longitude
					};
					var circle = new google.maps.Circle({
						center: geolocation,
						radius: position.coords.accuracy
					});
					autocomplete.setBounds(circle.getBounds());
				});
			}
		}
	');