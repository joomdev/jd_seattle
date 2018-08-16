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

class acyuserHelper{

	function __construct($config = array()){
		global $acymailingCmsUserVars;
		$this->cmsUserVars = $acymailingCmsUserVars;
	}

	function getIP(){
		$ip = '';
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strlen($_SERVER['HTTP_X_FORWARDED_FOR']) > 6){
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif(!empty($_SERVER['HTTP_CLIENT_IP']) && strlen($_SERVER['HTTP_CLIENT_IP']) > 6){
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['REMOTE_ADDR']) && strlen($_SERVER['REMOTE_ADDR']) > 6){
			$ip = $_SERVER['REMOTE_ADDR'];
		}//endif

		return strip_tags($ip);
	}

	function validEmail($email, $extended = false){
		if(empty($email) || !is_string($email)) return false;

		if(!preg_match('/^'.acymailing_getEmailRegex().'$/i', $email)) return false;

		if(!$extended) return true;


		$config = acymailing_config();
		if($config->get('email_checkpopmailclient', false)){
			if(preg_match('#^.{1,5}@(gmail|yahoo|aol|hotmail|msn|ymail)#i', $email)){
				return false;
			}
		}

		if($config->get('email_checkdomain', false) && function_exists('getmxrr')){
			$domain = substr($email, strrpos($email, '@') + 1);
			$mxhosts = array();
			$checkDomain = getmxrr($domain, $mxhosts);
			if(!empty($mxhosts) && strpos($mxhosts[0], 'hostnamedoesnotexist')){
				array_shift($mxhosts);
			}
			if(!$checkDomain || empty($mxhosts)){
				$dns = @dns_get_record($domain, DNS_A);
				$domainChanged = true;
				foreach($dns as $oneRes){
					if(strtolower($oneRes['host']) == strtolower($domain)){
						$domainChanged = false;
					}
				}
				if(empty($dns) || $domainChanged){
					return false;
				}
			}
		}
		$object = new stdClass();
		$object->IP = $this->getIP();
		$object->emailAddress = $email;

		if($config->get('email_botscout', false)){
			$botscoutClass = new acybotscout();
			$botscoutClass->apiKey = $config->get('email_botscout_key');
			if(!$botscoutClass->getInfo($object)){
				return false;
			}
		}

		if($config->get('email_stopforumspam', false)){
			$email_stopforumspam = new acystopforumspam();
			if(!$email_stopforumspam->getInfo($object)){
				return false;
			}
		}

		if($config->get('email_iptimecheck', 0)){
			$lapseTime = time() - 7200;
			$nbUsers = acymailing_loadResult('SELECT COUNT(*) FROM #__acymailing_subscriber WHERE created > '.intval($lapseTime).' AND ip = '.acymailing_escapeDB($object->IP));
			if($nbUsers >= 3){
				return false;
			}
		}

		return true;
	}

	function getUserGroups($userid){
		if(ACYMAILING_J16){
			$groups = acymailing_loadObjectList('SELECT ug.id, ug.title FROM #__usergroups AS ug JOIN #__user_usergroup_map AS ugm ON ug.id = ugm.group_id WHERE ugm.user_id = '.intval($userid));
		}else{
			$groups = acymailing_loadObjectList('SELECT gid AS id, userType AS title FROM '.acymailing_table($this->cmsUserVars->table, false).' WHERE '.$this->cmsUserVars->id.' = '.intval($userid));
		}
		return $groups;
	}

	function exportdata($id){
		if(empty($id)) die('No user found');

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriber = $subscriberClass->get($id);
		if(empty($subscriber)) die('No user found');
		$subscriber = get_object_vars($subscriber);

		acymailing_displayErrors();

		$dateFields = array('created', 'confirmed_date', 'lastopen_date', 'lastclick_date', 'lastsent_date', 'userstats_opendate', 'userstats_senddate', 'urlclick_date', 'hist_date');
		$excludedFields = array('userid', 'subid', 'html', 'key', 'source', 'filterflags');

        $exportFiles = array();

		$xml = new SimpleXMLElement('<xml/>');
		$userNode = $xml->addChild('user');

        $fields = acymailing_loadObjectList('SELECT namekey, type, value, options FROM #__acymailing_fields', 'namekey');
        $uploadFolder = trim(acymailing_cleanPath(html_entity_decode(acymailing_getFilesFolder())), DS.' ').DS;

		foreach($subscriber as $column => $value){
			if(in_array($column, $excludedFields) || strlen($value) == 0) continue;
			if(in_array($column, $dateFields)){
				if(empty($value)) continue;
				$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
			}

            if(empty($fields[$column])){
			    $userNode->addChild($column, htmlspecialchars($value));
                continue;
            }

			if(in_array($fields[$column]->type, array("singledropdown", "multipledropdown", "radio", "checkbox"))){
				$selectedValues = explode(',', $value);

                if(!empty($fields[$column]->value)) {
                    $options = explode("\n", $fields[$column]->value);
                    foreach ($options as $i => $oneOption) {
                        $options[$i] = explode('::', $oneOption);
                    }
                }else{
                    $fields[$column]->options = unserialize($fields[$column]->options);

                    if(!empty($fields[$column]->options['dbName']) && !empty($fields[$column]->options['tableName']) && !empty($fields[$column]->options['valueFromDb']) && !empty($fields[$column]->options['titleFromDb'])) {
                        $valueField = acymailing_secureField($fields[$column]->options['valueFromDb']);
                        $titleField = acymailing_secureField($fields[$column]->options['titleFromDb']);
                        $fieldsClass = acymailing_get('class.fields');
                        $options = $fieldsClass->_getDataFromDB($fields[$column], $valueField, $titleField);
                    }
                }

				foreach($selectedValues as &$oneValue){
                    foreach($options as $oneOption){
                        if(is_object($oneOption)){
                            if ($oneValue == $oneOption->$valueField) {
                                $oneValue = $oneOption->$titleField;
                                break;
                            }
                        }else {
                            if ($oneValue == $oneOption[0]) {
                                $oneValue = $oneOption[1];
                                break;
                            }
                        }
                    }
				}

                $value = implode(',', $selectedValues);
			}elseif(in_array($fields[$column]->type, array('gravatar', 'file'))){
				$data = acymailing_fileGetContent(ACYMAILING_ROOT.$uploadFolder.'userfiles'.DS.$value);
		        $value = str_replace('_', ' ', substr($value, strpos($value, '_')));
				$exportFiles[] = array('name' => $value, 'data' => $data);
                continue;
            }

			$userNode->addChild($column, htmlspecialchars($value));
		}

		$subscription = acymailing_loadObjectList('SELECT list.name, list.listid, sub.subdate, sub.unsubdate, sub.status FROM #__acymailing_listsub AS sub JOIN #__acymailing_list AS list ON list.listid = sub.listid WHERE sub.subid = '.intval($id));
		if(!empty($subscription)){
			$dateFields = array('subdate', 'unsubdate');
			$subscriptionNode = $xml->addChild('subscription');

			foreach($subscription as $oneSubscription){
				$list = $subscriptionNode->addChild('list');

				$oneSubscription = get_object_vars($oneSubscription);
				foreach($oneSubscription as $column => $value){
					if(strlen($value) == 0) continue;
					if(in_array($column, $dateFields)){
						if(empty($value)) continue;
						$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
					}

					if($column == 'subdate') $column = 'subscription_date';
					if($column == 'unsubdate') $column = 'unsubscription_date';
					if($column == 'status') $value = str_replace(array('-1', '1', '2'), array('Unsubscribed', 'Subscribed', 'Waiting for confirmation'), $value);
					$list->addChild($column, htmlspecialchars($value));
				}
			}
		}

		$geolocation = acymailing_loadObjectList('SELECT * FROM #__acymailing_geolocation WHERE geolocation_subid = '.intval($id));
		if(!empty($geolocation)){
			$dateFields = array('geolocation_created');
			$excludedFields = array('geolocation_id', 'geolocation_subid');
			$geolocNode = $xml->addChild('geolocation');

			foreach($geolocation as $onePosition){
				$position = $geolocNode->addChild('position');

				$onePosition = get_object_vars($onePosition);
				foreach($onePosition as $column => $value){
					if(in_array($column, $excludedFields) || strlen($value) == 0) continue;
					if(in_array($column, $dateFields)){
						if(empty($value)) continue;
						$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
					}

					if($column == 'geolocation_created') $column = 'date';
					if($column == 'geolocation_type') $column = 'event';
					$position->addChild(str_replace('geolocation_', '', $column), htmlspecialchars($value));
				}
			}
		}

		$history = acymailing_loadObjectList('SELECT h.action, h.date, h.ip, h.data, h.source, m.subject AS newsletter 
												FROM #__acymailing_history AS h 
												LEFT JOIN #__acymailing_mail AS m ON h.mailid = m.mailid 
												WHERE h.subid = '.intval($id));
		if(!empty($history)){
			$dateFields = array('date');
			$historyNode = $xml->addChild('history');

			foreach($history as $oneEvent){
				$event = $historyNode->addChild('event');

				$oneEvent = get_object_vars($oneEvent);
				foreach($oneEvent as $column => $value){
					if(empty($value)) continue;
					if(in_array($column, $dateFields)){
						if(empty($value)) continue;
						$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
					}

					$event->addChild($column, htmlspecialchars($value));
				}
			}
		}

		$statistics = acymailing_loadObjectList('SELECT mail.subject, stats.* FROM #__acymailing_userstats AS stats JOIN #__acymailing_mail AS mail ON mail.mailid = stats.mailid WHERE stats.subid = '.intval($id));
		if(!empty($statistics)){
			$dateFields = array('senddate', 'opendate');
			$excludedFields = array('subid');
			$statisticsNode = $xml->addChild('statistics');

			foreach($statistics as $oneStat){
				$detailedStat = $statisticsNode->addChild('email');

				$oneStat = get_object_vars($oneStat);
				foreach($oneStat as $column => $value){
					if(in_array($column, $excludedFields) || strlen($value) == 0) continue;
					if(in_array($column, $dateFields)){
						if(empty($value)) continue;
						$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
					}

					$detailedStat->addChild($column, htmlspecialchars($value));
				}
			}
		}

		$clickStats = acymailing_loadObjectList('SELECT url.url, click.date, click.ip FROM #__acymailing_urlclick AS click JOIN #__acymailing_url AS url ON url.urlid = click.urlid WHERE click.subid = '.intval($id));
		if(!empty($clickStats)){
			$dateFields = array('date');
			$clickStatsNode = $xml->addChild('click_statistics');

			foreach($clickStats as $oneClick){
				$click = $clickStatsNode->addChild('click');

				$oneClick = get_object_vars($oneClick);
				foreach($oneClick as $column => $value){
					if(strlen($value) == 0) continue;
					if(in_array($column, $dateFields)){
						if(empty($value)) continue;
						$value = acymailing_getDate($value, '%Y-%m-%d %H:%M:%S');
					}

					$click->addChild($column, htmlspecialchars($value));
				}
			}
		}

        $exportFiles[] = array('name' => 'user_data.xml', 'data' => $xml->asXML());

        $tempFolder = ACYMAILING_MEDIA.'tmp'.DS;
        acymailing_createArchive($tempFolder.'export_data_user_'.$id, $exportFiles);

		$exportHelper = acymailing_get('helper.export');
		$exportHelper->addHeaders('export_data_user_'.$id, 'zip');
        readfile($tempFolder.'export_data_user_'.$id.'.zip');

        ignore_user_abort(true);
        unlink($tempFolder.'export_data_user_'.$id.'.zip');
		exit;
	}
}

