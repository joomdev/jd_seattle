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
if(empty($this->listStatusData)) echo acymailing_translation("ACY_NO_STATISTICS");
else{

	$data = "['List Name', '".acymailing_translation('UNSUBSCRIBED')."', '".acymailing_translation('PENDING_SUBSCRIPTION')."', '".acymailing_translation('SUBSCRIBED')."',],";
	foreach($this->listStatusData as $listName => $oneStat){
		$data .= "['".addslashes($listName)."', ".(empty($oneStat[-1]) ? 0 : $oneStat[-1]).", ".(empty($oneStat[2]) ? 0 : $oneStat[2]).", ".(empty($oneStat[1]) ? 0 : $oneStat[1]).",],";
	}
	?>

	<script language="JavaScript" type="text/javascript">
		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawChart);

		function drawChart() {
			var data = google.visualization.arrayToDataTable([

				<?php echo rtrim($data, ','); ?>
			]);

			var container = document.getElementsByClassName('acygraph')[0];
			var width = container.getBoundingClientRect().width;

			var view = new google.visualization.DataView(data);
			var options = {
				height: 450,
				width: width,
				isStacked: true,
				backgroundColor: 'transparent',
				colors: ['#ed8585', '#adccea', '#dde281'],
				hAxis: {slantedText: true, slantedTextAngle: 40, textStyle: {fontSize: 13}}
			};
			var chart = new google.visualization.ColumnChart(document.getElementById("liststats"));
			chart.draw(view, options);
		}
	</script>
	<h1 class="acy_graphtitle"> <?php echo acymailing_translation('ACY_SUB_STATUS_PER_LIST') ?> </h1>
	<div id="liststats" style="text-align:center;margin-bottom:20px"></div>
<?php } ?>
