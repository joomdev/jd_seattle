<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymJevents extends acymPlugin
{
    var $imgFolder = '';
    var $useStdTime;

    public function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_jevents'.DS)) {
            $this->installed = false;
        }
        $this->name = 'jevents';
    }

    public function insertOptions()
    {
        $plugin = new stdClass();
        $plugin->name = 'JEvents';
        $plugin->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.ico';
        $plugin->plugin = __CLASS__;

        return $plugin;
    }

    public function contentPopup()
    {
        acym_loadLanguageFile('com_jevents', JPATH_SITE);

        $this->categories = acym_loadObjectList('SELECT id, parent_id, title FROM `#__categories` WHERE extension = "com_jevents"', 'id');

        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = [
            [
                'title' => 'ACYM_DISPLAY',
                'type' => 'radio',
                'name' => 'type',
                'options' => [
                    'title' => 'ACYM_TITLE_ONLY',
                    'full' => 'ACYM_FULL_TEXT',
                ],
                'default' => 'full',
            ],
            [
                'title' => 'ACYM_CLICKABLE_TITLE',
                'type' => 'boolean',
                'name' => 'clickable',
                'default' => true,
            ],
            [
                'title' => 'ACYM_READ_MORE',
                'type' => 'boolean',
                'name' => 'readmore',
                'default' => true,
            ],
            [
                'title' => 'ACYM_TRUNCATE',
                'type' => 'intextfield',
                'name' => 'wrap',
                'text' => 'ACYM_TRUNCATE_AFTER',
                'default' => 0,
            ],
            [
                'title' => 'ACYM_DISPLAY_PICTURES',
                'type' => 'pictures',
                'name' => 'pictures',
            ],
        ];

        if (file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')) {
            $displayOptions[] = [
                'title' => 'ACY_FILES',
                'type' => 'boolean',
                'name' => 'pluginFields',
                'default' => true,
            ];
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
                $customField = [
                    'title' => 'ACYM_FIELDS_TO_DISPLAY',
                    'type' => 'checkbox',
                    'name' => 'custom',
                    'separator' => ', ',
                    'options' => [],
                ];
                foreach ($jevCf as $oneParam) {
                    $name = $oneParam->attributes()->name;
                    $label = $oneParam->attributes()->label;
                    if (!empty($name) && !empty($label)) {
                        $customField['options'][$name] = [$label, false];
                    }
                }

                $displayOptions[] = $customField;
            }
        }

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name);

        echo $this->getFilteringZone();

        $this->displayListing();

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('ACYM_BY_CATEGORY'));

        $catOptions = [
            [
                'title' => 'ACYM_COLUMNS',
                'type' => 'text',
                'name' => 'cols',
                'default' => 1,
            ],
            [
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'text',
                'name' => 'max',
                'default' => 20,
            ],
            [
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => [
                    'id' => 'ACYM_ID',
                    'startrepeat' => 'JEV_EVENT_STARTDATE',
                    'endrepeat' => 'JEV_EVENT_ENDDATE',
                    'summary' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ],
                'default' => 'startrepeat',
                'defaultdir' => 'asc',
            ],
            [
                'title' => 'ACYM_FROM',
                'type' => 'date',
                'name' => 'from',
                'default' => date('Y-m-d'),
                'relativeDate' => '+',
            ],
            [
                'title' => 'ACYM_TO',
                'type' => 'date',
                'name' => 'to',
                'default' => '',
                'relativeDate' => '+',
            ],
        ];

        if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jevlocations')) {
            $locs = acym_loadObjectList('SELECT loc_id, title, city, state, country FROM #__jev_locations');

            if (!empty($locs)) {
                $allCities = [0 => 'ACYM_ALL'];
                $allStates = [0 => 'ACYM_ALL'];
                $allCountries = [0 => 'ACYM_ALL'];
                $locations = [0 => 'ACYM_ALL'];
                foreach ($locs as $oneLoc) {
                    $locations[$oneLoc->loc_id] = $oneLoc->title;

                    if (!empty($oneLoc->city)) $allCities[$oneLoc->city] = $oneLoc->city;
                    if (!empty($oneLoc->state)) $allStates[$oneLoc->state] = $oneLoc->state;
                    if (!empty($oneLoc->country)) $allCountries[$oneLoc->country] = $oneLoc->country;
                }

                $catOptions[] = [
                    'title' => 'ACYM_LOCATION',
                    'type' => 'select',
                    'name' => 'location',
                    'options' => $locations,
                ];

                $catOptions[] = [
                    'title' => 'ACYM_COUNTRY',
                    'type' => 'select',
                    'name' => 'country',
                    'options' => $allCountries,
                ];

                $catOptions[] = [
                    'title' => 'ACYM_STATE',
                    'type' => 'select',
                    'name' => 'state',
                    'options' => $allStates,
                ];

                $catOptions[] = [
                    'title' => 'ACYM_CITY',
                    'type' => 'select',
                    'name' => 'city',
                    'options' => $allCities,
                ];
            }
        }

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'auto'.$this->name, 'grouped');

        echo $this->getCategoryListing();

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    public function displayListing()
    {
        $querySelect = 'SELECT rpt.*, detail.*, cat.title AS cattitle ';
        $query = 'FROM `#__jevents_repetition` AS rpt ';
        $query .= 'JOIN `#__jevents_vevent` AS ev ON rpt.eventid = ev.ev_id ';
        $query .= 'JOIN `#__categories` AS cat ON ev.catid = cat.id ';
        $query .= 'JOIN `#__jevents_vevdetail` AS detail ON ev.detail_id = detail.evdet_id ';
        $filters = [];

        $this->pageInfo = new stdClass();
        $this->pageInfo->limit = acym_getCMSConfig('list_limit');
        $this->pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $this->pageInfo->start = ($this->pageInfo->page - 1) * $this->pageInfo->limit;
        $this->pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $this->pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $this->pageInfo->order = 'rpt.startrepeat';
        $this->pageInfo->orderdir = 'DESC';

        $searchFields = ['rpt.rp_id', 'detail.evdet_id', 'detail.description', 'detail.summary', 'detail.contact', 'detail.location'];
        if (!empty($this->pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($this->pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }

        if (!empty($this->pageInfo->filter_cat)) {
            $filters[] = "ev.catid = ".intval($this->pageInfo->filter_cat);
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($this->pageInfo->order)) {
            $query .= ' ORDER BY '.acym_secureDBColumn($this->pageInfo->order).' '.acym_secureDBColumn($this->pageInfo->orderdir);
        }

        $rows = acym_loadObjectList($querySelect.$query, '', $this->pageInfo->start, $this->pageInfo->limit);
        $this->pageInfo->total = acym_loadResult('SELECT COUNT(*) '.$query);


        $listingOptions = [
            'header' => [
                'summary' => [
                    'label' => 'ACYM_TITLE',
                    'size' => '5',
                ],
                'startrepeat' => [
                    'label' => 'ACYM_DATE',
                    'size' => '3',
                    'type' => 'date',
                ],
                'cattitle' => [
                    'label' => 'ACYM_CATEGORY',
                    'size' => '3',
                ],
                'rp_id' => [
                    'label' => 'ACYM_ID',
                    'size' => '1',
                    'class' => 'text-center',
                ],
            ],
            'id' => 'rp_id',
            'rows' => $rows,
        ];

        echo $this->getElementsListing($listingOptions);
    }

    public function replaceContent(&$email)
    {
        $this->_replaceAuto($email);
        $this->_replaceOne($email);
    }

    public function _replaceAuto(&$email)
    {
        $this->generateByCategory($email);
        if (empty($this->tags)) return;
        $this->acympluginHelper->replaceTags($email, $this->tags, true);
    }

    public function generateByCategory(&$email)
    {
        $time = time();

        $tags = $this->acympluginHelper->extractTags($email, 'auto'.$this->name);
        $return = new stdClass();
        $return->status = true;
        $return->message = '';
        $this->tags = [];

        if (empty($tags)) return $return;

        $multicat = JComponentHelper::getParams('com_jevents')->get('multicategory', 0);

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) continue;

            $where = [];
            $where[] = 'ev.`state` = 1';

            $query = 'SELECT DISTINCT rpt.rp_id FROM `#__jevents_repetition` AS rpt ';
            $query .= ' JOIN `#__jevents_vevent` AS ev ON rpt.eventid = ev.ev_id ';

            if (empty($parameter->order)) $parameter->order = 'startrepeat,ASC';
            if (empty($parameter->from)) {
                $parameter->from = date('Y-m-d H:i:s', $time);
            } else {
                $parameter->from = acym_date(acym_replaceDate($parameter->from), 'Y-m-d H:i:s');
            }
            if (!empty($parameter->to)) $parameter->to = acym_date(acym_replaceDate($parameter->to), 'Y-m-d H:i:s');

            if (!empty($parameter->id)) {
                $allCats = explode('-', $parameter->id);
                array_pop($allCats);
                if (!empty($allCats)) {
                    acym_arrayToInteger($allCats);
                    $catToSearch = implode(',', $allCats);
                    if ($multicat == 1) {
                        $query .= ' JOIN `#__jevents_catmap` AS cats ON ev.ev_id = cats.evid ';
                        $where[] = 'cats.catid IN ('.$catToSearch.')';
                    } else {
                        $where[] = 'ev.catid IN ('.$catToSearch.')';
                    }
                }
            }

            $locationColumn = '';
            if (empty($parameter->location)) {
                if (!empty($parameter->country)) $locationColumn = 'country';
                if (!empty($parameter->state)) $locationColumn = 'state';
                if (!empty($parameter->city)) $locationColumn = 'city';
            }

            if (isset($parameter->priority) || !empty($parameter->location) || !empty($locationColumn) || strpos($parameter->order, 'summary') !== false) {
                $query .= ' JOIN `#__jevents_vevdetail` AS evdet ON ev.detail_id = evdet.evdet_id ';
            }

            if (!empty($locationColumn)) {
                $query .= ' JOIN `#__jev_locations` AS evloc ON evdet.location = evloc.loc_id';
                $where[] = 'evloc.'.$locationColumn.' = '.acym_escapeDB($parameter->$locationColumn);
            }

            if (!empty($parameter->location)) {
                $where[] = 'evdet.location = '.intval($parameter->location);
            }

            if (isset($parameter->priority)) {
                $parameter->priority = explode(',', $parameter->priority);
                acym_arrayToInteger($parameter->priority);
                $where[] = 'evdet.priority IN ('.implode(',', $parameter->priority).')';
            }

            if ((empty($parameter->mindelay) || substr($parameter->mindelay, 0, 1) != '-') && (empty($parameter->delay) || substr($parameter->delay, 0, 1) != '-')) {
                if (!empty($parameter->addcurrent)) {
                    $where[] = 'rpt.`endrepeat` >= '.acym_escapeDB($parameter->from);
                } else {
                    $where[] = 'rpt.`startrepeat` >= '.acym_escapeDB($parameter->from);
                }
            }

            if (!empty($parameter->todaysevent)) {
                $where[] = 'rpt.`startrepeat` <= '.acym_escapeDB(date('Y-m-d 23:59:59', $time));
            }

            if (!empty($parameter->mindelay)) $where[] = 'rpt.`startrepeat` >= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->mindelay));
            if (!empty($parameter->delay)) $where[] = 'rpt.`startrepeat` <= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->delay));
            if (!empty($parameter->to)) $where[] = 'rpt.`startrepeat` <= '.acym_escapeDB($parameter->to);

            if (isset($parameter->access)) {
                $where[] = 'ev.`access` = '.intval($parameter->access);
            }

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            $ordering = explode(',', $parameter->order);
            if ($ordering[0] == 'rand') {
                $query .= ' ORDER BY rand()';
            } else {
                $query .= ' ORDER BY '.acym_secureDBColumn(trim($ordering[0])).' '.acym_secureDBColumn(trim($ordering[1]));
            }

            if (!empty($parameter->max)) $query .= ' LIMIT '.intval($parameter->max);

            $allArticles = acym_loadResultArray($query);

            if (!empty($parameter->min) && count($allArticles) < $parameter->min) {
                $return->status = false;
                $return->message = 'Not enough events for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
            }

            $this->tags[$oneTag] = $this->finalizeCategoryFormat($this->name, $allArticles, $parameter);
        }

        return $return;
    }

    public function _replaceOne(&$email)
    {
        $tags = $this->acympluginHelper->extractTags($email, $this->name);
        if (empty($tags)) return;

        acym_loadLanguageFile('com_jevents', JPATH_SITE);

        if (file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')) {
            $JEVplugin = JPluginHelper::getPlugin('jevents', 'jevfiles');
            $JEVparams = new acymParameter($JEVplugin->params);
            $imagesFolder = JComponentHelper::getParams('com_media')->get('image_path', 'images');
            $this->imgFolder = ACYM_LIVE.$imagesFolder.'/'.trim($JEVparams->get('folder', 'jevents'), '/').'/';
        }

        $this->useStdTime = JComponentHelper::getParams("com_jevents")->get('com_calUseStdTime');

        $tagsReplaced = [];
        foreach ($tags as $i => $params) {
            if (isset($tagsReplaced[$i])) continue;

            $tagsReplaced[$i] = $this->_replaceContent($tags[$i]);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    public function _replaceContent(&$tag)
    {
        $query = 'SELECT rpt.*, detail.*, cat.title AS category, ev.catid, ev.uid FROM `#__jevents_repetition` AS rpt ';
        $query .= ' JOIN `#__jevents_vevent` AS ev ON rpt.eventid = ev.ev_id ';
        $query .= ' JOIN `#__jevents_vevdetail` AS detail ON rpt.eventdetail_id = detail.evdet_id ';
        $query .= 'LEFT JOIN `#__categories` AS cat ON cat.id = ev.catid ';
        $query .= 'WHERE rpt.rp_id = '.intval($tag->id).' LIMIT 1';

        $element = acym_loadObject($query);

        if (empty($element)) {
            if (acym_isAdmin()) acym_enqueueMessage('The event "'.$tag->id.'" could not be loaded', 'notice');

            return '';
        }

        $this->acympluginHelper->translateItem($element, $tag, 'jevents_vevdetail', $element->evdet_id);

        if (file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jevlocations') && !empty($element->location) && is_numeric($element->location)) {
            $query = 'SELECT title, street, postcode, city, state, country FROM `#__jev_locations` WHERE loc_id = '.intval($element->location);
            $location = acym_loadObject($query);
            if (!empty($location)) {
                foreach ($location as $prop => $value) {
                    $element->$prop = $value;
                }
                $element->location = $location->title;
            }
        }

        $varFields = [];
        foreach ($element as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $startdate = acym_date($element->startrepeat, acym_translation('ACYM_DATE_FORMAT_LC1'), null);
        $enddate = acym_date($element->endrepeat, acym_translation('ACYM_DATE_FORMAT_LC1'), null);
        $starttime = substr($element->startrepeat, 11, 5);
        $endtime = substr($element->endrepeat, 11, 5);

        if ($starttime == '00:00') {
            $starttime = '';
            $endtime = '';
        } elseif ($element->noendtime) {
            $endtime = '';
        }

        if (!empty($this->useStdTime)) {
            if (!empty($starttime)) $starttime = strtolower(strftime("%#I:%M%p", strtotime($element->startrepeat)));
            if (!empty($endtime)) $endtime = strtolower(strftime("%#I:%M%p", strtotime($element->endrepeat)));
        }

        $date = $startdate;
        if (!empty($starttime)) $date .= ' '.$starttime;
        if ($startdate == $enddate) {
            if (!empty($endtime)) $date .= ' - '.$endtime;
        } else {
            $date .= ' - '.$enddate;
            if (!empty($endtime)) $date .= ' '.$endtime;
        }
        $varFields['{date}'] = $date;


        $link = ACYM_LIVE.'index.php?option=com_jevents&task=icalrepeat.detail&evid='.intval($element->rp_id);
        if (empty($tag->itemid)) {
            $areaCats = [];
            $areaCats[] = $element->catid;
            $cats = acym_loadObjectList('SELECT id, parent_id FROM #__categories', 'id');
            $position = $element->catid;

            while ($cats[$position]->parent_id != 0) {
                $areaCats[] = $cats[$position]->parent_id;
                $position = $cats[$position]->parent_id;
            }

            $menuId = '';
            $menus = acym_loadObjectList('SELECT id, params FROM #__menu WHERE link LIKE "index.php?option=com_jevents&view=cat&layout=listevents"');
            if (!empty($menus)) {
                foreach ($menus as $i => $menu) {
                    $menus[$i]->params = json_decode($menus[$i]->params);
                    if (empty($menus[$i]->params->catidnew)) continue;
                    foreach ($menus[$i]->params->catidnew as $oneCatid) {
                        if (in_array($oneCatid, $areaCats)) {
                            $menuId = $menus[$i]->id;
                            break;
                        }
                    }
                    if ($menuId != '') break;
                }
            }

            if (empty($menuId)) {
                $summary = str_replace('-', ' ', $element->summary);
                $summary = trim(strtolower($summary));
                $summary = preg_replace('/(\s|[^A-Za-z0-9\-])+/', '-', $summary);
                $summary = trim($summary, '-');
                $time = explode('-', substr($element->startrepeat, 0, strpos($element->startrepeat, ' ')));
                $link = 'index.php?option=com_jevents&task=icalrepeat.detail&evid='.intval($element->rp_id).'&year='.intval($time[0]).'&month='.intval($time[1]).'&day='.intval($time[2]).'&title='.$summary.'&uid='.$element->uid;
            } else {
                $link .= '&Itemid='.intval($menuId);
            }
        } else {
            $link .= '&Itemid='.intval($tag->itemid);
        }

        if (!empty($tag->lang)) $link .= '&lang='.substr($tag->lang, 0, strpos($tag->lang, ','));
        $varFields['{link}'] = $link;

        $title = $element->summary;

        $afterTitle = '';
        $afterArticle = '';

        $imagePath = '';
        $contentText = $element->description;
        $customFields = [];

        if ($tag->type == 'full') {
            $customFields[] = [$date];

            if (!empty($element->location)) $customFields[] = [$element->location, acym_translation('ACYM_ADDRESS')];

            if (!empty($tag->custom)) {
                $tag->custom = explode(',', $tag->custom);
                foreach ($tag->custom as $i => $oneField) {
                    $tag->custom[$i] = trim($oneField);
                }

                $jevCFParams = acym_loadObject('SELECT params FROM #__extensions WHERE element = "jevcustomfields"');
                if (!empty($jevCFParams->params)) $template = json_decode($jevCFParams->params)->template;

                if (!empty($template)) {
                    $xmlfile = JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevcustomfields'.DS.'customfields'.DS.'templates'.DS.$template;
                    if (file_exists($xmlfile)) {
                        $xml = simplexml_load_file($xmlfile);
                        $jevCf = $xml->xpath('//fields/fieldset/field');
                        $jevCustomFields = [];
                        foreach ($jevCf as $i => $oneField) {
                            $name = (string)$oneField->attributes()->name;
                            $jevCustomFields[$name] = new stdClass();
                            $jevCustomFields[$name]->label = (string)$oneField->attributes()->label;
                            $jevCustomFields[$name]->type = (string)$oneField->attributes()->type;

                            if (empty($oneField->option)) continue;

                            $jevCustomFields[$name]->options = [];
                            foreach ($oneField->option as $oneOption) {
                                $jevCustomFields[$name]->options[] = $oneOption;
                            }
                        }
                    }
                }

                $customVDB = acym_loadObjectList('SELECT name, value FROM #__jev_customfields WHERE evdet_id = '.intval($element->evdet_id));
                foreach ($customVDB as $oneField) {
                    $varFields['{'.$oneField->name.'}'] = $oneField->value;
                }

                $customValues = [];
                foreach ($customVDB as $oneCustomValue) {
                    $customValues[$oneCustomValue->name] = $oneCustomValue->value;
                }

                if (!empty($customValues)) {
                    foreach ($tag->custom as $oneCustom) {
                        $label = (!empty($jevCustomFields[$oneCustom]->label)) ? $jevCustomFields[$oneCustom]->label : $oneCustom;
                        if (!empty($jevCustomFields[$oneCustom]->options)) {
                            $multipleValues = explode(',', $customValues[$oneCustom]);

                            $orderedValues = [];
                            foreach ($multipleValues as $oneValue) {
                                $orderedValues[$oneValue] = $oneValue;
                            }

                            $possibleValues = [];
                            foreach ($jevCustomFields[$oneCustom]->options as $oneOption) {
                                $possibleValues[(string)$oneOption->attributes()->value] = (string)$oneOption;
                            }

                            foreach ($orderedValues as $key => $j) {
                                $orderedValues[$key] = $possibleValues[$key];
                            }
                            $customValues[$oneCustom] = implode(', ', $orderedValues);
                        } elseif ($jevCustomFields[$oneCustom]->type == 'jevrurl') { //we want a link !
                            $customValues[$oneCustom] = '<a href="'.$customValues[$oneCustom].'">'.$customValues[$oneCustom].'</a>';
                        } elseif ($jevCustomFields[$oneCustom]->type == 'jevrcalendar') {//comprehensible display
                            $customValues[$oneCustom] = acym_getDate(acym_getTime($customValues[$oneCustom]), acym_translation('ACYM_DATE_FORMAT_LC1'));
                        } elseif ($jevCustomFields[$oneCustom]->type == 'jevruser') {//we do not want the user id but its name
                            $user = acym_loadResultArray('SELECT name FROM #__users WHERE id = '.intval($customValues[$oneCustom]));
                            $customValues[$oneCustom] = (empty($user[0])) ? $customValues[$oneCustom] : $user[0];
                        } elseif ($jevCustomFields[$oneCustom]->type == 'jevcfboolean') {
                            $customValues[$oneCustom] = empty($customValues[$oneCustom]) ? acym_translation('ACYM_NO') : acym_translation('ACYM_YES');
                        }

                        if (empty($customValues[$oneCustom]) && in_array($jevCustomFields[$oneCustom]->type, ['jevcfuser', 'jevcfyoutube', 'jevcfupdatable', 'jevcfdblist', 'jevcftext', 'jevcfimage', 'jevcffile', 'jevcfhtml', 'jevcfeventflag', 'jevcfnotes'])) {
                            unset($customValues[$oneCustom]);
                        }

                        if (isset($customValues[$oneCustom])) $customFields[] = [$customValues[$oneCustom], $label];
                    }
                }
            }

            if (!empty($element->contact)) {
                $value = $element->contact;

                if (acym_isValidEmail($value)) $value = '<a href="mailto:'.$value.'">'.$value.'</a>';

                $customFields[] = [$value, acym_translation('JEV_EVENT_CONTACT')];
            }
            if (!empty($element->extra_info)) $customFields[] = [$element->extra_info];
        }

        if (file_exists(JPATH_SITE.DS.'plugins'.DS.'jevents'.DS.'jevfiles'.DS.'jevfiles.php')) {

            if (in_array(acym_getPrefix().'jev_files_combined', acym_getTableList())) {
                $filesRow = acym_loadObject(
                    'SELECT files.* 
                    FROM `#__jev_files_combined` AS files 
                    JOIN #__jevents_repetition AS rpt ON files.ev_id = rpt.eventid 
                    WHERE rpt.rp_id = '.intval($tag->id)
                );

                if (!empty($filesRow)) {
                    for ($i = 1 ; $i < 30 ; $i++) {
                        if (!empty($filesRow->{'imagename'.$i})) {
                            $varFields['{imgpath'.$i.'}'] = $this->imgFolder.$filesRow->{'imagename'.$i};
                            if (empty($imagePath)) {
                                $imagePath = $varFields['{imgpath'.$i.'}'];
                                continue;
                            }
                            $afterArticle .= '<br /><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'"><img src="'.$varFields['{imgpath'.$i.'}'].'" alt="" /></a>';
                        }

                        if (!empty($filesRow->{'filename'.$i})) {
                            $varFields['{filepath'.$i.'}'] = $this->imgFolder.$filesRow->{'filename'.$i};
                            if (!empty($tag->pluginFields)) $files[] = '<a target="_blank" href="'.$varFields['{filepath'.$i.'}'].'">'.(empty($filesRow->{'filename'.$i}) ? : $filesRow->{'filetitle'.$i}).'</a>';
                        }
                    }
                    if (!empty($files)) $afterArticle .= implode('<br />', $files);
                }
            } else {
                $files = acym_loadObjectList(
                    'SELECT files.* 
                    FROM `#__jev_files` AS files 
                    JOIN #__jevents_repetition AS rpt ON files.ev_id = rpt.eventid 
                    WHERE rpt.rp_id = '.intval($tag->id).' 
                    ORDER BY filetype DESC'
                );

                if (!empty($files)) {
                    foreach ($files as $i => $oneFile) {
                        if (empty($oneFile->filename)) continue;

                        $varFields['{imgpath'.$i.'}'] = $this->imgFolder.$oneFile->filename;
                        if ($oneFile->filetype == 'file') {
                            if (!empty($tag->pluginFields)) $afterArticle .= '<br /><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'">'.$oneFile->filetitle.'</a>';
                        } else {
                            if (empty($imagePath)) {
                                $imagePath = $varFields['{imgpath'.$i.'}'];
                                continue;
                            }
                            $afterArticle .= '<br /><a target="_blank" href="'.$varFields['{imgpath'.$i.'}'].'"><img src="'.$varFields['{imgpath'.$i.'}'].'" alt="" /></a>';
                        }
                    }
                }
            }
        }

        $varFields['{readmore}'] = '<a class="acymailing_readmore_link" style="text-decoration:none;" target="_blank" href="'.$link.'"><span class="acymailing_readmore">'.acym_translation('ACYM_READ_MORE').'</span></a>';
        if (!empty($tag->readmore)) {
            $afterArticle .= $varFields['{readmore}'];
        }

        $format = new stdClass();
        $format->tag = $tag;
        $format->title = $title;
        $format->afterTitle = $afterTitle;
        $format->afterArticle = $afterArticle;
        $format->imagePath = $imagePath;
        $format->description = $contentText;
        $format->link = empty($tag->clickable) ? '' : $link;
        $format->cols = empty($tag->nbcols) ? 1 : intval($tag->nbcols);
        $format->customFields = $customFields;
        $result = '<div class="acymailing_content">'.$this->acympluginHelper->getStandardDisplay($format).'</div>';

        return $this->finalizeElementFormat($this->name, $result, $tag, $varFields);
    }

    public function onAcymDeclareTriggers(&$triggers, &$defaultValues)
    {

        $every = [
            '3600' => acym_translation('ACYM_HOURS'),
            '86400' => acym_translation('ACYM_DAYS'),
        ];

        $when = [
            'before' => acym_translation('ACYM_BEFORE'),
            'after' => acym_translation('ACYM_AFTER'),
        ];

        $categories = acym_loadObjectList('SELECT `id`, `title` FROM #__categories WHERE `extension` = "com_jevents"', 'id');

        foreach ($categories as $key => $category) {
            $categories[$key] = $category->title;
        }

        $categories = ['' => acym_translation('ACYM_ANY_CATEGORY')] + $categories;

        $triggers['classic']['jevents_reminder'] = new stdClass();
        $triggers['classic']['jevents_reminder']->name = acym_translation_sprintf('ACYM_COMBINED_TRANSLATIONS', 'JEvents', acym_translation('ACYM_REMINDER'));
        $triggers['classic']['jevents_reminder']->option = '<div class="grid-x cell acym_vcenter"><div class="grid-x cell grid-margin-x acym_vcenter margin-bottom-1">';
        $triggers['classic']['jevents_reminder']->option .= '<div class="cell medium-shrink">
                                                                <input 
                                                                    type="number" 
                                                                    name="[triggers][classic][jevents_reminder][number]" 
                                                                    class="intext_input" 
                                                                    value="'.(empty($defaultValues['jevents_reminder']) ? '1' : $defaultValues['jevents_reminder']['number']).'">
                                                            </div>';
        $triggers['classic']['jevents_reminder']->option .= '<div class="cell medium-shrink">'.acym_select(
                $every,
                '[triggers][classic][jevents_reminder][time]',
                empty($defaultValues['jevents_reminder']) ? '86400' : $defaultValues['jevents_reminder']['time'],
                'data-class="intext_select acym__select"'
            ).'</div></div>';
        $triggers['classic']['jevents_reminder']->option .= '<div class="grid-x cell grid-margin-x acym_vcenter margin-bottom-1"><div class="cell medium-shrink">'.acym_select(
                $when,
                '[triggers][classic][jevents_reminder][when]',
                empty($defaultValues['jevents_reminder']) ? 'before' : $defaultValues['jevents_reminder']['when'],
                'data-class="intext_select acym__select"'
            ).'</div>';
        $triggers['classic']['jevents_reminder']->option .= '<div class="cell medium-shrink">'.acym_translation('ACYM_AN_EVENT_IN').'</div>';
        $triggers['classic']['jevents_reminder']->option .= '<div class="cell medium-auto">'.acym_select(
                $categories,
                '[triggers][classic][jevents_reminder][cat]',
                empty($defaultValues['jevents_reminder']) ? '' : $defaultValues['jevents_reminder']['cat'],
                'data-class="intext_select_larger intext_select acym__select"'
            ).'</div>';
        $triggers['classic']['jevents_reminder']->option .= '</div></div>';
    }

    public function onAcymExecuteTrigger(&$step, &$execute, $data)
    {
        $time = $data['time'];
        $triggers = json_decode($step->triggers, true);

        if (!empty($triggers['jevents_reminder']['number'])) {
            $config = acym_config();
            $triggerReminder = $triggers['jevents_reminder'];

            $timestamp = ($triggerReminder['number'] * $triggerReminder['time']);

            if ($triggerReminder['when'] == 'before') {
                $timestamp += $time;
            } else {
                $timestamp -= $time;
            }


            $join = [];
            $where = [];

            if (!empty($triggerReminder['cat'])) {
                $multicat = JComponentHelper::getParams('com_jevents')->get('multicategory', 0);
                if ($multicat == 1) {
                    $join[] = 'JOIN #__jevents_catmap AS cats ON rpt.eventid = cats.evid ';
                    $where[] = 'cats.catid = '.intval($triggerReminder['cat']);
                } else {
                    $join[] = 'LEFT JOIN #__jevents_vevent AS event ON `rpt`.`eventid` = `event`.`ev_id`';
                    $where[] = '`event`.`catid` = '.intval($triggerReminder['cat']);
                }
            }
            $join[] = 'LEFT JOIN #__jevents_vevdetail AS eventd ON `rpt`.`eventdetail_id` = `eventd`.`evdet_id`';

            $where[] = '`rpt`.`startrepeat` >= '.acym_escapeDB(acym_date($timestamp, 'Y-m-d H:i:s'));
            $where[] = '`rpt`.`startrepeat` <= '.acym_escapeDB(acym_date($timestamp + $config->get('cron_frequency', '900'), 'Y-m-d H:i:s'));
            $where[] = '`eventd`.`state` = 1';

            $events = acym_loadObjectList('SELECT * FROM `#__jevents_repetition` AS rpt '.implode(' ', $join).' WHERE '.implode(' AND ', $where));
            if (!empty($events)) $execute = true;
        }
    }

    public function onAcymDeclareSummary_triggers(&$automation)
    {
        if (!empty($automation->triggers['jevents_reminder'])) {
            $every = [
                '3600' => acym_translation('ACYM_HOURS'),
                '86400' => acym_translation('ACYM_DAYS'),
            ];

            $when = [
                'before' => acym_translation('ACYM_BEFORE'),
                'after' => acym_translation('ACYM_AFTER'),
            ];
            $categories = acym_loadObjectList('SELECT `id`, `title` FROM #__categories WHERE `extension` = "com_jevents"', 'id');

            foreach ($categories as $key => $category) {
                $categories[$key] = $category->title;
            }

            $categories = ['' => acym_translation('ACYM_ANY_CATEGORY')] + $categories;

            $final = '';

            $final = $automation->triggers['jevents_reminder']['number'].' ';
            $final .= $every[$automation->triggers['jevents_reminder']['time']].' ';
            $final .= $when[$automation->triggers['jevents_reminder']['when']].' ';
            $final .= acym_translation('ACYM_AN_EVENT_IN').' '.strtolower($categories[$automation->triggers['jevents_reminder']['cat']]);

            $automation->triggers['jevents_reminder'] = $final;
        }
    }
}

