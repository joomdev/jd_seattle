var ModSellaciousHyperLocal = function () {
	this.options = {
	};

	this.autocomplete = null;
	this.geocoder = null;
	this.geojax   = null;

	return this;
};

(function ($) {
	ModSellaciousHyperLocal.prototype = {
		init: function() {
			var $this = this;
			var $geo_finder_btn = '#' + $this.options.geo_finder_btn;

			$this.geocoder = new google.maps.Geocoder();

			$($geo_finder_btn).on('click', function () {
				$this.geolocate();
			});
		},
		setup: function (options) {

			$.extend(this.options, options);

			var $this = this;
			var $location_field = '#' + $this.options.location_field;
			var $location_value = '#' + $this.options.location_value;

			if ($this.options.params.address_autocomplete == 2)
			{
				$this.autocomplete = new google.maps.places.Autocomplete(
					/** @type {!HTMLInputElement} */(document.getElementById($this.options.location_field)),
					{types: ['geocode']});

				$this.autocomplete.addListener('place_changed', function () {
					$($location_value).val('');

					var place = this.getPlace();
					var types = $($location_field).data('autofill-components').split(',');
					var componentForm = {
						'zip'      : 'postal_code',
						'locality' : 'sublocality',
						'city'     : 'locality',
						'district' : 'administrative_area_level_2',
						'state'    : 'administrative_area_level_1',
						'country'  : 'country',
					};
					var components = [];

					for (type in types) {
						if (typeof types[type] !== "function") {
							components.push(componentForm[types[type]]);
						}
					}

					var formatted_address = [];

					for (var i = 0; i < place.address_components.length; i++) {
						var addressTypes = place.address_components[i].types;

						for (var addressType in addressTypes) {
							if ($.inArray(addressTypes[addressType], components) !== -1) {
								formatted_address.push(place.address_components[i].long_name);
								break;
							}
						}
					}

					formatted_address = formatted_address.join(', ');

					$($location_field).val(formatted_address);
					$this.setAddress('', formatted_address, $this.setBounds);
				});
			} else {
				$($location_field).autocomplete({
					source: function( request, response ) {
						var pData = {
							option: 'com_ajax',
							module: 'sellacious_hyperlocal',
							method: 'getAutoCompleteSearch',
							format: 'json',
							term: request.term,
							parent_id: 1,
							types: $($location_field).data('autofill-components').split(','),
							list_start: 0,
							list_limit: 5
						};
						$.ajax({
							url: "index.php",
							type: 'POST',
							dataType: "json",
							data: pData,
							cache: false,
							success: function(data) {
								response(data);
							}
						});
					},
					select: function(event, ui) {
						$($location_field).val(ui.item.value);
						$($location_value).val(ui.item.id);

						$this.setAddress(ui.item.id, ui.item.value, $this.setBounds);

						return false;
					},
					minLength: 3
				});
			}
		},
		geolocate: function(location) {
			var $this = this;

			if ($this.options.params.browser_detect == 1 && (location === undefined || !Object.keys(location).length))
			{
				if (navigator.geolocation) {
					var $this = this;
					var $geo_finder_btn = '#' + $this.options.geo_finder_btn;

					$($geo_finder_btn).attr('disabled', true);
					$($geo_finder_btn).text(Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_DETECTING_LOCATION'));

					navigator.geolocation.getCurrentPosition(function(position) {
						var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

						$this.geocoder.geocode({
							'latLng': latlng
						}, function (results, status) {
							if (status === google.maps.GeocoderStatus.OK) {
								if (results[1]) {
									if ($this.options.params.address_autocomplete == 2) {
										$this.setGeoLocation(results[1], $this.setBounds);
									} else {
										$this.getAddress(results[1].address_components, results[1].geometry.location.lat(), results[1].geometry.location.lng(), $this.setBounds);
									}
								} else {
									Joomla.renderMessages({error: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_NO_RESULTS_FOUND')]});
								}
							} else {
								alert(Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_GEOCODE_FAILED') + status);
							}
						});
					});
				}
			}
		},
		setAddress: function(id, address, callback) {
			var $this = this;

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option : 'com_ajax',
				module : 'sellacious_hyperlocal',
				format : 'json',
				method : 'setAddress',
				id     : id,
				address: address,
				params : $this.options.params,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					if (typeof callback == 'function') callback(response, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		},
		setGeoLocation: function(result, callback) {
			var $this = this;

			var $location_field = '#' + $this.options.location_field;
			var $location_value = '#' + $this.options.location_value;
			$($location_field).val(result.formatted_address);
			$($location_value).val('');

			var lat = result.geometry.location.lat();
			var long = result.geometry.location.lng();
			var resp = {data : {lat: lat, long: long}};

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option : 'com_ajax',
				module : 'sellacious_hyperlocal',
				format : 'json',
				method : 'setGeoLocation',
				address: result.formatted_address,
				lat    : lat,
				long   : long
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					if (typeof callback == 'function') callback(resp, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
			});
		},
		resetAddress: function(callback) {
			var $this = this;

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option : 'com_ajax',
				module : 'sellacious_hyperlocal',
				format : 'json',
				method : 'resetAddress',
				params : $this.options.params,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					if (typeof callback == 'function') callback(response, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				console.log(jqXHR.responseText);
			});
		},
		setShippableFilter: function(address, callback) {
			var $this = this;

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option : 'com_ajax',
				module : 'sellacious_hyperlocal',
				format : 'json',
				method : 'setShippableFilter',
				address: address,
				params : $this.options.params,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					if (typeof callback == 'function') callback(response, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		},
		setLocationFilter: function(address, callback) {
			var $this = this;

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option : 'com_ajax',
				module : 'sellacious_hyperlocal',
				format : 'json',
				method : 'setLocationFilter',
				address: address,
				params : $this.options.params,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					if (typeof callback == 'function') callback(response, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_SET_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		},
		getAddress: function(components, lat, lng, callback) {
			var $this = this;
			var $location_field = '#' + $this.options.location_field;
			var $location_value = '#' + $this.options.location_value;
			var $geo_finder_btn = '#' + $this.options.geo_finder_btn;
			var $params         = $this.options.params;
			var $view           = $this.options.view;

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option: 'com_ajax',
				module: 'sellacious_hyperlocal',
				format: 'json',
				method: 'getAddress',
				params: $params,
				autofill: $($location_field).data('autofill-components'),
				address_components: components,
				lat: lat,
				lng: lng,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
					$($geo_finder_btn).attr('disabled', false);
					$($geo_finder_btn).text(Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_GET_CURRENT_LOCATION'));
				}
			}).done(function (response) {
				if (response.success) {
					$($location_field).val(response.data.address);
					$($location_value).val(response.data.id);

					if (typeof callback == 'function') callback(response, $this);
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		},
		setRadiusRange: function(lat, long, min, max, minMetres, maxMetres) {
			var $this  = this;
			var latlng = new google.maps.LatLng(lat, long);

			// Get Product bounds (Min)
			var productCircleMin = new google.maps.Circle({
				center: latlng,
				radius: minMetres
			});
			var productBoundsMin   = productCircleMin.getBounds();
			var hlProductBoundsMin = {
				north: Math.round(productBoundsMin.getNorthEast().lat() * 10000) / 10000,
				east: Math.round(productBoundsMin.getNorthEast().lng() * 10000) / 10000,
				south: Math.round(productBoundsMin.getSouthWest().lat() * 10000) / 10000,
				west: Math.round(productBoundsMin.getSouthWest().lng() * 10000) / 10000,
			};

			// Get Product bounds (Max)
			var productCircle = new google.maps.Circle({
				center: latlng,
				radius: maxMetres
			});
			var productBounds   = productCircle.getBounds();
			var hlProductBounds = {
				north: Math.round(productBounds.getNorthEast().lat() * 10000) / 10000,
				east: Math.round(productBounds.getNorthEast().lng() * 10000) / 10000,
				south: Math.round(productBounds.getSouthWest().lat() * 10000) / 10000,
				west: Math.round(productBounds.getSouthWest().lng() * 10000) / 10000,
			};

			if ($this.geojax) $this.geojax.abort();
			var data = {
				option: 'com_ajax',
				module: 'sellacious_hyperlocal',
				format: 'json',
				method: 'setRadiusRange',
				product_bounds_min: hlProductBoundsMin,
				product_bounds: hlProductBounds,
				store_bounds: hlProductBounds,
				store_bounds_min: hlProductBoundsMin,
				min_radius: min,
				max_radius: max,
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					window.location.reload();
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		},
		setBounds: function (response, object) {
			var $this  = this;
			var latlng = new google.maps.LatLng(response.data.lat, response.data.long);

			// Get Product bounds (Min)
			var productCircleMin = new google.maps.Circle({
				center: latlng,
				radius: object.options.min_distance
			});
			var productBoundsMin   = productCircleMin.getBounds();
			var hlProductBoundsMin = {
				north: Math.round(productBoundsMin.getNorthEast().lat() * 10000) / 10000,
				east: Math.round(productBoundsMin.getNorthEast().lng() * 10000) / 10000,
				south: Math.round(productBoundsMin.getSouthWest().lat() * 10000) / 10000,
				west: Math.round(productBoundsMin.getSouthWest().lng() * 10000) / 10000,
			};

			// Get Product bounds (Max)
			var productCircle = new google.maps.Circle({
				center: latlng,
				radius: object.options.max_distance
			});
			var productBounds   = productCircle.getBounds();
			var hlProductBounds = {
				north: Math.round(productBounds.getNorthEast().lat() * 10000) / 10000,
				east: Math.round(productBounds.getNorthEast().lng() * 10000) / 10000,
				south: Math.round(productBounds.getSouthWest().lat() * 10000) / 10000,
				west: Math.round(productBounds.getSouthWest().lng() * 10000) / 10000,
			};

			var timezone = '';
			if ($this.geojax) $this.geojax.abort();

			$this.geojax = $.ajax({
				url:"https://maps.googleapis.com/maps/api/timezone/json?location="+response.data.lat+","+response.data.long+"&timestamp="+(Math.round((new Date().getTime())/1000)).toString()+'&key='+object.options.params.google_api_key,
				async: false
			}).done(function(response){
				timezone = response.timeZoneId;
			});

			// Set Bounds
			if ($this.geojax) $this.geojax.abort();
			var data = {
				option: 'com_ajax',
				module: 'sellacious_hyperlocal',
				format: 'json',
				method: 'setBounds',
				product_bounds_min: hlProductBoundsMin,
				product_bounds: hlProductBounds,
				store_bounds: hlProductBounds,
        store_bounds_min: hlProductBoundsMin,
				min_radius: object.options.min_distance / object.options.unit_rate,
				max_radius: object.options.max_distance / object.options.unit_rate,
        timezone: timezone
			};

			$this.geojax = $.ajax({
				url: 'index.php',
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: data,
				beforeSend: function () {
				},
				complete: function () {
				}
			}).done(function (response) {
				if (response.success) {
					window.location.reload();
				} else {
					Joomla.renderMessages({warning: [response.message]});
				}
			}).fail(function (jqXHR) {
				Joomla.renderMessages({warning: [Joomla.JText._('MOD_SELLACIOUS_HYPERLOCAL_FETCH_ADDRESS_FAILED')]});
				console.log(jqXHR.responseText);
			});
		}
	}
})(jQuery);

