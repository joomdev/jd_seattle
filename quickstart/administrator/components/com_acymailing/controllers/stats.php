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

class StatsController extends acymailingController{

	var $aclCat = 'statistics';

	function detaillisting(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'detaillisting'  );
		return parent::display();
	}

	function unsubscribed(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'unsubscribed'  );
		return parent::display();
	}

	function forward(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'forward'  );
		return parent::display();
	}

	function unsubchart(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'unsubchart'  );
		return parent::display();
	}

	function mailinglist(){
		if(!$this->isAllowed('statistics','manage')) return;
		acymailing_setVar( 'layout', 'mailinglist'  );
		return parent::display();
	}

	function remove(){
		if(!$this->isAllowed('statistics','delete')) return;
		acymailing_checkToken();

		$cids = acymailing_getVar('array',  'cid', array(), '');

		$class = acymailing_get('class.stats');
		$num = $class->delete($cids);

		acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS',$num), 'message');

		return $this->listing();
	}

	function export(){
		$selectedMail = acymailing_getVar('int', 'filter_mail', 0);
		$selectedStatus = acymailing_getVar('string', 'filter_status', '');
		$selectedBounce = acymailing_getVar('string', 'filter_bounce', '');

		$filters = array();
		if(!empty($selectedMail)) $filters[] = 'userstats.mailid = '.$selectedMail;
		if(!empty($selectedStatus)){
			if($selectedStatus == 'bounce') $filters[] = 'userstats.bounce > 0';
			elseif($selectedStatus == 'open') $filters[] = 'userstats.open > 0';
			elseif($selectedStatus == 'notopen') $filters[] = 'userstats.open < 1';
			elseif($selectedStatus == 'failed') $filters[] = 'userstats.fail > 0';
		}
		if(!empty($selectedStatus) && $selectedStatus == 'bounce' && !empty($selectedBounce)) $filters[] = "userstats.bouncerule = ".acymailing_escapeDB($selectedBounce);

		$query = 'FROM `#__acymailing_userstats` as userstats JOIN `#__acymailing_subscriber` as s ON s.subid = userstats.subid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';

		acymailing_session();
		$_SESSION['acymailing']['acyexportquery'] = $query;

		acymailing_redirect(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'data&task=export&sessionquery=1', acymailing_isNoTemplate(),true));
	}

	public function exportUnsubscribed(){
		return $this->exportData('unsubscribed');
	}


	public function exportForward(){
		return $this->exportData('forward');
	}

	private function exportData($action){
		$selectedMail = acymailing_getVar('int', 'filter_mail', 0);
		$filters = array();
		$filters[] = "hist.action = ".acymailing_escapeDB($action);
		if(!empty($selectedMail)) $filters[] = 'hist.mailid = '.intval($selectedMail);

		$query = 'FROM #__acymailing_history as hist JOIN #__acymailing_mail as b on hist.mailid = b.mailid JOIN #__acymailing_subscriber as s on hist.subid = s.subid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (',$filters).')';

		acymailing_session();
		$_SESSION['acymailing']['acyexportquery'] = $query;
		
		acymailing_redirect(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'data&task=export&sessionquery=1',true,true));
	}

	function exportglobal(){
		$extraJoin = '';
		$nlCondition = array();
		$cids = acymailing_getVar('none', 'cid');
		acymailing_arrayToInteger($cids);
		if(!empty($cids)){
			$nlCondition[] = 'a.mailid IN (' . implode(', ', $cids) . ')';
		}elseif (!acymailing_isAdmin()) {
			$listClass = acymailing_get('class.list');
			$lists = $listClass->getFrontendLists('listid');

			$frontListsIds = array_keys($lists);
			$extraJoin = " JOIN #__acymailing_listmail AS lm ON a.mailid = lm.mailid";
			$filters[] = 'lm.listid IN (' . implode(',', $frontListsIds) . ')';
		}

		$query = 'SELECT b.subject, a.senddate, a.* , a.bouncedetails 
					FROM #__acymailing_stats AS a 
					JOIN #__acymailing_mail AS b ON a.mailid = b.mailid '.$extraJoin;
		if(!empty($nlCondition)) $query .= ' WHERE '.implode(' AND ', $nlCondition);
		$query .= ' ORDER BY a.senddate DESC';
		
		$mydata = acymailing_loadObjectList($query);

		$exportHelper = acymailing_get('helper.export');
		$config = acymailing_config();
		$encodingClass = acymailing_get('helper.encoding');
		$exportHelper->addHeaders('globalStatistics_' . date('m_d_y'));

		$eol= "\r\n";
		$before = '"';
		$separator = '"'.str_replace(array('semicolon','comma'),array(';',','), $config->get('export_separator',';')).'"';
		$exportFormat = $config->get('export_format','UTF-8');
		$after = '"';

		$forwardEnabled = $config->get('forward', 0);
		$titles = array(acymailing_translation( 'JOOMEXT_SUBJECT'), acymailing_translation( 'SEND_DATE' ), acymailing_translation( 'OPEN_UNIQUE' ), acymailing_translation('OPEN_TOTAL'), acymailing_translation('OPEN').' (%)');
		if(acymailing_level(1)) array_push($titles, acymailing_translation('UNIQUE_HITS'), acymailing_translation('TOTAL_HITS'), acymailing_translation( 'CLICKED_LINK' ).' (%)');
		array_push($titles, acymailing_translation( 'UNSUBSCRIBE' ), acymailing_translation( 'UNSUBSCRIBE' ).' (%)');
		if(acymailing_level(1) && $forwardEnabled == 1) array_push($titles, acymailing_translation( 'FORWARDED' ));
		array_push($titles, acymailing_translation( 'SENT_HTML' ), acymailing_translation( 'SENT_TEXT' ));
		if(acymailing_level(3))  array_push($titles,acymailing_translation( 'BOUNCES' ), acymailing_translation( 'BOUNCES' ).' (%)');
		array_push($titles, acymailing_translation( 'FAILED' ), acymailing_translation( 'ACY_ID' ));

		$titleLine = $before.implode($separator, $titles).$after.$eol;
		echo $titleLine;

		foreach($mydata as $nl){
			$line = $nl->subject . $separator;
			$line.= acymailing_getDate($nl->senddate) . $separator;
			$line.= $nl->openunique . $separator;
			$line.= $nl->opentotal . $separator;
			$cleanSent = $nl->senthtml + $nl->senttext;
			if(acymailing_level(3)) $cleanSent = $cleanSent - $nl->bounceunique;
			$prct = (!empty($cleanSent)? round($nl->openunique/$cleanSent*100,2):'-');
			$line.= $prct . '%' . $separator;
			if(acymailing_level(1)){
				$line.= $nl->clickunique . $separator;
				$line.= $nl->clicktotal . $separator;
				$prct = (!empty($cleanSent)? round($nl->clickunique/$cleanSent*100,2):'-');
				$line.= $prct . '%' . $separator;
			}
			$line.= $nl->unsub . $separator;
			$prct = (!empty($cleanSent)? round($nl->unsub/$cleanSent*100,2):'-');
			$line.= $prct . '%' . $separator;
			if(acymailing_level(1) && $forwardEnabled == 1){
				$line.= $nl->forward . $separator;
			}
			$line.= $nl->senthtml . $separator;
			$line.= $nl->senttext . $separator;
			if(acymailing_level(3)){
				$line.= $nl->bounceunique . $separator;
				$prct = (!empty($nl->senthtml)? round($nl->bounceunique/($nl->senthtml+$nl->senttext)*100,2):'-');
				$line.= $prct . '%' . $separator;
			}
			$line.= $nl->fail . $separator;
			$line.= $nl->mailid;

			$line = $before.$encodingClass->change($line, 'UTF-8', $exportFormat).$after.$eol;
			echo $line;
		}
		exit;
	}

	function compare(){
		if(!$this->isAllowed('statistics','manage')) return;

		$ids = acymailing_getVar('array', 'cid', array(), '');
		acymailing_arrayToInteger($ids);

		if(empty($_SESSION['acycomparison'])){
			$_SESSION['acycomparison'] = $ids;
		}else{
			$_SESSION['acycomparison'] = array_unique(array_merge($_SESSION['acycomparison'], $ids));
		}

		if(count($_SESSION['acycomparison']) > 5){
			acymailing_enqueueMessage(acymailing_translation('ACY_MAX_COMPARE'), 'warning');
			$_SESSION['acycomparison'] = array_slice($_SESSION['acycomparison'], 0, 5);
		}elseif(count($_SESSION['acycomparison']) < 2){
			acymailing_enqueueMessage(acymailing_translation('ACY_MIN_COMPARE'), 'info');
			acymailing_setVar( 'layout', 'listing'  );
			return parent::display();
		}

		acymailing_setVar( 'layout', 'compare'  );
		return parent::display();
	}

	function addcompare(){
		if(!$this->isAllowed('statistics','manage')) return;

		$ids = acymailing_getVar('array', 'cid', array(), '');
		acymailing_arrayToInteger($ids);

		if(empty($_SESSION['acycomparison'])){
			$_SESSION['acycomparison'] = $ids;
		}else{
			$_SESSION['acycomparison'] = array_unique(array_merge($_SESSION['acycomparison'], $ids));
		}

		if(count($_SESSION['acycomparison']) > 5){
			acymailing_enqueueMessage(acymailing_translation('ACY_MAX_COMPARE'), 'warning');
			$_SESSION['acycomparison'] = array_slice($_SESSION['acycomparison'], 0, 5);
		}elseif(count($_SESSION['acycomparison']) < 2){
			acymailing_enqueueMessage(acymailing_translation('ACY_MIN_COMPARE'), 'info');
		}

		acymailing_setVar( 'layout', 'listing'  );
		return parent::display();
	}

	function resetcompare(){
		if(!$this->isAllowed('statistics','manage')) return;

		$_SESSION['acycomparison'] = array();

		acymailing_setVar( 'layout', 'listing'  );
		return parent::display();
	}

	function opendays(){
		$tags = acymailing_getVar('string', 'tags', '');
		if(empty($tags)){
			$intoQuery = 'SELECT opendate FROM ' . acymailing_table('userstats') . ' WHERE opendate > 0 LIMIT 5000';
			$statsDays = acymailing_loadObjectList('SELECT COUNT(*) AS nb, FROM_UNIXTIME(opendate,\'%w\') AS day FROM ('.$intoQuery.') AS a GROUP BY day', 'day');
		}else{
			$tags = explode(',', $tags);
			acymailing_arrayToInteger($tags);

			$tagsData = acymailing_loadObjectList('SELECT * FROM ' . acymailing_table('tagmail') . ' WHERE tagid IN ('.implode(',', $tags).')');

			$mails = array();
			foreach($tagsData as $oneData){
				$mails[$oneData->mailid][] = $oneData->tagid;
			}

			foreach($mails as $i => $oneMail){
				foreach($tags as $oneTag) {
					if(!in_array($oneTag, $oneMail)){
						unset($mails[$i]);
						break;
					}
				}
			}

			$eligibleMails = array_keys($mails);
			if(empty($eligibleMails)){
				$statsDays = array();
			}else {
				$intoQuery = 'SELECT opendate 
						  FROM ' . acymailing_table('userstats') . '
						  WHERE opendate > 0 AND mailid IN (' . implode(',', $eligibleMails) . ') 
						  LIMIT 5000';

				$statsDays = acymailing_loadObjectList('SELECT COUNT(*) AS nb, FROM_UNIXTIME(opendate,\'%w\') AS day FROM ('.$intoQuery.') AS a GROUP BY day', 'day');
			}
		}

		$total = 0;
		foreach ($statsDays as $oneDay) {
			$total += $oneDay->nb;
		}

		if(!empty($statsDays[0])){
			$statsDays[7] = $statsDays[0];
			unset($statsDays[0]);
		}

		$days = array('ACY_MONDAY', 'ACY_TUESDAY', 'ACY_WEDNESDAY', 'ACY_THURSDAY', 'ACY_FRIDAY', 'ACY_SATURDAY', 'ACY_SUNDAY');
		foreach($days as $i => &$text){
			$text = "['".acymailing_translation($text, true)."', ".(empty($statsDays[$i+1]) ? 0 : intval($statsDays[$i+1]->nb * 100 / $total))."]";
		}
		?>
		<div id="chart"></div>
		<script language="JavaScript" type="text/javascript">
			function drawChart(){
				var dataTable = new google.visualization.DataTable();

				dataTable.addColumn('string', '');
				dataTable.addColumn('number', '');
				dataTable.addRows([<?php echo implode(',', $days); ?>]);

				var options = {
					height: 300,
					legend: 'none',
					legendTextStyle: {
						color: '#333333'
					},
					legend: {position: 'none'},
					axes: {
						x: {
							0: {side: 'top'}
						}
					},
					vAxis: {
						format: '#\'%\''
					}
				};

				var chart = new google.charts.Bar(document.getElementById('chart'));
				chart.draw(dataTable, google.charts.Bar.convertOptions(options));
			}
			drawChart();
		</script>
<?php
		exit;
	}

	function detecttimeout(){
		$config = acymailing_config();
		if($config->get('security_key') != acymailing_getVar('string', 'seckey')) die('wrong key');
		acymailing_query("REPLACE INTO `#__acymailing_config` (`namekey`,`value`) VALUES ('max_execution_time','5'), ('last_maxexec_check','".time()."')");
		@ini_set('max_execution_time',600);
		@ignore_user_abort(true);
		$i = 0;
		while($i < 480){
			sleep(8);
			$i += 10;
			acymailing_query("UPDATE `#__acymailing_config` SET `value` = '".intval($i)."' WHERE `namekey` = 'max_execution_time'");
			acymailing_query("UPDATE `#__acymailing_config` SET `value` = '".time()."' WHERE `namekey` = 'last_maxexec_check'");
			sleep(2);
		}
		exit;
	}
}
