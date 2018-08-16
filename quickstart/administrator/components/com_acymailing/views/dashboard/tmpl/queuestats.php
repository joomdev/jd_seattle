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
if(empty($this->newsletters)) echo acymailing_translation("ACY_NO_STATISTICS");
else{ ?>

	<script language="JavaScript" type="text/javascript">
		function statsqueue() {
			var dataTable = new google.visualization.DataTable();

			dataTable.addColumn('date');
			dataTable.addColumn('number', '<?php echo acymailing_translation('ACY_SENT_EMAILS'); ?>');
			dataTable.addColumn('number', '<?php echo acymailing_translation('FAILED'); ?>');

			<?php
			$i = -1;
			$statsdetailsSentDate = '';
			$mindate = 0;
			$maxdate = 0;

			foreach($this->newsletters as $oneResult){
				$date = strtotime(substr($oneResult->send_date,0,4)."-".intval(substr($oneResult->send_date,5,2))."-".substr($oneResult->send_date,8,2));
				if(empty($mindate) || $date < $mindate) $mindate = $date;
				if(empty($maxdate) || $date > $maxdate) $maxdate = $date;



				if($statsdetailsSentDate != $oneResult->send_date){
					$i++;
					echo 'dataTable.addRow();';
					echo "dataTable.setValue($i, 0, new Date(".$date."*1000));";
					$statsdetailsSentDate = $oneResult->send_date;
				}
				echo "dataTable.setValue($i, 1, ".intval(@$oneResult->total)."); ";
				echo "dataTable.setValue($i, 2, ".intval(@$oneResult->nbFailed)."); ";
			}
			?>

			var container = document.getElementsByClassName('acygraph')[0];
			var width = container.getBoundingClientRect().width;

			var vis = new google.visualization.ColumnChart(document.getElementById('statsqueue'));
			var options = {
				height: 400,
				width: width,
				backgroundColor: 'transparent',
				hAxis: {
					format: ' MMM d, y',
					maxValue: new Date(<?php echo $maxdate+86400; ?> * 1000),
					minValue: new Date(<?php echo $mindate-86400; ?> * 1000)
				},
				colors: ['#adccea', '#ed8585']
			};

		vis.draw(dataTable, options);
		}
		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(statsqueue);

	</script>
	<h1 class="acy_graphtitle"> <?php echo acymailing_translation('ACY_NEWSLETTER_STATUS') ?> </h1>
	<div id="statsqueue" style="text-align:center;width:100%,margin-bottom:20px"></div>
<?php } ?>
