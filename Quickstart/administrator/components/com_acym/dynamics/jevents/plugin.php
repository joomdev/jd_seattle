<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymJevents extends acymPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_jevents'.DS)) {
            $this->installed = false;
        }
    }

    function insertOptions()
    {
        $plugins = new stdClass();
        $plugins->name = 'JEvents';
        $plugins->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.ico';
        $plugins->plugin = __CLASS__;

        return $plugins;
    }

    function _categories($filter_cat)
    {
        $cats = acym_loadObjectList("SELECT id, parent_id, title FROM `#__categories` WHERE extension = 'com_jevents'", 'id');
        $this->cats = array();
        if (!empty($cats)) {
            foreach ($cats as $oneCat) {
                $this->cats[$oneCat->parent_id][] = $oneCat;
            }
        }
        $this->catvalues = array();
        $this->catvalues[] = acym_selectOption(0, acym_translation('ACYM_ALL'));
        $this->_handleChildren();

        return acym_select($this->catvalues, 'plugin_category', (int)$filter_cat, 'style="width: 150px;"', 'value', 'text');
    }

    function _handleChildren($parent_id = 1, $level = 0)
    {
        if (empty($this->cats[$parent_id])) {
            return;
        }
        foreach ($this->cats[$parent_id] as $cat) {
            $this->catvalues[] = acym_selectOption($cat->id, str_repeat(" - - ", $level).$cat->title);
            $this->_handleChildren($cat->id, $level + 1);
        }
    }

    function contentPopup()
    {
        acym_loadLanguageFile('com_jevents', JPATH_SITE);
        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = array(
            array(
                'title' => 'ACYM_DISPLAY',
                'type' => 'radio',
                'name' => 'type',
                'options' => array(
                    'title' => 'ACYM_TITLE_ONLY',
                    'full' => 'ACYM_FULL_TEXT',
                ),
                'default' => 'full',
            ),
            array(
                'title' => 'ACYM_READ_MORE',
                'type' => 'boolean',
                'name' => 'readmore',
                'default' => true,
            ),
            array(
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ),
            array(
                'title' => 'ACYM_TRUNCATE',
                'type' => 'intextfield',
                'name' => 'wrap',
				'text' => 'ACYM_TRUNCATE_AFTER',   
                'default' => 0,
            ),
            array(
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ),
        );

        if (file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')) {
            $displayOptions[] = array(
                'title' => 'ACY_FILES',
                'type' => 'boolean',
                'name' => 'pluginFields',
                'default' => true,
            );
        }

        if (file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevcustomfields')) {
            $jevCFParams = acym_loadObject('SELECT params FROM #__extensions WHERE element = "jevcustomfields"');
            if (!empty($jevCFParams->params)) {
                $template = json_decode($jevCFParams->params)->template;
            }
            if (!empty($template)) {
                $xmlfile = JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevcustomfields'.DS.'customfields'.DS.'templates'.DS.$template;
                if (file_exists($xmlfile)) {
                    $xml = simplexml_load_file($xmlfile);
                    $jevCf = $xml->xpath('//fields/fieldset/field');
                }
            }

            if (!empty($jevCf)) {
                $customField = array(
                    'title' => 'ACYM_FIELDS_TO_DISPLAY',
                    'type' => 'checkbox',
                    'name' => 'custom',
                    'separator' => ', ',
                    'options' => array(),
                );
                foreach ($jevCf as $oneParam) {
                    if (!empty($oneParam->attributes()->name) && !empty($oneParam->attributes()->label)) {
                        $customField['options'][$oneParam->attributes()->name] = array($oneParam->attributes()->label, false);
                    }
                }

                $displayOptions[] = $customField;
            }
        }

        echo $this->acympluginHelper->displayOptions($displayOptions, 'jeventz');


        $filter_cat = acym_getVar('int', 'plugin_category', 0);
        echo '<div class="grid-x grid-margin-x" id="plugin_listing_filters">
                <div class="cell medium-6">
                    <input type="text" name="plugin_search" placeholder="'.acym_escape(acym_translation('ACYM_SEARCH')).'"/>
                </div>
                <div class="cell medium-6 grid-x">
                    <div class="cell hide-for-small-only medium-auto"></div>
                    <div class="cell medium-shrink">
                    '.$this->_categories($filter_cat).'
                    </div>
                </div>
            </div>';
        echo '<div id="plugin_listing" class="acym__popup__listing padding-0">';
        $this->displayListing();
        echo '</div>';
        $tabHelper->endTab();

        $tabHelper->startTab(acym_translation('ACYM_BY_CATEGORY'));

        $catOptions = array(
            array(
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ),
            array(
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ),
            array(
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => array(
                    'id' => 'ACYM_ID',
                    'startrepeat' => 'JEV_EVENT_STARTDATE',
                    'endrepeat' => 'JEV_EVENT_ENDDATE',
                    'summary' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ),
            ),
            array(
                'title' => 'ACYM_FROM',
                'type' => 'date',
                'name' => 'from',
                'default' => date('Y-m-d'),
            ),
            array(
                'title' => 'ACYM_TO',
                'type' => 'date',
                'name' => 'to',
                'default' => '',
            ),
        );

        if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jevlocations')) {
            $locs = acym_loadObjectList('SELECT loc_id, title, city, state, country FROM #__jev_locations');

            if (!empty($locs)) {
                $allCities = array(0 => 'ACYM_ALL');
                $allStates = array(0 => 'ACYM_ALL');
                $allCountries = array(0 => 'ACYM_ALL');
                $locations = array(0 => 'ACYM_ALL');
                foreach ($locs as $oneLoc) {
                    $locations[$oneLoc->loc_id] = $oneLoc->title;

                    if (!empty($oneLoc->city)) {
                        $allCities[$oneLoc->city] = $oneLoc->city;
                    }
                    if (!empty($oneLoc->state)) {
                        $allStates[$oneLoc->state] = $oneLoc->state;
                    }
                    if (!empty($oneLoc->country)) {
                        $allCountries[$oneLoc->country] = $oneLoc->country;
                    }
                }

                $catOptions[] = array(
                    'title' => 'ACYM_LOCATION',
                    'type' => 'select',
                    'name' => 'location',
                    'options' => $locations,
                );

                $catOptions[] = array(
                    'title' => 'ACYM_COUNTRY',
                    'type' => 'select',
                    'name' => 'country',
                    'options' => $allCountries,
                );

                $catOptions[] = array(
                    'title' => 'ACYM_STATE',
                    'type' => 'select',
                    'name' => 'state',
                    'options' => $allStates,
                );

                $catOptions[] = array(
                    'title' => 'ACYM_CITY',
                    'type' => 'select',
                    'name' => 'city',
                    'options' => $allCities,
                );
            }
        }

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'autojevents', 'grouped');

        if (!empty($this->catvalues)) {
            echo '<div class="acym__popup__listing padding-0">';
            foreach ($this->catvalues as $oneCat) {
                if (empty($oneCat->value)) {
                    continue;
                }
                echo '<div class="cell grid-x acym__listing__row acym__listing__row__popup" data-id="'.$oneCat->value.'" onclick="applyContentautojevents('.$oneCat->value.', this);">
                        <div class="cell medium-5">'.$oneCat->text.'</div>
                    </div>';
            }
            echo '</div>';
        }

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    function displayListing()
    {
        echo '<input type="hidden" name="plugin" value="'.__CLASS__.'" />';
        $query = 'SELECT SQL_CALC_FOUND_ROWS rpt.*, detail.*, cat.title AS cattitle FROM `#__jevents_repetition` as rpt ';
        $query .= 'JOIN `#__jevents_vevent` as ev ON rpt.eventid = ev.ev_id ';
        $query .= 'JOIN `#__categories` as cat ON ev.catid = cat.id ';
        $query .= 'JOIN `#__jevents_vevdetail` AS detail ON ev.detail_id=detail.evdet_id ';

        $pageInfo = new stdClass();
        $pageInfo->limit = acym_getCMSConfig('list_limit');
        $pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $pageInfo->start = ($pageInfo->page - 1) * $pageInfo->limit;
        $pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $pageInfo->order = 'rpt.startrepeat';
        $pageInfo->orderdir = 'DESC';

        $searchFields = array('rpt.rp_id', 'detail.evdet_id', 'detail.description', 'detail.summary', 'detail.contact', 'detail.location');
        if (!empty($pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }
        if (!empty($pageInfo->filter_cat)) {
            $filters[] = "ev.catid = ".intval($pageInfo->filter_cat);
        }
        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }
        if (!empty($pageInfo->order)) {
            $query .= ' ORDER BY '.$pageInfo->order.' '.$pageInfo->orderdir;
        }

        $rows = acym_loadObjectList($query, '', $pageInfo->start, $pageInfo->limit);
        $pageInfo->total = acym_loadResult('SELECT FOUND_ROWS()');

        $selected = explode(',', acym_getVar('string', 'selected', ''));

        echo '<div class="cell grid-x hide-for-small-only plugin_listing_headers">
                <div class="cell medium-5">'.acym_translation('ACYM_TITLE').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_DATE_CREATED').'</div>
                <div class="cell medium-3">'.acym_translation('ACYM_CATEGORY').'</div>
                <div class="cell medium-1">'.acym_translation('ACYM_ID').'</div>
            </div>';
        foreach ($rows as $row) {
            $class = 'cell grid-x acym__listing__row acym__listing__row__popup';
            if (in_array($row->rp_id, $selected)) {
                $class .= ' selected_row';
            }
            echo '<div class="'.$class.'" data-id="'.$row->rp_id.'" onclick="applyContentjeventz('.$row->rp_id.', this);">
                    <div class="cell medium-5">'.$row->summary.'</div>
                    <div class="cell medium-3">'.$row->startrepeat.'</div>
                    <div class="cell medium-3">'.$row->cattitle.'</div>
                    <div class="cell medium-1">'.$row->rp_id.'</div>
                </div>';
        }

        if (empty($rows)) {
            echo '<h1 class="cell acym__listing__empty__search__modal text-center">'.acym_translation('ACYM_NO_RESULTS_FOUND').'</h1>';
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($pageInfo->total, $pageInfo->page, $pageInfo->limit);
        echo $pagination->displayAjax();
    }

    function replaceContent(&$email)
    {
        $this->_replaceAuto($email);
        $this->_replaceOne($email);
    }

	function _replaceAuto(&$email){
		$this->generateByCategory($email);
		if(empty($this->tags)) return;
		$this->acympluginHelper->replaceTags($email, $this->tags, true);
	}

	function generateByCategory(&$email){
		$time = time();

		$tags = $this->acympluginHelper->extractTags($email, 'autojevents');
		$return = new stdClass();
		$return->status = true;
		$return->message = '';
		$this->tags = array();

		if(empty($tags)) return $return;

		$multicat = JComponentHelper::getParams('com_jevents')->get('multicategory', 0);

		foreach($tags as $oneTag => $parameter){
			if(isset($this->tags[$oneTag])) continue;

			$where = array();

			$query = 'SELECT DISTINCT rpt.rp_id FROM `#__jevents_repetition` AS rpt ';
			$query .= ' JOIN `#__jevents_vevent` AS ev ON rpt.eventid = ev.ev_id ';

			if(empty($parameter->order)) $parameter->order = 'startrepeat,ASC';
			if(empty($parameter->from)) $parameter->from = date('Y-m-d H:i:s', $time);

			if(!empty($parameter->id)){
				$allCats = explode('-', $parameter->id);
				array_pop($allCats);
				if(!empty($allCats)){
					acym_arrayToInteger($allCats);
					$catToSearch = implode(',', $allCats);
					if($multicat == 1){
						$query .= ' JOIN `#__jevents_catmap` AS cats ON ev.ev_id = cats.evid ';
						$where[] = 'cats.catid IN ('.$catToSearch.')';
					}else{
						$where[] = 'ev.catid IN ('.$catToSearch.')';
					}
				}
			}

			$parameterToSearch = ''; //Always initialize variables in a loop
			if(empty($parameter->location)){//If we don't set the location we check if the country/state/city have been set
				if(!empty($parameter->country)){
					$parameterToSearch = 'country';
				}
				if(!empty($parameter->state)){
					$parameterToSearch = 'state';
				}
				if(!empty($parameter->city)){
					$parameterToSearch = 'city';
				}
			}

			if(isset($parameter->priority) || isset($parameter->location) || isset($parameterToSearch) || strpos($parameter->order, 'summary') !== false){
				$query .= ' JOIN `#__jevents_vevdetail` as evdet ON ev.detail_id = evdet.evdet_id ';
			}
			if(!empty($parameterToSearch)){
				$query .= ' JOIN `#__jev_locations` AS evloc ON evdet.location = evloc.loc_id';
				$where[] = 'evloc.'.$parameterToSearch.' = '.acym_escapeDB($parameter->$parameterToSearch);
			}

			if(!empty($parameter->location)){
				$where[] = 'evdet.location = '.intval($parameter->location);
			}

			if(isset($parameter->priority)){
				$parameter->priority = explode(',', $parameter->priority);
				acym_arrayToInteger($parameter->priority);
				$where[] = 'evdet.priority IN ('.implode(',', $parameter->priority).')';
			}
			$where[] = 'ev.`state` = 1';

			if((empty($parameter->mindelay) || substr($parameter->mindelay, 0, 1) != '-') && (empty($parameter->delay) || substr($parameter->delay, 0, 1) != '-')) {
				if (!empty($parameter->addcurrent)) {
					$where[] = 'rpt.`endrepeat` >= ' . acym_escapeDB($parameter->from);
				} else {
					$where[] = 'rpt.`startrepeat` >= ' . acym_escapeDB($parameter->from);
				}
			}

			if(!empty($parameter->todaysevent)){
				$where[] = 'rpt.`startrepeat` <= '.acym_escapeDB(date('Y-m-d 23:59:59', $time));
			}

			if(!empty($parameter->mindelay)) $where[] = 'rpt.`startrepeat` >= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->mindelay));
			if(!empty($parameter->delay)) $where[] = 'rpt.`startrepeat` <= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->delay));
			if(!empty($parameter->to)) $where[] = 'rpt.`startrepeat` <= '.acym_escapeDB($parameter->to);

			if(isset($parameter->access)){
				$where[] = 'ev.`access` = '.intval($parameter->access);
			}

			if(!empty($parameter->created) && !empty($email->params['lastgenerateddate'])){
				$where[] = 'ev.created > \''.date('Y-m-d H:i:s', $email->params['lastgenerateddate'] - date('Z')).'\' AND ev.created < \''.date('Y-m-d H:i:s', $time - date('Z')).'\'';
			}

			$query .= ' WHERE ('.implode(') AND (', $where).')';

			$ordering = explode(',', $parameter->order);
			if($ordering[0] == 'rand'){
				$query .= ' ORDER BY rand()';
			}else{
				$query .= ' ORDER BY '.acym_secureDBColumn(trim($ordering[0])).' '.acym_secureDBColumn(trim($ordering[1]));
			}

			if(!empty($parameter->max)) $query .= ' LIMIT '.intval($parameter->max);

			$allArticles = acym_loadResultArray($query);

			if(!empty($parameter->min) && count($allArticles) < $parameter->min){
				$return->status = false;
				$return->message = 'Not enough events for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
			}

			$stringTag = '';
			if(!empty($allArticles)){
				if(file_exists(ACYM_MEDIA.'plugins'.DS.'autojevents.php')){
					ob_start();
					require(ACYM_MEDIA.'plugins'.DS.'autojevents.php');
					$stringTag = ob_get_clean();
				}else{
					$arrayElements = array();
					foreach($allArticles as $oneArticleId){
						$stringTag .= '<tr><td>';
						$args = array();
						$args[] = 'jevents:'.$oneArticleId;
						if(isset($parameter->pluginFields)) $args[] = 'pluginFields:'.$parameter->pluginFields;
						if(!empty($parameter->custom)) $args[] = 'custom:'.$parameter->custom;
						if(isset($parameter->pict)) $args[] = 'pict:'.$parameter->pict;
						if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
						if(!empty($parameter->wrap)) $args[] = 'wrap:'.$parameter->wrap;
						if(!empty($parameter->clickable)) $args[] = 'clickable';
						if(!empty($parameter->readmore)) $args[] = 'readmore';
						if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
						if(!empty($parameter->itemid)) $args[] = 'itemid:'.$parameter->itemid;
						if(!empty($parameter->maxwidth)) $args[] = 'maxwidth:'.$parameter->maxwidth;
						if(!empty($parameter->maxheight)) $args[] = 'maxheight:'.$parameter->maxheight;
						$arrayElements[] = '{'.implode('|', $args).'}';
					}
					$stringTag = $this->acympluginHelper->getFormattedResult($arrayElements, $parameter);
				}
			}
			$this->tags[$oneTag] = $stringTag;
		}
		return $return;
	}

	function _replaceOne(&$email){
		$tags = $this->acympluginHelper->extractTags($email, 'jevents');

		if(empty($tags)) return;

		acym_loadLanguageFile('com_jevents', JPATH_SITE);

		$this->newslanguage = new stdClass();
		if(!empty($email->language)){
			$this->newslanguage = acym_loadObject('SELECT lang_id, lang_code FROM #__languages WHERE sef = '.acym_escapeDB($email->language).' LIMIT 1');
		}

		$this->mailerHelper = acym_get('helper.mailer');

		$this->readmore = empty($email->template->readmore) ? acym_translation('ACYM_READ_MORE') : '<img src="'.ACYM_LIVE.$email->template->readmore.'" alt="'.acym_translation('ACYM_READ_MORE', true).'" />';

		if(file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')){
			$JEVplugin = JPluginHelper::getPlugin('jevents', 'jevfiles');
			$JEVparams = new acymParameter($JEVplugin->params);
			$imagesFolder = JComponentHelper::getParams('com_media')->get('image_path', 'images');
			$this->imgFolder = ACYM_LIVE.$imagesFolder.'/'.trim($JEVparams->get('folder', 'jevents'), '/').'/';
		}

		$tagsReplaced = array();
		foreach($tags as $i => $params){
			if(isset($tagsReplaced[$i])) continue;
			$tagsReplaced[$i] = $this->_replaceContent($tags[$i]);
		}

		$this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
	}

	function _replaceContent(&$tag){
		$query = 'SELECT rpt.*,detail.*,cat.title as category, ev.catid, ev.uid FROM `#__jevents_repetition` as rpt ';
		$query .= ' JOIN `#__jevents_vevent` as ev ON rpt.eventid = ev.ev_id ';
		$query .= ' JOIN `#__jevents_vevdetail` AS detail ON rpt.eventdetail_id = detail.evdet_id ';
		$query .= 'LEFT JOIN `#__categories` as cat ON cat.id = ev.catid ';
		$query .= 'WHERE rpt.rp_id = '.intval($tag->id).' LIMIT 1';

		$event = acym_loadObject($query);

		if(empty($tag->lang) && !empty($this->newslanguage) && !empty($this->newslanguage->lang_code)) $tag->lang = $this->newslanguage->lang_code.','.$this->newslanguage->lang_id;

		$this->acympluginHelper->translateItem($event, $tag, 'jevents_vevdetail', $event->evdet_id);

		if(empty($event)){
			if(acym_isAdmin()) acym_enqueueMessage('The event "'.$tag->id.'" could not be loaded', 'notice');
			return '';
		}

		if(file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jevlocations') && !empty($event->location) && is_numeric($event->location)){
			$query = 'SELECT title,street,postcode,city,state,country FROM `#__jev_locations` WHERE loc_id = '.intval($event->location);
			$location = acym_loadObject($query);
			if(!empty($location)){
				foreach($location as $prop => $value){
					$event->$prop = $value;
				}
				$event->location = $location->title;
			}
		}

		$varFields = array();
		foreach($event as $fieldName => $oneField){
			$varFields['{'.$fieldName.'}'] = $oneField;
		}
        $startdate = acym_date($event->startrepeat, acym_translation('ACYM_DATE_FORMAT_LC1'), null);
        $enddate = acym_date($event->endrepeat, acym_translation('ACYM_DATE_FORMAT_LC1'), null);
		$starttime = substr($event->startrepeat, 11, 5);
		$endtime = substr($event->endrepeat, 11, 5);

		if($starttime == '00:00'){
			$starttime = '';
			$endtime = '';
		}elseif($event->noendtime){
			$endtime = '';
		}

		$cfg = JComponentHelper::getParams("com_jevents");
		$useStdTime = $cfg->get('com_calUseStdTime');

		if(!empty($useStdTime)){
			if(!empty($starttime)) $starttime = strtolower(strftime("%#I:%M%p", strtotime($event->startrepeat)));
			if(!empty($endtime)) $endtime = strtolower(strftime("%#I:%M%p", strtotime($event->endrepeat)));
		}

		$date = $startdate;
		if(!empty($starttime)) $date .= ' '.$starttime;
		if($startdate == $enddate){
			if(!empty($endtime)) $date .= ' - '.$endtime;
		}else{
			$date .= ' - '.$enddate;
			if(!empty($endtime)) $date .= ' '.$endtime;
		}
		$varFields['{date}'] = $date;


		$link = ACYM_LIVE.'index.php?option=com_jevents&task=icalrepeat.detail&evid='.intval($event->rp_id);
		if(!empty($tag->itemid)){
			$link .= '&Itemid='.intval($tag->itemid);
		}else{
			$areaCats = array();
			$areaCats[] = $event->catid;
			$cats = acym_loadObjectList('SELECT id, parent_id FROM #__categories', 'id');
			$position = $event->catid;
			while($cats[$position]->parent_id != 0){
				$areaCats[] = $cats[$position]->parent_id;
				$position = $cats[$position]->parent_id;
			}
			$menuId = '';
			$menus = acym_loadObjectList('SELECT id, params FROM #__menu WHERE link LIKE "index.php?option=com_jevents&view=cat&layout=listevents"');
			if(!empty($menus)){
				foreach($menus as $i => $menu){
					$menus[$i]->params = json_decode($menus[$i]->params);
					if(empty($menus[$i]->params->catidnew)) continue;
					foreach($menus[$i]->params->catidnew as $oneCatid){
						if(in_array($oneCatid, $areaCats)){
							$menuId = $menus[$i]->id;
							break;
						}
					}
					if($menuId != '') break;
				}
			}

			if(!empty($menuId)){
				$link .= '&Itemid='.intval($menuId);
			}else{
				$summary = str_replace('-', ' ', $event->summary);
				$summary = trim(strtolower($summary));
				$summary = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $summary);
				$summary = trim($summary, '-');
				$time = explode('-', substr($event->startrepeat, 0, strpos($event->startrepeat, ' ')));
				$link = 'index.php?option=com_jevents&task=icalrepeat.detail&evid='.intval($event->rp_id).'&year='.intval($time[0]).'&month='.intval($time[1]).'&day='.intval($time[2]).'&title='.$summary.'&uid='.$event->uid;
			}
		}
		if(!empty($tag->lang)) $link .= '&lang='.substr($tag->lang, 0, strpos($tag->lang, ','));
		$varFields['{link}'] = $link;

		$result = '<table cellspacing="0" cellpadding="0" border="0" width="100%">';

		$event->summary = '<h2 class="acym_title">'.$event->summary.'</h2>';
		if(!empty($tag->clickable)) $event->summary = '<a target="_blank" href="'.$link.'">'.$event->summary.'</a>';
		$result .= '<tr><td>'.$event->summary.'</td></tr>';

		$event->description = $this->acympluginHelper->wrapText($event->description, $tag);
		$varFields['{wrapeddescription}'] = $event->description;

		if($tag->type == 'full'){
			$result .= '<tr><td><span class="eventdate">'.$date.'</span></td></tr>';

			$result .= '<tr><td class="eventdescription">'.$event->description.'</td></tr>';
			if(!empty($event->location)) $result .= '<tr><td><span class="eventlocation">'.acym_translation('ACYM_ADDRESS').' : '.$event->location.'</span></td></tr>';

			if(!empty($tag->custom)){
				$tag->custom = explode(',', $tag->custom);
				foreach($tag->custom as $i => $oneField){
					$tag->custom[$i] = trim($oneField);
				}

				$jevCFParams = acym_loadObject('SELECT params FROM #__extensions WHERE element = "jevcustomfields"');
				if(!empty($jevCFParams->params)) $template = json_decode($jevCFParams->params)->template;

				if(!empty($template)){
					$xmlfile = JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevcustomfields'.DS.'customfields'.DS.'templates'.DS.$template;
					if(file_exists($xmlfile)){
						$xml = simplexml_load_file($xmlfile);
						$jevCf = $xml->xpath('//fields/fieldset/field');
						$customFields = array();
						foreach($jevCf as $i => $oneField){
							$name = (string)$oneField->attributes()->name;
							$customFields[$name] = new stdClass();
							$customFields[$name]->label = (string)$oneField->attributes()->label;
							$customFields[$name]->type = (string)$oneField->attributes()->type;

							if(empty($oneField->option)) continue;

							$customFields[$name]->options = array();
							foreach($oneField->option as $oneOption){
								$customFields[$name]->options[] = $oneOption;
							}
						}
					}
				}

				$customVDB = acym_loadObjectList('SELECT name, value FROM #__jev_customfields WHERE evdet_id = '.intval($event->evdet_id));
				foreach($customVDB as $oneField){
					$varFields['{'.$oneField->name.'}'] = $oneField->value;
				}

				$customValues = array();
				foreach($customVDB as $oneCustomValue){
					$customValues[$oneCustomValue->name] = $oneCustomValue->value;
				}

				if(!empty($customValues)){
					foreach($tag->custom as $oneCustom){
						$label = (!empty($customFields[$oneCustom]->label)) ? $customFields[$oneCustom]->label : $oneCustom;
						if(!empty($customFields[$oneCustom]->options)){
							$multipleValues = explode(',', $customValues[$oneCustom]);

							$orderedValues = array();
							foreach($multipleValues as $oneValue){
								$orderedValues[$oneValue] = $oneValue;
							}

							$possibleValues = array();
							foreach($customFields[$oneCustom]->options as $oneOption){
								$possibleValues[(string)$oneOption->attributes()->value] = (string)$oneOption;
							}

							foreach($orderedValues as $key => $j){
								$orderedValues[$key] = $possibleValues[$key];
							}
							$customValues[$oneCustom] = implode(', ', $orderedValues);
						}elseif($customFields[$oneCustom]->type == 'jevrurl'){ //we want a link !
							$customValues[$oneCustom] = '<a href="'.$customValues[$oneCustom].'">'.$customValues[$oneCustom].'</a>';
						}elseif($customFields[$oneCustom]->type == 'jevrcalendar'){//comprehensible display
							$customValues[$oneCustom] = acym_getDate(acym_getTime($customValues[$oneCustom]), acym_translation('ACYM_DATE_FORMAT_LC1'));
						}elseif($customFields[$oneCustom]->type == 'jevruser'){//we do not want the user id but its name
							$user = acym_loadResultArray('SELECT name FROM #__users WHERE id = '.intval($customValues[$oneCustom]));
							$customValues[$oneCustom] = (empty($user[0])) ? $customValues[$oneCustom] : $user[0];
						}elseif($customFields[$oneCustom]->type == 'jevcfboolean'){
							$customValues[$oneCustom] = empty($customValues[$oneCustom]) ? acym_translation('ACYM_NO') : acym_translation('ACYM_YES');
						}

						if(empty($customValues[$oneCustom]) && in_array($customFields[$oneCustom]->type, array('jevcfuser', 'jevcfyoutube', 'jevcfupdatable', 'jevcfdblist', 'jevcftext', 'jevcfimage', 'jevcffile', 'jevcfhtml', 'jevcfeventflag', 'jevcfnotes'))){
							unset($customValues[$oneCustom]);
						}

						if(isset($customValues[$oneCustom])) $result .= '<tr><td><span class="jev_cf_"'.$oneCustom.'>'.$label.' : '.$customValues[$oneCustom].'</span></td></tr>';
					}
				}
			}
			if(!empty($event->contact)) $result .= '<tr><td><span class="eventcontact">'.$event->contact.'</span></td></tr>';
			if(!empty($event->extra_info)) $result .= '<tr><td><span class="eventextra">'.$event->extra_info.'</span></td></tr>';
		}

		if(file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')){

			if(in_array(acym_getPrefix().'jev_files_combined', acym_getTableList())){
				$filesRow = acym_loadObject('SELECT * FROM `#__jev_files_combined` WHERE ev_id = (SELECT eventid FROM #__jevents_repetition WHERE rp_id = '.intval($tag->id).')');

				if(!empty($filesRow)){
					for($i = 1; $i < 30; $i++){
						if(!empty($filesRow->{'imagename'.$i})){
							$varFields['{imgpath'.$i.'}'] = $this->imgFolder.$filesRow->{'imagename'.$i};
							$result .= '<tr><td><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'"><img src="'.$varFields['{imgpath'.$i.'}'].'"/></a></td></tr>';
						}

						if(!empty($filesRow->{'filename'.$i})){
							$varFields['{filepath'.$i.'}'] = $this->imgFolder.$filesRow->{'filename'.$i};
							if(!empty($tag->pluginFields)) $files[] = '<tr><td><a target="_blank" href="'.$varFields['{filepath'.$i.'}'].'">'.(empty($filesRow->{'filename'.$i}) ? : $filesRow->{'filetitle'.$i}).'</a></td></tr>';
						}
					}
					if(!empty($files)) $result .= implode('', $files);
				}
			}else{
				$files = acym_loadObjectList('SELECT * FROM `#__jev_files` WHERE ev_id = (SELECT eventid FROM #__jevents_repetition WHERE rp_id = '.intval($tag->id).') ORDER BY filetype DESC');

				if(!empty($files)){
					foreach($files as $i => $oneFile){
						if(empty($oneFile->filename)) continue;

						$varFields['{imgpath'.$i.'}'] = $this->imgFolder.$oneFile->filename;
						if($oneFile->filetype == 'file'){
							if(!empty($tag->pluginFields)) $result .= '<tr><td><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'">'.$oneFile->filetitle.'</a></td></tr>';
						}else{
							$result .= '<tr><td><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'"><img src="'.$varFields['{imgpath'.$i.'}'].'"/></a></td></tr>';
						}
					}
				}
			}
		}

		if(!empty($tag->readmore)){
			$readMoreText = empty($this->readmore) ? acym_translation('ACYM_READ_MORE') : $this->readmore;
			$varFields['{readmore}'] = '<a style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acym_readmore">'.$readMoreText.'</span></a>';
			$result .= '<tr><td><br />'.$varFields['{readmore}'].'</td></tr>';
		}

		$result = '<div class="acym_content" >'.$result.'</table></div>';

		if(file_exists(ACYM_MEDIA.'plugins'.DS.'tagjevents.php')){
			ob_start();
			require(ACYM_MEDIA.'plugins'.DS.'tagjevents.php');
			$result = ob_get_clean();
			$result = str_replace(array_keys($varFields), $varFields, $result);
		}

		$result = $this->acympluginHelper->managePicts($tag, $result);

		return $result;
	}
}