class acybotscout{

	var $apiKey = '';
	var $conn;
	var $error = '';


	function connect(){
		if(is_resource($this->conn)){
			return true;
		}

		$this->conn = fsockopen('www.botscout.com', 80, $errno, $errstr, 20);
		if(!$this->conn){
			$this->error = "Could not open connection ".$errstr;
			return false;
		}
		return true;
	}

	function getInfo(&$object){
		if(!$this->connect()){
			return true;
		}
		$result = true;

		if(!empty($object->IP) && $object->IP != '127.0.0.1'){
			$data = 'ip='.$object->IP;
			$resIP = $this->sendInfo($data);
			$result = $this->checkXML($resIP, $object) && $result;
		}
		if(!empty($object->emailAddress)){
			$data = 'mail='.$object->emailAddress;
			$resAddress = $this->sendInfo($data);
			$result = $this->checkXML($resAddress, $object) && $result;
		}

		if(is_resource($this->conn)){
			fclose($this->conn);
		}

		return $result;
	}

	function sendInfo($data){
		$res = '';
		if(!empty($this->apiKey)){
			$data .= '&key='.$this->apiKey;
		}
		$data .= '&format=xml';
		$header = "GET /test/?".$data." HTTP/1.1\r\n";
		$header .= "Host: www.botscout.com \r\n";
		$header .= "Connection: keep-alive\r\n\r\n";
		fwrite($this->conn, $header);
		while(!feof($this->conn)){
			$res .= fread($this->conn, 1024);
			if(strpos($res, "</response>")){
				break;
			}
		}
		return $res;
	}

