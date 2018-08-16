<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class geolocationClass extends acymailingClass{
	var $tables = array('geolocation');
	var $pkey = 'geolocation_id';

	function saveGeolocation($geoloc_action, $subid){
		$config = acymailing_config();

		$geoloc_config = $config->get('geolocation');
		if(stripos($geoloc_config, $geoloc_action) === false) return false;

		$geo_element = new stdClass();
		$geo_element->geolocation_subid = $subid;
		$geo_element->geolocation_type = $geoloc_action;

		$userHelper = acymailing_get('helper.user');
		$geo_element->geolocation_ip = $userHelper->getIP();
		if(empty($geo_element->geolocation_subid) || empty($geo_element->geolocation_ip)) return false;

		$geo_element = $this->getIpLocation($geo_element);

		if($config->get('anonymous_tracking', 0) == 1){
			$geo_element->geolocation_subid = 0;
			$geo_element->geolocation_ip = '';
		}

		if($geo_element != false){
			parent::save($geo_element);
			return $geo_element;
		}else{
			return false;
		}
	}

	function getIpLocation($element){
		$oldElement = $this->getMostRecentDataByIp($element->geolocation_ip);
		if(!empty($oldElement) && (time() - $oldElement->geolocation_created < 2592000)){
			$element->geolocation_latitude = $oldElement->geolocation_latitude;
			$element->geolocation_longitude = $oldElement->geolocation_longitude;
			$element->geolocation_postal_code = $oldElement->geolocation_postal_code;
			$element->geolocation_country = $oldElement->geolocation_country;
			$element->geolocation_country_code = $oldElement->geolocation_country_code;
			$element->geolocation_state = $oldElement->geolocation_state;
			$element->geolocation_state_code = $oldElement->geolocation_state_code;
			$element->geolocation_city = $oldElement->geolocation_city;
			$element->geolocation_created = time();
			$element->geolocation_continent = (!empty($oldElement->geolocation_country_code) ? $this->countryToContinent($oldElement->geolocation_country_code) : '');
			$element->geolocation_timezone = $oldElement->geolocation_timezone;
			return $element;
		}

		$geoClass = acymailing_get('inc.ipinfodb');

		$config = acymailing_config();
		$api_key = trim($config->get('geoloc_api_key', ''));
		if($api_key == '') return false;
		$geoClass->setKey($api_key);
		$location = $geoClass->getCity($element->geolocation_ip);
		$errorLoc = $geoClass->getError();

		if(empty($errorLoc) && !empty($location) && !empty($location->countryCode) && $location->countryCode != '-'){
			$element->geolocation_latitude = (!empty($location->latitude) ? $location->latitude : 0);
			$element->geolocation_longitude = (!empty($location->longitude) ? $location->longitude : 0);
			$element->geolocation_postal_code = (!empty($location->zipCode) ? $location->zipCode : '');
			$element->geolocation_country = (!empty($location->countryName) ? ucwords(strtolower($location->countryName)) : '');
			$element->geolocation_country_code = (!empty($location->countryCode) ? $location->countryCode : '');
			$element->geolocation_state = (!empty($location->regionName) ? $location->regionName : '');
			$element->geolocation_state_code = (!empty($location->regioncode) ? $location->regioncode : '');
			$element->geolocation_city = (!empty($location->cityName) ? ucwords(strtolower($location->cityName)) : '');
			$element->geolocation_created = time();
			$element->geolocation_continent = (!empty($location->countryCode) ? $this->countryToContinent($location->countryCode) : '');
			$element->geolocation_timezone = (!empty($location->timeZone) ? $location->timeZone : '');
			return $element;
		}else{
			return false;
		}
	}

	function getMostRecentDataByIp($ip){
		return acymailing_loadObject("SELECT * FROM #__acymailing_geolocation WHERE geolocation_ip=".acymailing_escapeDB($ip)." ORDER BY geolocation_created DESC");
	}

	function testApiKey($apiKey){
		$geoClass = acymailing_get('inc.ipinfodb');
		$geoClass->setKey(trim($apiKey));

		$userHelper = acymailing_get('helper.user');
		$ipUser = $userHelper->getIP();
		$test = $geoClass->getCity($ipUser);
		$errorLoc = $geoClass->getError();

		if(!empty($test)){ // Has a return from the API
			return $test;
		}else{ // No return, we will display the IP used when calling API
			$retourError = new stdClass();
			$retourError->statusCode = 'noReturn';
			$retourError->ip = $ipUser;
			if(!empty($errorLoc)) $retourError->errorAPI = $errorLoc;
			return $retourError;
		}
	}

	function countryToContinent($country){
		$continent = '';
		$asia = array('AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'IO', 'KH', 'CN', 'CX', 'CC', 'CY', 'GE', 'HK', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JP', 'JO', 'KZ', 'KP', 'KR', 'KW', 'KG', 'LA', 'LB', 'MO', 'MY', 'MV', 'MN', 'MM', 'NP', 'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'LK', 'SY', 'TW', 'TJ', 'TH', 'TL', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE');
		$africa = array('AO', 'BJ', 'DZ', 'BW', 'BF', 'BI', 'CM', 'CV', 'CF', 'TD', 'KM', 'CD', 'CG', 'CI', 'DJ', 'EG', 'GQ', 'ER', 'ET', 'GA', 'GM', 'GH', 'GN', 'GW', 'KE', 'LS', 'LR', 'LY', 'MG', 'MW', 'ML', 'MR', 'MU', 'YT', 'MA', 'MZ', 'NA', 'NE', 'NG', 'RE', 'RW', 'SH', 'ST', 'SN', 'SC', 'SL', 'SO', 'ZA', 'SD', 'SZ', 'TZ', 'TG', 'TN', 'UG', 'EH', 'ZM', 'ZW');
		$europe = array('AX', 'AL', 'AT', 'AD', 'BY', 'BE', 'BA', 'BG', 'HR', 'CZ', 'DK', 'EE', 'FO', 'FI', 'FR', 'DE', 'GI', 'GR', 'GG', 'VA', 'HU', 'IS', 'IE', 'IM', 'IT', 'JE', 'LV', 'LI', 'LT', 'LU', 'MK', 'MT', 'MD', 'MC', 'ME', 'NL', 'NO', 'PL', 'PT', 'RO', 'RU', 'SM', 'RS', 'SK', 'SI', 'ES', 'SJ', 'SE', 'CH', 'UA', 'GB');
		$oceania = array('AS', 'AU', 'CK', 'FJ', 'PF', 'GU', 'KI', 'MH', 'FM', 'NR', 'NC', 'NZ', 'NU', 'NF', 'MP', 'PW', 'PG', 'PN', 'WS', 'SB', 'TK', 'TO', 'TV', 'UM', 'VU', 'WF');
		$northAmerica = array('AI', 'AG', 'AW', 'BS', 'BB', 'BZ', 'BM', 'VG', 'CA', 'KY', 'CR', 'CU', 'DM', 'DO', 'SV', 'GL', 'GD', 'GP', 'GT', 'HT', 'HN', 'JM', 'MQ', 'MX', 'MS', 'AN', 'NI', 'PA', 'PR', 'BL', 'KN', 'LC', 'MF', 'PM', 'VC', 'TT', 'TC', 'US', 'VI');
		$southAmerica = array('AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF', 'GY', 'PY', 'PE', 'SR', 'UY', 'VE');
		$antarctica = array('AQ', 'BV', 'TF', 'HM', 'GS');

		if(in_array($country, $asia)) $continent = 'Asia';
		if(in_array($country, $africa)) $continent = 'Africa';
		if(in_array($country, $europe)) $continent = 'Europe';
		if(in_array($country, $oceania)) $continent = 'Oceania';
		if(in_array($country, $northAmerica)) $continent = 'North America';
		if(in_array($country, $southAmerica)) $continent = 'South America';
		if(in_array($country, $antarctica)) $continent = 'Antarctica';

		return $continent;
	}
}
