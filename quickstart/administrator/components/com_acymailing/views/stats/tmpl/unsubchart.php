<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php if(empty($this->unsubreasons)) return; ?>
<script language="JavaScript" type="text/javascript">
	function drawChart(){
		var dataTable = new google.visualization.DataTable();
		dataTable.addColumn('string');
		dataTable.addColumn('number');

		<?php
		$i = 0;
		$numberReasons = count($this->unsubreasons);
		foreach($this->unsubreasons as $oneRule => $total ){
				if($total < 2 && $numberReasons > 10) continue;
			?>
		dataTable.addRows(1);
		dataTable.setValue(<?php echo $i ?>, 0, '<?php echo addslashes($oneRule); ?>');
		dataTable.setValue(<?php echo $i ?>, 1, <?php echo intval($total); ?>);
		<?php 	$i++;
		} ?>

		var vis = new google.visualization.ColumnChart(document.getElementById('unsubchart'));
		var options = {
			width: '100%', height: 400, is3D: true, legendTextStyle: {color: '#333333'}, legend: 'none'
		};
		vis.draw(dataTable, options);
	}
	google.load("visualization", "1", {packages: ["corechart"]});
	google.setOnLoadCallback(drawChart);
</script>
<div id="acy_content">
	<div id="iframedoc"></div>
	<div id="unsubchart"></div>
	<table id="unsublist" class="adminlist table table-striped">
		<?php

		arsort($this->unsubreasons);
		foreach($this->unsubreasons as $oneRule => $total){
			if(preg_match('#^[A-Z_]*$#', $oneRule)) $oneRule = acymailing_translation($oneRule);
			echo '<tr><td>'.$total.'</td><td>'.$oneRule.'</td></tr>';
		}
		?>
	</table>
</div>
