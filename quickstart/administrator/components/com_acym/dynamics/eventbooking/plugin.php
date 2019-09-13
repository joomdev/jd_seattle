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

class plgAcymEventbooking extends acymPlugin
{
    var $imgFolder = '';
    var $useStdTime;
    var $eventbookingconfig;

    public function __construct()
    {
        parent::__construct();
        $this->cms = 'Joomla';
        if (!defined('JPATH_ADMINISTRATOR') || !file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_eventbooking'.DS)) {
            $this->installed = false;
        }
        $this->name = 'eventbooking';
        $this->rootCategoryId = 0;
    }

    public function insertOptions()
    {
        $plugin = new stdClass();
        $plugin->name = 'Event Booking';
        $plugin->icon = ACYM_DYNAMICS_URL.basename(__DIR__).'/icon.png';
        $plugin->plugin = __CLASS__;

        return $plugin;
    }

    public function contentPopup()
    {
        acym_loadLanguageFile('com_eventbooking', JPATH_SITE);
        $this->categories = acym_loadObjectList('SELECT `id`, `parent` AS `parent_id`, `name` AS `title` FROM `#__eb_categories` WHERE published = 1', 'id');

        $tabHelper = acym_get('helper.tab');
        $tabHelper->startTab(acym_translation('ACYM_ONE_BY_ONE'));

        $displayOptions = [
            [
                'title' => 'ACYM_DISPLAY',
                'type' => 'checkbox',
                'name' => 'display',
                'options' => [
                    'title' => ['ACYM_TITLE', true],
                    'price' => ['ACYM_PRICE', true],
                    'sdate' => ['ACYM_DATE', true],
                    'edate' => ['EB_EVENT_END_DATE', true],
                    'image' => ['ACYM_IMAGE', true],
                    'short' => ['ACYM_SHORT_DESCRIPTION', true],
                    'desc' => ['ACYM_DESCRIPTION', false],
                    'cats' => ['ACYM_CATEGORIES', false],
                    'location' => ['ACYM_LOCATION', true],
                    'capacity' => ['EB_CAPACTIY', false],
                    'regstart' => ['EB_REGISTRATION_START_DATE', false],
                    'cut' => ['EB_CUT_OFF_DATE', false],
                    'indiv' => ['EB_REGISTER_INDIVIDUAL', false],
                    'group' => ['EB_REGISTER_GROUP', false],
                ],
            ],
        ];

        if (file_exists(JPATH_ROOT.DS.'components'.DS.'com_eventbooking'.DS.'fields.xml')) {
            $xml = JFactory::getXML(JPATH_ROOT.'/components/com_eventbooking/fields.xml');
            if (!empty($xml->fields)) {
                $fields = $xml->fields->fieldset->children();
                $customFields = [];
                foreach ($fields as $oneCustomField) {
                    $name = $oneCustomField->attributes()->name;
                    $label = acym_translation($oneCustomField->attributes()->label);
                    $customFields["$name"] = [$label, false];
                }

                $displayOptions[] = [
                    'title' => 'ACYM_CUSTOM_FIELDS',
                    'type' => 'checkbox',
                    'name' => 'custom',
                    'options' => $customFields,
                ];
            }
        }

        $displayOptions = array_merge(
            $displayOptions,
            [
                [
                    'title' => 'ACYM_CLICKABLE_TITLE',
                    'type' => 'boolean',
                    'name' => 'clickable',
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
                    'title' => 'ACYM_READ_MORE',
                    'type' => 'boolean',
                    'name' => 'readmore',
                    'default' => true,
                ],
                [
                    'title' => 'ACYM_DISPLAY_PICTURES',
                    'type' => 'pictures',
                    'name' => 'pictures',
                ],
            ]
        );

        echo $this->acympluginHelper->displayOptions($displayOptions, $this->name);

        echo $this->getFilteringZone();

        $this->displayListing();

        $tabHelper->endTab();
        $tabHelper->startTab(acym_translation('ACYM_BY_CATEGORY'));

        $catOptions = [
            [
                'title' => 'ACYM_COLUMNS',
                'type' => 'number',
                'name' => 'cols',
                'default' => 1,
                'min' => 1,
                'max' => 10,
            ],
            [
                'title' => 'ACYM_MAX_NB_ELEMENTS',
                'type' => 'number',
                'name' => 'max',
                'default' => 20,
            ],
            [
                'title' => 'ACYM_FROM',
                'type' => 'date',
                'name' => 'from',
                'default' => date('Y-m-d'),
            ],
            [
                'title' => 'ACYM_TO',
                'type' => 'date',
                'name' => 'to',
                'default' => '',
            ],
            [
                'title' => 'ACYM_ORDER_BY',
                'type' => 'select',
                'name' => 'order',
                'options' => [
                    'id' => 'ACYM_ID',
                    'event_date' => 'ACYM_DATE',
                    'cut_off_date' => 'EB_CUT_OFF_DATE',
                    'title' => 'ACYM_TITLE',
                    'rand' => 'ACYM_RANDOM',
                ],
                'default' => 'event_date',
                'defaultdir' => 'asc',
            ],
        ];

        $displayOptions = array_merge($displayOptions, $catOptions);

        echo $this->acympluginHelper->displayOptions($displayOptions, 'auto'.$this->name, 'grouped');

        echo $this->getCategoryListing();

        $tabHelper->endTab();

        $tabHelper->display('plugin');
    }

