<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
use Sellacious\Config\ConfigHelper;

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('Text');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_sellacious
 * @since		1.6.1
 */
class JFormFieldMapAddress extends JFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var	 string
	 */
	protected $type = 'MapAddress';

	protected function getInput()
	{
		$input = parent::getInput();

		if (JPluginHelper::isEnabled('system', 'sellacioushyperlocal'))
		{
			// Default US location for now
			$lat = (float) $this->element['lat'] ? : '';
			$lng = (float) $this->element['lng'] ? : '';

			$hlConfig     = ConfigHelper::getInstance('plg_system_sellacioushyperlocal');
			$hlParams     = $hlConfig->getParams();
			$googleApiKey = $hlParams->get('google_api_key', '');

			$args = array(
				"id"   => $this->id,
				"zoom" => 12,
				"lat"  => $lat,
				"lng"  => $lng
			);
			$args = json_encode($args);

			JHtml::_('jquery.framework');
			JHtml::_('script', 'https://maps.googleapis.com/maps/api/js?key=' . $googleApiKey . '&libraries=places', false, false);

			$script = <<<JS
			var JFormFieldMapAddress = function () {
				this.options = {
				};
				
				this.map = null;
				this.latLng = null;
				this.geocoder = null;
				this.autocomplete = null;
				this.markers = [];
			
				return this;
			};
			
			(function ($) {
				JFormFieldMapAddress.prototype = {
					init: function(options) {
						var thisobj = this;
						
						$.extend(thisobj.options, options);
						
						if (thisobj.options.lat != "" || thisobj.options.lng != "") {
							thisobj.latLng = new google.maps.LatLng(thisobj.options.lat, thisobj.options.lng);
							thisobj.setup();
						} else {
							if (navigator.geolocation) {
								navigator.geolocation.getCurrentPosition(function(position) {
									thisobj.latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
									
									thisobj.setup();
								});
							} else {
								return false;
							}
						}
					},
					setup: function() {
						var thisobj = this;
						
						thisobj.map = new google.maps.Map(document.getElementById(thisobj.options.id + "_map"), {
							center: thisobj.latLng,
							zoom: thisobj.options.zoom
						});
						google.maps.event.trigger(thisobj.map,'resize');
						
						thisobj.geocoder = new google.maps.Geocoder();
						
						thisobj.addMarker(thisobj.latLng);
						thisobj.geocodeMap(thisobj.latLng);
						
						thisobj.autocomplete = new google.maps.places.Autocomplete(
						/** @type {!HTMLInputElement} */(document.getElementById(thisobj.options.id)),
						{types: ['geocode']});
						
						thisobj.autocomplete.addListener('place_changed', function () {
							var place = this.getPlace();
							var latLng = place.geometry.location;
							
							thisobj.deleteMarkers();
							thisobj.addMarker(latLng);
							thisobj.geocodeMap(latLng);
						    $(document).trigger('onMapChangeLocation', [latLng.lat(), latLng.lng()]);
						});
					},
					addMarker: function(latLng)
					{
						var thisobj = this;
						
						thisobj.deleteMarkers();
						
						var marker = new google.maps.Marker({
				          position: latLng,
				          map: thisobj.map,
				          draggable:true,
				        });
						
						thisobj.geocodeMap(latLng);
						
						google.maps.event.addListener(marker, "dragend", function(event) {
							thisobj.geocodeMap(event.latLng);
						    
						    $(document).trigger('onMapChangeLocation', [event.latLng.lat(), event.latLng.lng()]);
				        });
						
						thisobj.markers.push(marker);
						thisobj.map.setCenter(latLng);
						
						$(document).trigger('onMapChangeLocation', [latLng.lat(), latLng.lng()]);
					},
					geocodeMap: function(latLng) {
						var thisobj = this;
						
						thisobj.geocoder.geocode({
						    'latLng': latLng
						  }, function(results, status) {
						    if (status == google.maps.GeocoderStatus.OK) {
						      if (results[1]) {
						        $('#' + thisobj.options.id).val(results[1].formatted_address);
						        $(document).trigger('OnMapGeoCode', [results[1].address_components])
						      }
						    }
						});
					},
					deleteMarkers: function() {
				        this.clearMarkers();
				        this.markers = [];
					},
					clearMarkers: function() {
				        this.setMapOnAll(null);
					},
					showMarkers: function() {
				        this.setMapOnAll(this.map);
					},
					setMapOnAll: function(map) {
				        for (var i = 0; i < this.markers.length; i++) {
				          this.markers[i].setMap(map);
				        }
			        }
				}
			})(jQuery);
			
			jQuery(document).ready(function($) {
				$(window).load(function () {
					window.{$this->id} = new JFormFieldMapAddress;
					window.{$this->id}.init({$args});
					google.maps.event.trigger(window.{$this->id}.map, 'resize');
				});
			});
JS;
			JFactory::getDocument()->addScriptDeclaration($script);

			$input .= '<div class="clearfix"></div><div id="' . $this->id .'_map" class="mapaddress" style="width: 400px; height: 400px;"></div>';
		}


		return $input;
	}
}
