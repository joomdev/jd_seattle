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
if(empty($this->statsusers)) echo acymailing_translation("ACY_NO_STATISTICS");
else{ ?>

	<script language="JavaScript" type="text/javascript">
		function statsusers() {
			var dataTable = new google.visualization.DataTable();
			dataTable.addRows(<?php echo count($this->statsusers); ?>);

			dataTable.addColumn('date');
			dataTable.addColumn('number', '<?php echo acymailing_translation('USERS',true); ?>');

			<?php
			$i = count($this->statsusers)-1;
			foreach($this->statsusers as $oneResult){
				echo "dataTable.setValue($i, 0, new Date('".substr($oneResult->subday,0,4)."','".intval(substr($oneResult->subday,5,2) - 1)."','".substr($oneResult->subday,8,2)."')); ";
				echo "dataTable.setValue($i, 1, ".intval(@$oneResult->total)."); ";
				if($i-- == 0) break;
			}
			?>
			var container = document.getElementsByClassName('acygraph')[0];
			var width = container.getBoundingClientRect().width;

			var vis = new google.visualization.ColumnChart(document.getElementById('statsusers'));
			var options = {
				height: 300,
				legend: 'none',
				vAxis: {minValue: 0},
				hAxis: {format: 'dd MMM'},
				backgroundColor: 'transparent',
				colors: ['#adccea'],
				width: width
			};

			vis.draw(dataTable, options);
		}
		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(statsusers);

	</script>
	<h1 class="acy_graphtitle"> <?php echo acymailing_translation('ACY_SUBSCRIPTION_CHRONOLOGY') ?> </h1>
	<div id="statsusers" style="width:100%;text-align:center;margin-bottom:20px"></div>
<?php } ?>