	function checkXML($res, $object){

		if(!preg_match('#<response.*</response>#Uis', $res, $results)){
			$this->error = 'There is an error while trying to get the xml could not find "<reponse>"';
			return true;
		}

		$xml = new SimpleXMLElement($results[0]);
		if($xml->matched == "Y" && $xml->test == 'IP'){
			$this->error .= 'There is a problem with the IP : '.$object->IP.' you used to do the registration ( Spam test positive )</br>'; // Check failed. Result indicates dangerous.
			return false;
		}
		if($xml->matched == "Y" && $xml->test == 'MAIL'){
			$this->error .= 'There is a problem with the email : '.$object->emailAddress.' you entered in the form ( Spam test positive )</br>';
			return false;
		}
		return true;
	}
}


class acystopforumspam{

	var $conn;
	var $error = '';

	function connect(){
		$this->conn = fsockopen('www.stopforumspam.com', 80, $errno, $errstr, 20);
		if(!$this->conn){
			$this->error = "Could not open connection ".$errstr;
			return false;
		}
		return true;
	}

	function getInfo(&$object){
		if(!$this->connect()){
			return true;
		}

		$IP = '';
		$emailAddress = '';

		if(empty($object->IP) && empty($object->emailAddress)){
			return true;
		}
		if(!empty($object->IP)){
			$IP = 'ip='.$object->IP.'&';
		}
		if(!empty($object->emailAddress)){
			$emailAddress = 'email='.$object->emailAddress.'&';
		}

		$data = $IP.$emailAddress;
		$data = trim($data, '&');
		$res = '';

		$header = "GET /api?".$data." HTTP/1.1\r\n";
		$header .= "Host: www.stopforumspam.com \r\n";
		$header .= "Connection: Close\r\n\r\n";
		fwrite($this->conn, $header);
		while(!feof($this->conn)){
			$res .= fread($this->conn, 1024);
		}

		if(!preg_match('#<response.*</response>#Uis', $res, $results)){
			$this->error = 'There is an error while trying to get the xml could not find "<reponse>"';
			return true;
		}

		$xml = new SimpleXMLElement($results[0]);

		$number = 0;
		foreach($xml->appears as $oneTest){
			if($oneTest == "yes"){
				if(strtolower($xml->type[$number]) == 'ip'){
					$problemSource = $object->IP;
				}
				if(strtolower($xml->type[$number]) == 'email'){
					$problemSource = $object->emailAddress;
				}
				$this->error .= 'There is a problem with the '.$xml->type[$number].' : '.$problemSource.' you used ( Spam test positive ) </br>'; // Check failed. Result indicates dangerous.
				return false;
			}elseif($oneTest == "no"){
			}else{
				$this->error = 'There is a problem with the result. Service down ? '; // Test returned neither positive or negative result. Service might be down?
				continue;
			}
			$number++;
		}
		return true;
	}
}

