<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$mapid = \G2\L\Str::slug($view['name']);
	
	echo '<div id="'.$mapid.'" style="width:'.$this->Parser->parse($view['width'], true).'; height:'.$this->Parser->parse($view['height'], true).'"></div>';
	echo '<script src="https://maps.googleapis.com/maps/api/js?key='.$view['api_key'].(!empty($view['places']) ? '&libraries=places' : '').'&callback='.$view['name'].'" async defer></script>';
	
	$places = '';
	if(!empty($view['places'])){
		$places = '
			var infowindow = new google.maps.InfoWindow();
			var service = new google.maps.places.PlacesService(map);
			
			var plcs = JSON.parse(\''.json_encode($this->Parser->parse($view['places'], true)).'\');
			
			jQuery.each(plcs, function(i, plc){
				service.getDetails({
					placeId: plc["id"]
				}, function(place, status) {
					if (status === google.maps.places.PlacesServiceStatus.OK) {
						var marker = new google.maps.Marker({
							map: map,
							position: place.geometry.location
						});
						google.maps.event.addListener(marker, "click", function() {
							var content = plc["content"];
							
							jQuery.each(place, function(key, val){
								content = content.replace("{" + key + "}", val);
							});
							
							infowindow.setContent(content);
							infowindow.open(map, this);
						});
					}
				});
			});
		';
	}
	
	\GApp::document()->addJsCode('
		function '.$view['name'].'() {
			var map = new google.maps.Map(document.getElementById("'.$mapid.'"), {
				center: {lat: '.$this->Parser->parse($view['lat'], true).', lng: '.$this->Parser->parse($view['lng'], true).'},
				zoom: '.$this->Parser->parse($view['zoom'], true).'
			});
			
			'.$places.'
		}
	');