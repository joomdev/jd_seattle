<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->geoloc_details)){
	$config = acymailing_config();
	$google_map_api_key = $config->get('google_map_api_key');
	if(empty($google_map_api_key)){
		acymailing_display('<a href="'.acymailing_completeLink('cpanel').'" onclick="localStorage.setItem(\'acyconfig_tab\', \'config_subscription\');">'.acymailing_translation('ACY_NEED_GOOGLE_MAP_API_KEY').'</a>', 'info');
	}else{ ?>
		<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
		<script language="javascript" type="text/javascript">
			google.charts.load('current', {
				packages: ['geochart', 'corechart'],
				mapsApiKey: '<?php echo $google_map_api_key; ?>'
			});
			google.charts.setOnLoadCallback(drawMarkersMap);

			var chart;
			var data;

			var mapOptions = {
				legend: 'none', height: 400, displayMode: 'markers', colorAxis: {colors: ['', '#a8a12c']}, sizeAxis: {minSize: 2, maxSize: 10, minValue: 1, maxValue: 15}, enableRegionInteractivity: 'true', backgroundColor: 'transparent', region: '<?php echo $this->geoloc_region; ?>'
			};
			function drawMarkersMap(){
				data = new google.visualization.DataTable();
				data.addColumn('string', 'Address');
				data.addColumn('number', 'Color');
				data.addColumn('number', 'Size');
				data.addColumn({type: 'string', role: 'tooltip'});
				<?php
				$myData = array();
				foreach($this->geoloc_city as $key => $city){
					$toolTipTxt = str_replace("'", "\'", acymailing_translation('GEOLOC_NB_USERS')).': '.$this->geoloc_details[$key];
					$myData[] = "['".str_replace("'", "\'", $this->geoloc_addresses[$key])."', 1, ".$this->geoloc_details[$key].", '".$toolTipTxt."']";
				}
				echo "data.addRows([".implode(", ", $myData)."]);";
				?>

				chart = new google.visualization.GeoChart(document.getElementById('mapGeoloc_div'));
				chart.draw(data, mapOptions);
			}

		</script>
		<h1 class="acy_graphtitle"><?php echo acymailing_translation_sprintf('ACY_SUBSCRIBERS_LOCATIONS', $this->nbUsersToGet) ?></h1>
		<div id="mapGeoloc_div"></div>
		<?php
	}
} ?>