    public function displayListing()
    {
        $querySelect = 'SELECT event.* ';
        $query = 'FROM `#__eb_events` AS event ';
        $filters = [];

        $this->pageInfo = new stdClass();
        $this->pageInfo->limit = acym_getCMSConfig('list_limit');
        $this->pageInfo->page = acym_getVar('int', 'pagination_page_ajax', 1);
        $this->pageInfo->start = ($this->pageInfo->page - 1) * $this->pageInfo->limit;
        $this->pageInfo->search = acym_getVar('string', 'plugin_search', '');
        $this->pageInfo->filter_cat = acym_getVar('int', 'plugin_category', 0);
        $this->pageInfo->order = 'event.id';
        $this->pageInfo->orderdir = 'DESC';

        $searchFields = ['event.id', 'event.title'];
        if (!empty($this->pageInfo->search)) {
            $searchVal = '%'.acym_getEscaped($this->pageInfo->search, true).'%';
            $filters[] = implode(" LIKE ".acym_escapeDB($searchVal)." OR ", $searchFields)." LIKE ".acym_escapeDB($searchVal);
        }

        if (!empty($this->pageInfo->filter_cat)) {
            $query .= 'JOIN `#__eb_event_categories` AS cat ON event.id = cat.event_id ';
            $filters[] = 'cat.category_id = '.intval($this->pageInfo->filter_cat);
        }

        $filters[] = 'event.published = 1';

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
                'title' => [
                    'label' => 'ACYM_TITLE',
                    'size' => '8',
                ],
                'event_date' => [
                    'label' => 'ACYM_DATE',
                    'size' => '3',
                    'type' => 'date',
                ],
                'id' => [
                    'label' => 'ACYM_ID',
                    'size' => '1',
                    'class' => 'text-center',
                ],
            ],
            'id' => 'id',
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

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) continue;

            $allcats = explode('-', $parameter->id);
            $selectedArea = [];
            foreach ($allcats as $oneCat) {
                if (empty($oneCat)) continue;

                $selectedArea[] = intval($oneCat);
            }
            if (empty($parameter->from)) {
                $parameter->from = date('Y-m-d H:i:s', $time);
            } else {
                $parameter->from = acym_date(acym_replaceDate($parameter->from), 'Y-m-d H:i:s');
            }
            if (!empty($parameter->to)) $parameter->to = acym_date(acym_replaceDate($parameter->to), 'Y-m-d H:i:s');

            $query = 'SELECT DISTINCT event.id FROM `#__eb_events` AS event ';

            $where = [];
            $where[] = 'event.`published` = 1';

            if (!empty($selectedArea)) {
                $query .= 'JOIN `#__eb_event_categories` AS cat ON event.id = cat.event_id ';
                $where[] = 'cat.category_id IN ('.implode(',', $selectedArea).')';
            }

            if ((empty($parameter->mindelay) || substr($parameter->mindelay, 0, 1) != '-') && (empty($parameter->delay) || substr($parameter->delay, 0, 1) != '-')) {
                if (!empty($parameter->addcurrent)) {
                    $where[] = 'event.`event_end_date` >= '.acym_escapeDB($parameter->from);
                } else {
                    $where[] = 'event.`event_date` >= '.acym_escapeDB($parameter->from);
                }
            }

            if (!empty($parameter->todaysevent)) {
                $where[] = 'event.`event_date` <= '.acym_escapeDB(date('Y-m-d 23:59:59', $time));
            }

            if (!empty($parameter->mindelay)) $where[] = 'event.`event_date` >= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->mindelay));
            if (!empty($parameter->delay)) $where[] = 'event.`event_date` <= '.acym_escapeDB(date('Y-m-d H:i:s', $time + $parameter->delay));
            if (!empty($parameter->to)) $where[] = 'event.`event_date` <= '.acym_escapeDB($parameter->to);

            $query .= ' WHERE ('.implode(') AND (', $where).')';

            $ordering = explode(',', $parameter->order);
            if ($ordering[0] == 'rand') {
                $query .= ' ORDER BY rand()';
            } else {
                $query .= ' ORDER BY '.acym_secureDBColumn(trim($ordering[0])).' '.acym_secureDBColumn(trim($ordering[1]));
            }

            if (empty($parameter->max)) $parameter->max = 20;
            $query .= ' LIMIT '.intval($parameter->max);

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

        acym_loadLanguageFile('com_eventbooking', JPATH_SITE);

        if (!include_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php') {
            if (acym_isAdmin()) acym_enqueueMessage('Could not load the Event Booking helper', 'notice');

            return;
        }

        $this->eventbookingconfig = EventBookingHelper::getConfig();

        $tagsReplaced = [];
        foreach ($tags as $i => $params) {
            if (isset($tagsReplaced[$i])) continue;

            $tagsReplaced[$i] = $this->_replaceContent($tags[$i]);
        }

        $this->acympluginHelper->replaceTags($email, $tagsReplaced, true);
    }

    public function _replaceContent(&$tag)
    {
        $query = 'SELECT event.*, location.name AS location_name FROM `#__eb_events` AS event ';
        $query .= 'LEFT JOIN `#__eb_locations` AS location ON event.location_id = location.id ';
        $query .= 'WHERE event.id = '.intval($tag->id);

        $element = acym_loadObject($query);

        if (empty($element)) {
            if (acym_isAdmin()) acym_enqueueMessage('The event "'.$tag->id.'" could not be loaded', 'notice');

            return '';
        }

        $varFields = [];
        foreach ($element as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $tag->display = empty($tag->display) ? [] : explode(',', $tag->display);
        $tag->custom = empty($tag->custom) ? [] : explode(',', $tag->custom);

        $link = acym_frontendLink('index.php?option=com_eventbooking&view=event&id='.intval($tag->id), false);
        $varFields['{link}'] = $link;

        $title = '';
        $afterTitle = '';
        $afterArticle = '';

        $imagePath = '';
        $contentText = '';
        $customFields = [];


        if (in_array('title', $tag->display)) $title = $element->title;
        if (in_array('short', $tag->display)) $contentText .= $element->short_description;
        if (in_array('desc', $tag->display)) $contentText .= $element->description;

        if (in_array('image', $tag->display) && !empty($element->image)) $imagePath = acym_frontendLink($element->image, false);


        if (in_array('sdate', $tag->display) && $element->event_date > '0001-00-00') {
            $customFields[] = [acym_date($element->event_date, $this->eventbookingconfig->event_date_format, null), acym_translation('EB_EVENT_DATE')];
        }

        if (in_array('edate', $tag->display) && $element->event_end_date > '0001-00-00') {
            $customFields[] = [acym_date($element->event_end_date, $this->eventbookingconfig->event_date_format, null), acym_translation('EB_EVENT_END_DATE')];
        }

        if (in_array('location', $tag->display) && !empty($element->location_id)) {
            $location = '<a href="index.php?option=com_eventbooking&view=map&format=html&location_id='.$element->location_id.'">'.$element->location_name.'</a>';
            $customFields[] = [$location, acym_translation('EB_LOCATION')];
        }

        if (in_array('cats', $tag->display)) {
            $categories = acym_loadObjectList(
                'SELECT cat.id, cat.name
                FROM #__eb_categories AS cat 
                JOIN #__eb_event_categories AS eventcats ON cat.id = eventcats.category_id 
                WHERE eventcats.event_id = '.intval($tag->id).' 
                ORDER BY cat.name ASC'
            );

            foreach ($categories as $i => $oneCat) {
                $categories[$i] = '<a href="index.php?option=com_eventbooking&view=category&id='.$oneCat->id.'">'.acym_escape($oneCat->name).'</a>';
            }
            $customFields[] = [implode(', ', $categories), acym_translation('ACYM_CATEGORIES')];
        }

        if (in_array('capacity', $tag->display)) {
            $capacity = empty($element->event_capacity) ? acym_translation('EB_UNLIMITED') : $element->event_capacity;
            $customFields[] = [$capacity, acym_translation('EB_CAPACTIY')];
        }

        if (in_array('price', $tag->display)) {
            if ($element->individual_price > 0) {
                $price = @EventBookingHelper::formatCurrency($element->individual_price, $this->eventbookingconfig, $element->currency_symbol);
            } else {
                $price = acym_translation('EB_FREE');
            }

            $customFields[] = [$price, acym_translation('EB_PRICE')];
        }

        if (!empty($tag->custom) && !empty($element->custom_fields)) {
            $customFields = array_merge($customFields, $this->_handleCustomFields($element->custom_fields, $tag->custom));
        }

        if (in_array('regstart', $tag->display) && $element->registration_start_date > '0001-00-00') {
            $customFields[] = [acym_date($element->registration_start_date, $this->eventbookingconfig->date_format, null), acym_translation('EB_REGISTRATION_START_DATE')];
        }

        if (in_array('cut', $tag->display) && $element->cut_off_date > '0001-00-00') {
            $customFields[] = [acym_date($element->cut_off_date, $this->eventbookingconfig->date_format, null), acym_translation('EB_CUT_OFF_DATE')];
        }

        if (in_array('indiv', $tag->display) || in_array('group', $tag->display)) {
            $value = [];

            if (in_array('indiv', $tag->display)) {
                $reglink = acym_frontendLink('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$tag->id, false);
                $varFields['{individualregbutton}'] = '<a class="event_registration eb_indivreg" href="'.$reglink.'" target="_blank" >'.acym_translation('EB_REGISTER_INDIVIDUAL').'</a> ';
                $value[] = $varFields['{individualregbutton}'];
            }

            if (in_array('group', $tag->display)) {
                $reglink = acym_frontendLink('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$tag->id, false);
                $varFields['{groupregbutton}'] = '<a class="event_registration eb_groupreg" href="'.$reglink.'" target="_blank" >'.acym_translation('EB_REGISTER_GROUP').'</a> ';
                $value[] = $varFields['{groupregbutton}'];
            }

            $customFields[] = [implode(' ', $value)];
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

    private function _handleCustomFields($customFields, $selected)
    {
        $result = [];

        if (!file_exists(JPATH_ROOT.DS.'components'.DS.'com_eventbooking'.DS.'fields.xml')) return $result;

        $xml = JFactory::getXML(JPATH_ROOT.'/components/com_eventbooking/fields.xml');
        $fields = $xml->fields->fieldset->children();
        $params = new JRegistry();
        $params->loadString($customFields, 'INI');
        $decodedFields = json_decode($customFields);

        foreach ($fields as $oneCustomField) {
            $name = $oneCustomField->attributes()->name;
            $label = acym_translation($oneCustomField->attributes()->label);
            $value = $params->get($name);
            $name = (string)$name;

            if ($value === null && !empty($decodedFields) && !empty($decodedFields->$name)) {
                $value = $decodedFields->$name;
            }

            if (empty($value) || !in_array($name, $selected)) continue;

            $result[] = [$value, $label];
        }

        return $result;
    }

    public function searchEvent()
    {
        $id = acym_getVar('int', 'id');
        if (!empty($id)) {
            $subject = acym_loadResult('SELECT `title` FROM #__eb_events WHERE `id` = '.intval($id));
            if (empty($subject)) $subject = '';
            echo json_encode(['value' => $id.' - '.$subject]);
            exit;
        }

        $return = [];
        $search = acym_getVar('cmd', 'search', '');
        $products = acym_loadObjectList('SELECT `id`, `title` FROM `#__eb_events` WHERE `title` LIKE '.acym_escapeDB('%'.$search.'%').' ORDER BY `title` ASC');

        foreach ($products as $oneProduct) {
            $return[] = [$oneProduct->id, $oneProduct->id.' - '.$oneProduct->title];
        }

        echo json_encode($return);
        exit;
    }

    public function onAcymDeclareConditions(&$conditions)
    {
        acym_loadLanguageFile('com_eventbooking', JPATH_SITE);

        $conditions['user']['ebregistration'] = new stdClass();
        $conditions['user']['ebregistration']->name = acym_translation_sprintf('ACYM_COMBINED_TRANSLATIONS', 'Event Booking', acym_translation('EB_REGISTRANTS'));
        $conditions['user']['ebregistration']->option = '<div class="cell grid-x grid-margin-x">';

        $conditions['user']['ebregistration']->option .= '<div class="intext_select_automation cell">';
        $ajaxParams = json_encode(
            [
                'plugin' => __CLASS__,
                'trigger' => 'searchEvent',
            ]
        );
        $conditions['user']['ebregistration']->option .= acym_select(
            [],
            'acym_condition[conditions][__numor__][__numand__][ebregistration][event]',
            null,
            'class="acym__select acym_select2_ajax" data-placeholder="'.acym_translation('ACYM_ANY_EVENT', true).'" data-params="'.acym_escape($ajaxParams).'"'
        );
        $conditions['user']['ebregistration']->option .= '</div>';

        $status = [];
        $status[] = acym_selectOption('-1', 'ACYM_STATUS');
        $status[] = acym_selectOption('0', 'EB_PENDING');
        $status[] = acym_selectOption('1', 'EB_PAID');
        $status[] = acym_selectOption('2', 'EB_CANCELLED');

        $conditions['user']['ebregistration']->option .= '<div class="intext_select_automation cell">';
        $conditions['user']['ebregistration']->option .= acym_select($status, 'acym_condition[conditions][__numor__][__numand__][ebregistration][status]', '-1', 'class="acym__select"');
        $conditions['user']['ebregistration']->option .= '</div>';

        $conditions['user']['ebregistration']->option .= '</div>';

        $conditions['user']['ebregistration']->option .= '<div class="cell grid-x grid-margin-x">';
        $conditions['user']['ebregistration']->option .= acym_dateField('acym_condition[conditions][__numor__][__numand__][ebregistration][datemin]', '', 'cell shrink');
        $conditions['user']['ebregistration']->option .= '<span class="acym__content__title__light-blue acym_vcenter margin-bottom-0 cell shrink"><</span>';
        $conditions['user']['ebregistration']->option .= '<span class="acym_vcenter">'.acym_translation('EB_REGISTRATION_DATE').'</span>';
        $conditions['user']['ebregistration']->option .= '<span class="acym__content__title__light-blue acym_vcenter margin-bottom-0 cell shrink"><</span>';
        $conditions['user']['ebregistration']->option .= acym_dateField('acym_condition[conditions][__numor__][__numand__][ebregistration][datemax]', '', 'cell shrink');
        $conditions['user']['ebregistration']->option .= '</div>';
    }

    public function onAcymProcessCondition_ebregistration(&$query, $options, $num, &$conditionNotValid)
    {
        $this->processConditionFilter_ebregistration($query, $options, $num);
        $affectedRows = $query->count();
        if (empty($affectedRows)) $conditionNotValid++;
    }

    private function processConditionFilter_ebregistration(&$query, $options, $num)
    {
        $query->join['ebregistration'.$num] = '`#__eb_registrants` AS eventbooking'.$num.' ON (
                                                    eventbooking'.$num.'.email = user.email 
                                                    OR (
                                                        eventbooking'.$num.'.user_id != 0 
                                                        AND eventbooking'.$num.'.user_id = user.cms_id
                                                    )
                                                )';

        if (!empty($options['event'])) $query->where[] = 'eventbooking'.$num.'.event_id = '.intval($options['event']);
        if (!empty($options['status']) && $options['status'] != -1) $query->where[] = 'eventbooking'.$num.'.published = '.intval($options['status']);

        if (!empty($options['datemin'])) {
            $options['datemin'] = acym_replaceDate($options['datemin']);
            if (!is_numeric($options['datemin'])) $options['datemin'] = strtotime($options['datemin']);
            if (!empty($options['datemin'])) {
                $query->where[] = 'eventbooking'.$num.'.register_date > '.acym_escapeDB(date('Y-m-d H:i:s', $options['datemin']));
            }
        }

        if (!empty($options['datemax'])) {
            $options['datemax'] = acym_replaceDate($options['datemax']);
            if (!is_numeric($options['datemax'])) $options['datemax'] = strtotime($options['datemax']);
            if (!empty($options['datemax'])) {
                $query->where[] = 'eventbooking'.$num.'.register_date < '.acym_escapeDB(date('Y-m-d H:i:s', $options['datemax']));
            }
        }
    }

    public function onAcymDeclareSummary_conditions(&$automationCondition)
    {
        $this->summaryConditionFilters($automationCondition);
    }

    private function summaryConditionFilters(&$automationCondition)
    {
        if (!empty($automationCondition['ebregistration'])) {
            if (empty($automationCondition['ebregistration']['event'])) {
                $event = acym_translation('ACYM_ANY_EVENT');
            } else {
                $event = acym_loadResult('SELECT `title` FROM #__eb_events WHERE `id` = '.intval($automationCondition['ebregistration']['event']));
            }

            $status = [
                '-1' => 'ACYM_ANY',
                '0' => 'EB_PENDING',
                '1' => 'EB_PAID',
                '2' => 'EB_CANCELLED',
            ];

            $status = acym_translation($status[$automationCondition['ebregistration']['status']]);

            $finalText = acym_translation_sprintf('ACYM_REGISTERED', $event, $status);

            $dates = [];
            if (!empty($automationCondition['ebregistration']['datemin'])) {
                $dates[] = acym_translation('ACYM_AFTER').' '.acym_replaceDate($automationCondition['ebregistration']['datemin'], true);
            }

            if (!empty($automationCondition['ebregistration']['datemax'])) {
                $dates[] = acym_translation('ACYM_BEFORE').' '.acym_replaceDate($automationCondition['ebregistration']['datemax'], true);
            }

            if (!empty($dates)) {
                $finalText .= ' '.implode(' '.acym_translation('ACYM_AND').' ', $dates);
            }

            $automationCondition = $finalText;
        }
    }

    public function onAcymDeclareFilters(&$filters)
    {
        $newFilters = [];

        $this->onAcymDeclareConditions($newFilters);
        foreach ($newFilters as $oneType) {
            foreach ($oneType as $oneFilterName => $oneFilter) {
                if (!empty($oneFilter->option)) $oneFilter->option = str_replace(['acym_condition', '[conditions]'], ['acym_action', '[filters]'], $oneFilter->option);
                $filters[$oneFilterName] = $oneFilter;
            }
        }
    }

    public function onAcymProcessFilterCount_ebregistration(&$query, $options, $num)
    {
        $this->onAcymProcessFilter_ebregistration($query, $options, $num);

        return acym_translation_sprintf('ACYM_SELECTED_USERS', $query->count());
    }

    public function onAcymProcessFilter_ebregistration(&$query, $options, $num)
    {
        $this->processConditionFilter_ebregistration($query, $options, $num);
    }

    public function onAcymDeclareSummary_filters(&$automationFilter)
    {
        $this->summaryConditionFilters($automationFilter);
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

        $categories = acym_loadObjectList('SELECT `id`, `name` FROM #__eb_categories', 'id');

        foreach ($categories as $key => $category) {
            $categories[$key] = $category->name;
        }

        $categories = ['' => acym_translation('ACYM_ANY_CATEGORY')] + $categories;

        $triggers['classic']['eventbooking_reminder'] = new stdClass();
        $triggers['classic']['eventbooking_reminder']->name = acym_translation_sprintf('ACYM_COMBINED_TRANSLATIONS', 'EventBooking', acym_translation('ACYM_REMINDER'));
        $triggers['classic']['eventbooking_reminder']->option = '<div class="grid-x cell acym_vcenter"><div class="grid-x cell grid-margin-x acym_vcenter margin-bottom-1">';
        $triggers['classic']['eventbooking_reminder']->option .= '<div class="cell medium-shrink">
                                                                <input 
                                                                    type="number" 
                                                                    name="[triggers][classic][eventbooking_reminder][number]" 
                                                                    class="intext_input" 
                                                                    value="'.(empty($defaultValues['eventbooking_reminder']) ? '1' : $defaultValues['eventbooking_reminder']['number']).'">
                                                            </div>';
        $triggers['classic']['eventbooking_reminder']->option .= '<div class="cell medium-shrink">'.acym_select(
                $every,
                '[triggers][classic][eventbooking_reminder][time]',
                empty($defaultValues['eventbooking_reminder']) ? '86400' : $defaultValues['eventbooking_reminder']['time'],
                'data-class="intext_select acym__select"'
            ).'</div></div>';
        $triggers['classic']['eventbooking_reminder']->option .= '<div class="grid-x cell grid-margin-x acym_vcenter margin-bottom-1"><div class="cell medium-shrink">'.acym_select(
                $when,
                '[triggers][classic][eventbooking_reminder][when]',
                empty($defaultValues['eventbooking_reminder']) ? 'before' : $defaultValues['eventbooking_reminder']['when'],
                'data-class="intext_select acym__select"'
            ).'</div>';
        $triggers['classic']['eventbooking_reminder']->option .= '<div class="cell medium-shrink">'.acym_translation('ACYM_AN_EVENT_IN').'</div>';
        $triggers['classic']['eventbooking_reminder']->option .= '<div class="cell medium-auto">'.acym_select(
                $categories,
                '[triggers][classic][eventbooking_reminder][cat]',
                empty($defaultValues['eventbooking_reminder']) ? '' : $defaultValues['eventbooking_reminder']['cat'],
                'data-class="intext_select_larger intext_select acym__select"'
            ).'</div>';
        $triggers['classic']['eventbooking_reminder']->option .= '</div></div>';
    }

    public function onAcymExecuteTrigger(&$step, &$execute, $data)
    {
        $time = $data['time'];
        $triggers = json_decode($step->triggers, true);

        if (!empty($triggers['eventbooking_reminder']['number'])) {
            $config = acym_config();
            $triggerReminder = $triggers['eventbooking_reminder'];

            $timestamp = ($triggerReminder['number'] * $triggerReminder['time']);

            if ($triggerReminder['when'] == 'before') {
                $timestamp += $time;
            } else {
                $timestamp -= $time;
            }


            $join = [];
            $where = [];

            if (!empty($triggerReminder['cat'])) {
                $join[] = 'LEFT JOIN #__eb_event_categories as cat ON `event`.`id` = `cat`.`event_id`';
                $where[] = '`cat`.`category_id` = '.intval($triggerReminder['cat']);
            }

            $where[] = '`event`.`event_date` >= '.acym_escapeDB(acym_date($timestamp, 'Y-m-d H:i:s', true));
            $where[] = '`event`.`event_date` <= '.acym_escapeDB(acym_date($timestamp + $config->get('cron_frequency', '900'), 'Y-m-d H:i:s', true));
            $where[] = '`event`.`published` = 1';

            $events = acym_loadObjectList('SELECT * FROM `#__eb_events` as event '.implode(' ', $join).' WHERE '.implode(' AND ', $where));
            if (!empty($events)) $execute = true;
        }
    }

    public function onAcymDeclareSummary_triggers(&$automation)
    {
        if (!empty($automation->triggers['eventbooking_reminder'])) {
            $every = [
                '3600' => acym_translation('ACYM_HOURS'),
                '86400' => acym_translation('ACYM_DAYS'),
            ];

            $when = [
                'before' => acym_translation('ACYM_BEFORE'),
                'after' => acym_translation('ACYM_AFTER'),
            ];

            $categories = acym_loadObjectList('SELECT `id`, `name` FROM #__eb_categories', 'id');

            foreach ($categories as $key => $category) {
                $categories[$key] = $category->name;
            }

            $categories = ['' => acym_translation('ACYM_ANY_CATEGORY')] + $categories;

            $final = $automation->triggers['eventbooking_reminder']['number'].' ';
            $final .= $every[$automation->triggers['eventbooking_reminder']['time']].' ';
            $final .= $when[$automation->triggers['eventbooking_reminder']['when']].' ';
            $final .= acym_translation('ACYM_AN_EVENT_IN').' '.strtolower($categories[$automation->triggers['eventbooking_reminder']['cat']]);

            $automation->triggers['eventbooking_reminder'] = $final;
        }
    }
}

