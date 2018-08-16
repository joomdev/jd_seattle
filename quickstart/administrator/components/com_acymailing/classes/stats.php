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

class statsClass extends acymailingClass{

	var $tables = array('urlclick', 'userstats', 'stats');
	var $pkey = 'mailid';

	var $countReturn = true;

	var $subid = 0;
	var $mailid = 0;


	function saveStats(){
		$subid = empty($this->subid) ? acymailing_getVar('int', 'subid') : $this->subid;
		$mailid = empty($this->mailid) ? acymailing_getVar('int', 'mailid') : $this->mailid;
		if(empty($subid) || empty($mailid)) return false;
		if(acymailing_isRobot()) return false;

		$actual = acymailing_loadObject('SELECT `open` FROM '.acymailing_table('userstats').' WHERE `mailid` = '.intval($mailid).' AND `subid` = '.intval($subid).' LIMIT 1');
		if(empty($actual)) return false;

		$userHelper = acymailing_get('helper.user');
		$config = acymailing_config();

		if($config->get('anonymous_tracking', 0) == 0) {
			try {
				$results = acymailing_query('UPDATE #__acymailing_subscriber SET `lastopen_date` = '.time().', `lastopen_ip` = '.acymailing_escapeDB($userHelper->getIP()).' WHERE `subid` = '.intval($subid));
			} catch (Exception $e) {
				$results = null;
			}
			if ($results === null) {
				acymailing_display(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
				exit;
			}
		}

		$open = 0;

		if(empty($actual->open)){
			$open = 1;
			$unique = ',openunique = openunique +1';
		}elseif($this->countReturn){
			$open = $actual->open + 1;
			$unique = '';
		}
		if(empty($open)) return true;


		if($config->get('anonymous_tracking', 0) == 0) {
			$ipClass = acymailing_get('helper.user');
			$ip = $ipClass->getIP();

			try {
				$results = acymailing_query('UPDATE '.acymailing_table('userstats').' SET open = '.$open.', opendate = '.time().', `ip`= '.acymailing_escapeDB($ip).' WHERE mailid = '.$mailid.' AND subid = '.$subid);
			} catch (Exception $e) {
				$results = null;
			}
			if ($results === null) {
				acymailing_display(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
				exit;
			}

			$browsers = array(
				'Abrowse' => 'abrowse',
				'Abolimba' => 'abolimba',
				'3ds' => '3ds',
				'Acoo browser' => 'acoo browser',
				'Alienforce' => 'alienforce',
				'Amaya' => 'amaya',
				'Amigavoyager' => 'amigavoyager',
				'Antfresco' => 'antfresco',
				'Aol' => 'aol',
				'Arora' => 'arora',
				'Avant' => 'avant',
				'Baidubrowser' => 'baidubrowser',
				'Beamrise' => 'beamrise',
				'Beonex' => 'beonex',
				'Blackbird' => 'blackbird',
				'Blackhawk' => 'blackhawk',
				'Bolt' => 'bolt',
				'Browsex' => 'browsex',
				'Browzar' => 'browzar',
				'Bunjalloo' => 'bunjalloo',
				'Camino' => 'camino',
				'Charon' => 'charon',
				'Chromium' => 'chromium',
				'Columbus' => 'columbus',
				'Cometbird' => 'cometbird',
				'Dragon' => 'dragon',
				'Conkeror' => 'conkeror',
				'Coolnovo' => 'coolnovo',
				'Corom' => 'corom',
				'Deepnet explorer' => 'deepnet explorer',
				'Demeter' => 'demeter',
				'Deskbrowse' => 'deskbrowse',
				'Dillo' => 'dillo',
				'Dooble' => 'dooble',
				'Dplus' => 'dplus',
				'Edbrowse' => 'edbrowse',
				'Element browser' => 'element browser',
				'Elinks' => 'elinks',
				'Epic' => 'epic',
				'Epiphany' => 'epiphany',
				'Firebird' => 'firebird',
				'Flock' => 'flock',
				'Fluid' => 'fluid',
				'Galeon' => 'galeon',
				'Globalmojo' => 'globalmojo',
				'Greenbrowser' => 'greenbrowser',
				'Hotjava' => 'hotjava',
				'Hv3' => 'hv3',
				'Hydra' => 'hydra',
				'Ibrowse' => 'ibrowse',
				'Icab' => 'icab',
				'Icebrowser' => 'icebrowser',
				'Iceape' => 'iceape',
				'Icecat' => 'icecat',
				'Icedragon' => 'icedragon',
				'Iceweasel' => 'iceweasel',
				'Surfboard' => 'surfboard',
				'Irider' => 'irider',
				'Iron' => 'iron',
				'Meleon' => 'meleon',
				'Ninja' => 'ninja',
				'Kapiko' => 'kapiko',
				'Kazehakase' => 'kazehakase',
				'Strata' => 'strata',
				'Kkman' => 'kkman',
				'Konqueror' => 'konqueror',
				'Kylo' => 'kylo',
				'Lbrowser' => 'lbrowser',
				'Links' => 'links',
				'Lobo' => 'lobo',
				'Lolifox' => 'lolifox',
				'Lunascape' => 'lunascape',
				'Lynx' => 'lynx',
				'Maxthon' => 'maxthon',
				'Midori' => 'midori',
				'Minibrowser' => 'minibrowser',
				'Mosaic' => 'mosaic',
				'Multizilla' => 'multizilla',
				'Myibrow' => 'myibrow',
				'Netcaptor' => 'netcaptor',
				'Netpositive' => 'netpositive',
				'Netscape' => 'netscape',
				'Navigator' => 'navigator',
				'Netsurf' => 'netsurf',
				'Nintendobrowser' => 'nintendobrowser',
				'Offbyone' => 'offbyone',
				'Omniweb' => 'omniweb',
				'Orca' => 'orca',
				'Oregano' => 'oregano',
				'Otter' => 'otter',
				'Palemoon' => 'palemoon',
				'Patriott' => 'patriott',
				'Perk' => 'perk',
				'Phaseout' => 'phaseout',
				'Phoenix' => 'phoenix',
				'Polarity' => 'polarity',
				'Playstation 4' => 'playstation 4',
				'Qtweb internet browser' => 'qtweb internet browser',
				'Qupzilla' => 'qupzilla',
				'Rekonq' => 'rekonq',
				'Retawq' => 'retawq',
				'Roccat' => 'roccat',
				'Rockmelt' => 'rockmelt',
				'Ryouko' => 'ryouko',
				'Saayaa' => 'saayaa',
				'Seamonkey' => 'seamonkey',
				'Shiira' => 'shiira',
				'Sitekiosk' => 'sitekiosk',
				'Skipstone' => 'skipstone',
				'Sleipnir' => 'sleipnir',
				'Slimboat' => 'slimboat',
				'Slimbrowser' => 'slimbrowser',
				'Metasr' => 'metasr',
				'Stainless' => 'stainless',
				'Sundance' => 'sundance',
				'Sundial' => 'sundial',
				'Sunrise' => 'sunrise',
				'Superbird' => 'superbird',
				'Surf' => 'surf',
				'Swiftweasel' => 'swiftweasel',
				'Tenfourfox' => 'tenfourfox',
				'Theworld' => 'theworld',
				'Tjusig' => 'tjusig',
				'Tencenttraveler' => 'tencenttraveler',
				'Ultrabrowser' => 'ultrabrowser',
				'Usejump' => 'usejump',
				'Uzbl' => 'uzbl',
				'Vonkeror' => 'vonkeror',
				'V3m' => 'v3m',
				'Webianshell' => 'webianshell',
				'Webrender' => 'webrender',
				'Weltweitimnetzbrowser' => 'weltweitimnetzbrowser',
				'Whitehat aviator' => 'whitehat aviator',
				'Wkiosk' => 'wkiosk',
				'Worldwideweb' => 'worldwideweb',
				'Wyzo' => 'wyzo',
				'Smiles' => 'smiles',
				'Yabrowser' => 'yabrowser',
				'Yrcweblink' => 'yrcweblink',
				'Zbrowser' => 'zbrowser',
				'Zipzap' => 'zipzap',
				'Firefox' => 'firefox',
				'Internet Explorer' => 'msie|trident',
				'Opera' => 'opera',
				'Chrome' => 'chrome',
				'Safari' => 'safari',
				'Thunderbird' => 'thunderbird',
				'Outlook' => 'outlook',
				'Airmail' => 'airmail',
				'Barca' => 'barca',
				'Eudora' => 'eudora',
				'Gcmail' => 'gcmail',
				'Lotus' => 'lotus',
				'Pocomail' => 'pocomail',
				'Postbox' => 'postbox',
				'Shredder' => 'shredder',
				'Sparrow' => 'sparrow',
				'Spicebird' => 'spicebird',
				'Bat!' => 'bat!',
				'Tizenbrowser' => 'tizenbrowser',
				'Apple Mail' => 'applewebkit',
				'Mozilla' => 'mozilla',
				'Gecko' => 'gecko'
			);

			$name = "unknown";
			$version = "";

			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
			} else {
				$agent = "unknown";
			}
			foreach ($browsers as $key => $oneBrowser) {
				if (preg_match("#($oneBrowser)[/ ]?([0-9]*)#", $agent, $match)) {
					$name = $key;
					$version = $this->_getRealBrowserVersion($match[2], $name, $agent);
					break;
				}
			}

			$isMobile = 0;
			$osName = '';
			if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/', $agent) || preg_match(
					'/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',
					substr($agent, 0, 4)
				)
			) {
				$isMobile = 1;
				$osName = "unknown";
				$mobileOs = array("bada" => "Bada", "ubuntu; mobile" => "Ubuntu", "ubuntu; tablet" => "Ubuntu", "tizen" => "Tizen", "palm os" => "Palm", "meego" => "meeGo", "symbian" => "Symbian", "symbos" => "Symbian", "blackberry" => "BlackBerry", "windows ce" => "Windows Phone", "windows mobile" => "Windows Phone", "windows phone" => "Windows Phone", "iphone" => "iOS", "ipad" => "iOS", "ipod" => "iOS", "android" => "Android");
				$mobileOsKeys = array_keys($mobileOs);
				foreach ($mobileOsKeys as $oneMobileOsKey) {
					if (preg_match("/($oneMobileOsKey)/", $agent, $match2)) {
						$osName = $mobileOs[$match2[1]];
						break;
					}
				}
			}

			try {
				$results = acymailing_query('UPDATE '.acymailing_table('userstats').' SET `is_mobile` = '.intval($isMobile).', `mobile_os` = '.acymailing_escapeDB($osName).', `browser` = '.acymailing_escapeDB($name).', browser_version = '.intval($version).', user_agent = '.acymailing_escapeDB($agent).' WHERE mailid = '.$mailid.' AND subid = '.$subid.' LIMIT 1');
			} catch (Exception $e) {
				$results = null;
			}
			if ($results === null) {
				acymailing_display(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
				exit;
			}
		}

		acymailing_query('UPDATE '.acymailing_table('stats').' SET opentotal = opentotal +1 '.$unique.' WHERE mailid = '.$mailid.' LIMIT 1');

		if(!empty($subid)){
			$filterClass = acymailing_get('class.filter');
			$filterClass->subid = $subid;
			$filterClass->trigger('opennews');
		}

		$classGeoloc = acymailing_get('class.geolocation');
		$classGeoloc->saveGeolocation('open', $subid);

		acymailing_importPlugin('acymailing');
		acymailing_trigger('onAcyOpenMail', array($subid, $mailid));

		return true;
	}

	private function _getRealBrowserVersion($versionUA, $browserUA, $userAgent){
		if($browserUA == 'Internet Explorer' && strpos($userAgent, 'trident') !== false){
			return '11';
		}

		return $versionUA;
	}

}
