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

class dashboardViewDashboard extends acymailingView{

	function display($tpl = null){
		$config = acymailing_config();

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->help('dashboard');
		$acyToolbar->setTitle(acymailing_translation('ACY_CPANEL'), 'dashboard');
		$acyToolbar->display();

		$userQuery = 'SELECT (confirmed + enabled) AS addition, COUNT(subid) AS total FROM #__acymailing_subscriber GROUP BY addition';
		$userResult = acymailing_loadObjectList($userQuery, 'addition');

		$userStats = new stdClass();
		$userStats->nbUnconfirmedAndDisabled = (empty($userResult[0]->total) ? 0 : $userResult[0]->total);
		$userStats->nbConfirmed = (empty($userResult[1]->total) ? 0 : $userResult[1]->total);
		$userStats->nbConfirmed += (empty($userResult[2]->total) ? 0 : $userResult[2]->total);
		$userStats->total = $userStats->nbConfirmed + $userStats->nbUnconfirmedAndDisabled;

		$userStats->confirmedPercent = (empty($userStats->total) ? 0 : round((($userStats->nbConfirmed * 100) / $userStats->total), 0));

		$listsQuery = "SELECT COUNT(DISTINCT(l.listid)) FROM #__acymailing_list as l LEFT JOIN #__acymailing_listsub as ls ON l.listid=ls.listid WHERE l.type='list' AND ls.status=1 AND ls.subid IS NOT NULL";
		$atLeastOneSub = acymailing_loadResult($listsQuery);

		$nbLists = acymailing_loadResult('SELECT COUNT(listid) FROM #__acymailing_list WHERE type = "list"');

		$listStats = new stdClass();
		$listStats->atLeastOneSub = $atLeastOneSub;
		$listStats->noSub = $nbLists - $atLeastOneSub;
		$listStats->total = $nbLists;

		$listStats->subscribedPercent = (empty($nbLists) ? 0 : round((($atLeastOneSub * 100) / $nbLists), 0));

		$nlQuery = 'SELECT count(mailid) AS total, published FROM #__acymailing_mail WHERE type = "news" GROUP BY published';
		$nlResult = acymailing_loadObjectList($nlQuery, 'published');

		$nlStats = new stdClass();
		$nlStats->nbUnpublished = (empty($nlResult[0]->total) ? 0 : $nlResult[0]->total);
		$nlStats->nbpublished = (empty($nlResult[1]->total) ? 0 : $nlResult[1]->total);
		$nlStats->total = $nlStats->nbpublished + $nlStats->nbUnpublished;

		$nlStats->publishedPercent = (empty($nlStats->total) ? 0 : round((($nlStats->nbpublished * 100) / $nlStats->total), 0));


		$this->nlStats = $nlStats;
		$this->userStats = $userStats;
		$this->listStats = $listStats;
		$this->config = $config;




		$geolocParam = $config->get('geolocation');
		if(!empty($geolocParam) && $geolocParam != 1){
			$condition = '';
			if(strpos($geolocParam, 'creation') !== false){
				$condition = " WHERE geolocation_type='creation'";
			}

			$nbUsersToGet = 100;
			$query = 'SELECT geolocation_type, geolocation_subid, geolocation_country_code, geolocation_city, geolocation_country, geolocation_state';
			$query .= ' FROM #__acymailing_geolocation'.$condition.' GROUP BY geolocation_subid ORDER BY geolocation_created DESC LIMIT '.$nbUsersToGet;
			$geoloc = acymailing_loadObjectList($query);

			if(!empty($geoloc)){
				$markCities = array();
				$diffCountries = false;
				$dataDetails = array();
				$addresses = array();
				foreach($geoloc as $mark){
					$indexCity = array_search($mark->geolocation_city, $markCities);
					if($indexCity === false){
						array_push($markCities, $mark->geolocation_city);
						array_push($dataDetails, 1);
						$addresses[] = $mark->geolocation_city.' '.$mark->geolocation_state.' '.$mark->geolocation_country;
					}else{
						$dataDetails[$indexCity] += 1;
					}

					if(!$diffCountries){
						if(!empty($region) && $region != $mark->geolocation_country_code){
							$region = 'world';
							$diffCountries = true;
						}else{
							$region = $mark->geolocation_country_code;
						}
					}
				}
				$this->geoloc_city = $markCities;
				$this->geoloc_details = $dataDetails;
				$this->geoloc_region = $region;
				$this->geoloc_addresses = $addresses;
				$this->nbUsersToGet = $nbUsersToGet;
			}
		}

		acymailing_addScript(false, "https://www.google.com/jsapi");
		$statsusers = acymailing_loadObjectList("SELECT count(`subid`) as total, DATE_FORMAT(FROM_UNIXTIME(`created`),'%Y-%m-%d') as subday FROM ".acymailing_table('subscriber')." WHERE `created` > 100000 GROUP BY subday ORDER BY subday DESC LIMIT 15");
		$this->statsusers = $statsusers;

		$users10 = acymailing_loadObjectList('SELECT name,email,html,confirmed,subid,created FROM '.acymailing_table('subscriber').' ORDER BY subid DESC LIMIT 10');
		$this->users = $users10;

		$toggleClass = acymailing_get('helper.toggle');
		$this->toggleClass = $toggleClass;


		$listStatusQuery = 'SELECT count(subid) AS total, list.name AS listname, list.listid, listsub.status FROM #__acymailing_list AS list JOIN #__acymailing_listsub AS listsub ON list.listid = listsub.listid GROUP BY listsub.listid, listsub.status';
		$listStatusResult = acymailing_loadObjectList($listStatusQuery);

		$listStatusData = array();
		foreach($listStatusResult as $oneResult){
			$listStatusData[$oneResult->listname][$oneResult->status] = $oneResult->total;
		}
		$this->listStatusData = $listStatusData;


		$newsletters = acymailing_loadObjectList("SELECT count(userstats.`mailid`) as total, DATE_FORMAT(FROM_UNIXTIME(`senddate`), '%Y-%m-%d') AS send_date,
						SUM(CASE WHEN fail>0 THEN 1 ELSE 0 END) AS nbFailed
						FROM ".acymailing_table('userstats')." AS userstats
						WHERE userstats.senddate > ".intval(time() - 2628000)."
						GROUP BY send_date
						ORDER BY send_date DESC");

		$this->newsletters = $newsletters;



		$progressBarSteps = new stdClass();
		$progressBarSteps->listCreated = (!empty($listStats->total) ? 1 : 0);
		$progressBarSteps->contactCreated = (!empty($userStats->total) ? 1 : 0);
		$progressBarSteps->newsletterCreated = (!empty($nlStats->total) ? 1 : 0);

		$result = acymailing_loadResult('SELECT subid FROM #__acymailing_userstats LIMIT 1');

		$progressBarSteps->newsletterSent = (!empty($result) ? 1 : 0);
		$this->progressBarSteps = $progressBarSteps;

		$news = @simplexml_load_file('https://www.acyba.com/acynews.xml');
		if(!empty($news->news)) {
			
			$currentLanguage = acymailing_getLanguageTag();

			$latestNews = null;
			foreach ($news->news as $oneNews) {
				if (!empty($latestNews) && strtotime($latestNews->date) > strtotime($oneNews->date)) break;

				if (empty($oneNews->published) || (strtolower($oneNews->language) != strtolower($currentLanguage) && (strtolower($oneNews->language) != 'default' || !empty($latestNews)))) continue;

				if (!empty($oneNews->extension) && strtolower($oneNews->extension) != 'acymailing') continue;

				if (!empty($oneNews->cms) && strtolower($oneNews->cms) != 'joomla') continue;

				if (!empty($oneNews->level) && strtolower($oneNews->level) != strtolower($config->get('level'))) continue;

				if (!empty($oneNews->version)) {
					list($version, $operator) = explode('_', $oneNews->version);
					if(!version_compare($config->get('version'), $version, $operator)) continue;
				}

				$latestNews = $oneNews;
			}

			if (!empty($latestNews)) {
				$this->contentToDisplay = $latestNews;
				$this->config = $config;
			}
		}

		parent::display($tpl);
	}
}
