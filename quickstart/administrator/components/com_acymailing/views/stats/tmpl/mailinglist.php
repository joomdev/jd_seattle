<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acy_content">
	<?php
	if(empty($this->isData)) return;
	if(!acymailing_isAdmin() && acymailing_isNoTemplate()) include(dirname(__FILE__).DS.'menu.mailinglist.php'); ?>
	<style type="text/css">
		.mailingListChart{
			float: left;
			margin: 2px;
		}

		.noDataChart{
			display: none;
		}
	</style>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script language="JavaScript" type="text/javascript">
		function getDataMailSent(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Name');
			data.addColumn('number', 'Value');
			data.addRows(<?php echo count($this->mydata); ?>);
			<?php
			$array_detail = array();
			$i = 0;
			foreach($this->mydata as $list){
				echo 'data.setValue('. $i .', 0, \''. str_replace("'", "\'", $list['listname']) .'\'); ';
				echo 'data.setValue('. $i .', 1, '. $list['nbMailSent'] .'); ';
				$i++;
				$nbSentRatio = number_format($list['nbMailSent'] / $this->totalSent * 100, 1);
				array_push($array_detail, $list['listname'] .': '. $list['nbMailSent'] . ' ('. $nbSentRatio .'%)');
			}
			$detailSent = implode("\n", $array_detail); ?>
			return data;
		}

		function drawMailSent(){
			var vis = new google.visualization.PieChart(document.getElementById('chartMailSent'));
			var options = {
				width: 350, height: 350, colors: [<?php echo $this->listColors; ?>], legend: 'right', title: '<?php echo str_replace("'", "\'", acymailing_translation('ACY_SENT_EMAILS')); ?>', legendTextStyle: {color: '#333333'}, pieSliceText: 'value', is3D: true
			};
			vis.draw(getDataMailSent(), options);
		}

		var optionsColumnChart = {
			width: 350, height: 350, colors: [<?php echo $this->listColors; ?>], legend: 'none', vAxis: {minValue: 0, maxValue: 100}
		};

		function getDataOpen(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Columns');
			<?php foreach($this->mydata as $list){
				echo 'data.addColumn(\'number\', \''. str_replace("'", "\'", $list['listname']) .'\'); ';
			} ?>
			data.addRows(1);
			data.setValue(0, 0, '');
			<?php $i = 1;
			$array_detail = array();
			$dataOpen = false;
			foreach($this->mydata as $list){
				if(!$dataOpen && $list['nbOpenRatio'] > 0) $dataOpen = true;
				echo 'data.setValue(0,'. $i .', '. $list['nbOpenRatio'] .'); ';
				array_push($array_detail, $list['listname'] .': '. $list['nbOpen'] .' ('. $list['nbOpenRatio'] .'%)');
				$i++;
			}
			$detailOpen = implode("\n", $array_detail); ?>
			return data;
		}
		function drawOpen(){
			var vis = new google.visualization.ColumnChart(document.getElementById('chartMailOpen'));
			optionsColumnChart['title'] = '<?php echo str_replace("'", "\'", acymailing_translation('OPEN')); ?> (%)';
			<?php if(!$dataOpen) {echo	"optionsColumnChart['vAxis'] = {minValue:0, maxValue:100};";}
			else echo	"optionsColumnChart['vAxis'] = {minValue:0};"; ?>
			vis.draw(getDataOpen(), optionsColumnChart);
		}

		function getDataBounce(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Columns');
			<?php foreach($this->mydata as $list){
				echo 'data.addColumn(\'number\', \''. str_replace("'", "\'", $list['listname']) .'\'); ';
			} ?>
			data.addRows(1);
			data.setValue(0, 0, '');
			<?php $i = 1;
			$array_detail = array();
			$dataBounce = false;
			foreach($this->mydata as $list){
				if(!$dataBounce && $list['nbBounceRatio'] > 0) $dataBounce = true;
				echo 'data.setValue(0,'. $i .', '. $list['nbBounceRatio'] .'); ';
				array_push($array_detail, $list['listname'] .': '. $list['nbBounce'] .' ('. $list['nbBounceRatio'] .'%)');
				$i++;
			}
			$detailBounce = implode("\n", $array_detail); ?>
			return data;
		}
		function drawBounce(){
			var vis = new google.visualization.ColumnChart(document.getElementById('chartBounce'));
			optionsColumnChart['title'] = '<?php echo str_replace("'", "\'", acymailing_translation('BOUNCES')); ?> (%)';
			<?php if(!$dataBounce) {echo	"optionsColumnChart['vAxis'] = {minValue:0, maxValue:100};";}
			else echo	"optionsColumnChart['vAxis'] = {minValue:0};"; ?>
			vis.draw(getDataBounce(), optionsColumnChart);
		}

		function getDataClic(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Columns');
			<?php foreach($this->mydata as $list){
				echo 'data.addColumn(\'number\', \''. str_replace("'", "\'", $list['listname']) .'\'); ';
			} ?>
			data.addRows(1);
			data.setValue(0, 0, '');
			<?php $i = 1;
			$array_detail = array();
			$dataClic = false;
			foreach($this->mydata as $list){
				if(!$dataClic && $list['nbClicRatio'] > 0) $dataClic = true;
				echo 'data.setValue(0,'. $i .', '. $list['nbClicRatio'] .'); ';
				array_push($array_detail, $list['listname'] .': '. $list['nbClic'] .' ('. $list['nbClicRatio'] .'%)');
				$i++;
			}
			$detailClic = implode("\n", $array_detail); ?>
			return data;
		}
		function drawClic(){
			var vis = new google.visualization.ColumnChart(document.getElementById('chartClic'));
			optionsColumnChart['title'] = '<?php echo str_replace("'", "\'", acymailing_translation('CLICKED_LINK')); ?> (%)';
			<?php if(!$dataClic) {echo	"optionsColumnChart['vAxis'] = {minValue:0, maxValue:100};";}
			else echo	"optionsColumnChart['vAxis'] = {minValue:0};"; ?>
			vis.draw(getDataClic(), optionsColumnChart);
		}

		function getDataUnsub(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Columns');
			<?php foreach($this->mydata as $list){
				echo 'data.addColumn(\'number\', \''. str_replace("'", "\'", $list['listname']) .'\'); ';
			} ?>
			data.addRows(1);
			data.setValue(0, 0, '');
			<?php $i = 1;
			$array_detail = array();
			$dataUnsub = false;
			foreach($this->mydata as $list){
				if(!$dataUnsub && $list['nbUnsubRatio'] > 0) $dataUnsub = true;
				echo 'data.setValue(0,'. $i .', '. $list['nbUnsubRatio'] .'); ';
				array_push($array_detail, $list['listname'] .': '. $list['nbUnsub'] .' ('. $list['nbUnsubRatio'] .'%)');
				$i++;
			}
			$detailUnsub = implode("\n", $array_detail); ?>
			return data;
		}
		function drawUnsub(){
			var vis = new google.visualization.ColumnChart(document.getElementById('chartUnsubscribed'));
			optionsColumnChart['title'] = '<?php echo str_replace("'", "\'", acymailing_translation('UNSUBSCRIBED')); ?> (%)';
			<?php if(!$dataUnsub) {echo	"optionsColumnChart['vAxis'] = {minValue:0, maxValue:100};";}
			else echo	"optionsColumnChart['vAxis'] = {minValue:0};"; ?>
			vis.draw(getDataUnsub(), optionsColumnChart);
		}

		function getDataForward(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Columns');
			<?php foreach($this->mydata as $list){
				echo 'data.addColumn(\'number\', \''. str_replace("'", "\'", $list['listname']) .'\'); ';
			} ?>
			data.addRows(1);
			data.setValue(0, 0, '');
			<?php $i = 1;
			$array_detail = array();
			$dataForward = false;
			foreach($this->mydata as $list){
				echo 'data.setValue(0,'. $i .', '. $list['nbForward'] .'); ';
				if(!$dataForward && $list['nbForward'] != 0) $dataForward = true;
				array_push($array_detail, $list['listname'] .': '. $list['nbForward']);
				$i++;
			}
			$detailForward = implode("\n", $array_detail); ?>
			return data;
		}
		function drawForward(){
			var vis = new google.visualization.ColumnChart(document.getElementById('chartForward'));
			optionsColumnChart['title'] = '<?php echo str_replace("'", "\'", acymailing_translation('FORWARDED')); ?>';
			<?php if(!$dataForward) {echo	"optionsColumnChart['vAxis'] = {minValue:0, maxValue:100};";}
			else echo	"optionsColumnChart['vAxis'] = {minValue:0};"; ?>
			vis.draw(getDataForward(), optionsColumnChart);
		}

		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawMailSent);
		google.setOnLoadCallback(drawOpen);
		google.setOnLoadCallback(drawBounce);
		google.setOnLoadCallback(drawClic);
		google.setOnLoadCallback(drawUnsub);
		google.setOnLoadCallback(drawForward);

		function showData(typeGraph){
			if(document.getElementById('exporteddata_' + typeGraph).style.display == 'none'){
				document.getElementById('exporteddata_' + typeGraph).style.display = '';
			}else{
				document.getElementById('exporteddata_' + typeGraph).style.display = 'none';
			}
		}
	</script>

	<div id="iframedoc"></div>
	<?php echo acymailing_translation('SEND_DATE').' : <span class="statnumber">'.acymailing_getDate($this->mailing->senddate); ?></span><br/>

	<div class="acychart mailingListChart" width="350px" height="350px">
		<div id="chartMailSent"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('sent');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="25" rows="9" id="exporteddata_sent" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailSent; ?></textarea>
	</div>
	<div class="acychart mailingListChart" width="350px" height="350px">
		<div id="chartMailOpen"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('open');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="35" rows="9" id="exporteddata_open" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailOpen; ?></textarea>
	</div>

	<!--[if !IE]><!-->
	<div style="page-break-after: always;">&nbsp;</div>
	<!--<![endif]-->
	<div class="acychart mailingListChart" width="350px" height="350px">
		<div id="chartClic"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('clic');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="35" rows="9" id="exporteddata_clic" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailClic; ?></textarea>
	</div>
	<div class="acychart mailingListChart <?php echo($dataForward == false ? 'noDataChart' : ''); ?>" width="350px" height="350px">
		<div id="chartForward"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('forward');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="35" rows="9" id="exporteddata_forward" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailClic; ?></textarea>
	</div>

	<?php echo($dataForward != false ? '<!--[if !IE]><!--><div style="page-break-after: always">&nbsp;</div><!--<![endif]-->' : ''); ?>
	<div class="acychart mailingListChart" width="350px" height="350px">
		<div id="chartBounce"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('bounce');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="35" rows="9" id="exporteddata_bounce" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailBounce; ?></textarea>
	</div>
	<?php echo($dataForward == false ? '<!--[if !IE]><!--><div style="page-break-after: always">&nbsp;</div><!--<![endif]-->' : ''); ?>
	<div class="acychart mailingListChart" width="350px" height="350px">
		<div id="chartUnsubscribed"></div>
		<img style="position:relative;cursor:pointer;margin-top:-30px;" onclick="showData('unsub');" class="donotprint" src="<?php echo ACYMAILING_IMAGES.'smallexport.png'; ?>" alt="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" title="<?php echo acymailing_translation('VIEW_DETAILS', true) ?>" width="30px"/>
		<textarea cols="35" rows="9" id="exporteddata_unsub" style="display:none;position:absolute;margin-top:-160px;z-index:2;width:300px;" class="donotprint"><?php echo $detailUnsub; ?></textarea>
	</div>
</div>
