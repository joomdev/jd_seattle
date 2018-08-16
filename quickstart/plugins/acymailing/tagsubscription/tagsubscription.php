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

class plgAcymailingTagsubscription extends JPlugin
{
    var $listunsubscribe = false;
    var $lists = array();
    var $listsowner = array();
    var $listsinfo = array();
    var $campaigns = array();
    var $unsubscribeLink = false;
    var $unsubscribeItem = '';

    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        if (!isset($this->params)) {
            $plugin = JPluginHelper::getPlugin('acymailing', 'tagsubscription');
            $this->params = new acyParameter($plugin->params);
        }
        $this->acypluginsHelper = acymailing_get('helper.acyplugins');
    }

    function acymailing_getPluginType()
    {

        if ($this->params->get('frontendaccess') == 'none' && !acymailing_isAdmin()) {
            return;
        }
        $onePlugin = new stdClass();
        $onePlugin->name = acymailing_translation('SUBSCRIPTION');
        $onePlugin->function = 'acymailingtagsubscription_show';
        $onePlugin->help = 'plugin-tagsubscription';

        return $onePlugin;
    }

    function acymailingtagsubscription_show()
    {

        $others = array();
        $others['unsubscribe'] = array('name' => acymailing_translation('UNSUBSCRIBE_LINK'), 'default' => acymailing_translation('UNSUBSCRIBE', true));
        $others['modify'] = array('name' => acymailing_translation('MODIFY_SUBSCRIPTION_LINK'), 'default' => acymailing_translation('MODIFY_SUBSCRIPTION', true));
        $others['confirm'] = array('name' => acymailing_translation('CONFIRM_SUBSCRIPTION_LINK'), 'default' => acymailing_translation('CONFIRM_SUBSCRIPTION', true));
        $others['subscribe'] = array('name' => acymailing_translation('SUBSCRIBE_LINK'), 'default' => acymailing_translation('SUBSCRIBE', true));

        ?>
        <script language="javascript" type="text/javascript">
            <!--
            var openLists = true;
            var selectedTag = '';
            function changeTag(tagName){
                selectedTag = tagName;
                defaultText = [];
                <?php
                $k = 0;
                foreach ($others as $tagname => $tag) {
                    echo "document.getElementById('tr_$tagname').className = 'row$k';";
                    echo "defaultText['$tagname'] = '".$tag['default']."';";
                    $k = 1 - $k;
                }
                ?>
                document.getElementById('tr_' + tagName).className = 'selectedrow';
                document.adminForm.tagtext.value = defaultText[tagName];
                if(tagName == 'subscribe'){
                    document.getElementById('iframelists').style.display = '';
                    document.getElementById('subscriptionlists').style.display = '';
                    if(openLists) displayLists();
                }else{
                    document.getElementById('iframelists').style.display = 'none';
                    document.getElementById('subscriptionlists').style.display = 'none';
                }
                setSubscriptionTag();
            }

            function setSubscriptionTag(){
                var tag = '{' + selectedTag;

                if(document.getElementById('tagmenu').value != 0) tag += "|itemid:" + document.getElementById('tagmenu').value;
                if(selectedTag == 'subscribe') tag += "|lists:" + document.getElementById('paramslistids').value;

                tag += '}' + document.adminForm.tagtext.value + '{/' + selectedTag + '}'
                setTag(tag);
            }

            function displayLists(){
                var box = document.getElementById('iframelists');
                if(openLists){
                    box.style.display = 'block';
                    box.className += ' slide_open';
                }else{
                    box.className = box.className.replace('slide_open', 'slide_close');
                }

                if(!openLists) setSubscriptionTag();
                openLists = !openLists;
            }
            //-->
        </script>
        <?php

        acymailing_addScript(true, "document.addEventListener(\"DOMContentLoaded\", function(){ changeTag('unsubscribe'); });");

        $text = '<div id="iframelists" style="display:none;"><iframe src="index.php?option=com_acymailing&tmpl=component&ctrl='.(acymailing_isAdmin() ? '' : 'front').'chooselist&popup=0&task=listids&all=0" width="98%" height="100%" scrolling="auto"></iframe></div>
				<div class="onelineblockoptions">
					<span class="acyblocktitle">'.acymailing_translation('SUBSCRIPTION').'</span>
					<table class="acymailing_table" cellpadding="1">';
        $menus = acymailing_loadObjectList('SELECT 0 AS id, "- - -" AS title UNION SELECT id, title FROM #__menu WHERE link LIKE "%com_acymailing%" AND client_id = 0 AND published = 1');
        $text .= '<tr>
					<td><label for="tagtext">'.acymailing_translation('FIELD_TEXT').': </label><input type="text" name="tagtext" id="tagtext" onchange="setSubscriptionTag();"></td>
					<td><label for="tagmenu">'.acymailing_translation('ACY_MENU').': </label>'.acymailing_select($menus, "tagmenu", 'class="inputbox" size="1" onchange="setSubscriptionTag();"', 'id', 'title', '').'</td>
				</tr>
				<tr id="subscriptionlists">
					<td colspan="2">
						<button class="acymailing_button_grey" onclick="displayLists();return false;">'.acymailing_translation('LISTS').'</button>
						<input class="inputbox" id="paramslistids" name="listids" type="text" style="width:100px" value="">
					</td>
				</tr>';
        $text .= '</table>
					<table class="acymailing_table" cellpadding="1">';

        $k = 0;
        foreach ($others as $tagname => $tag) {
            $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="changeTag(\''.$tagname.'\');" id="tr_'.$tagname.'" ><td class="acytdcheckbox"></td><td>'.$tag['name'].'</td></tr>';
            $k = 1 - $k;
        }
        $text .= '</table></div>';

        $others = array();
        $others['name'] = acymailing_translation('LIST_NAME');
        $others['names'] = acymailing_translation('ACY_LIST_NAMES');
        $others['description'] = acymailing_translation('ACY_DESCRIPTION');
        $others['count'] = trim(acymailing_translation('GEOLOC_NB_USERS', true), ':');
        $others['count|listid:0'] = trim(acymailing_translation('GEOLOC_NB_USERS', true), ':').' ('.acymailing_translation('ALL_LISTS').')';
        $others['id'] = acymailing_translation('ACY_ID', true);

        $text .= '<div class="onelineblockoptions">
					<span class="acyblocktitle">'.acymailing_translation('LIST').'</span>
					<table class="acymailing_table" cellpadding="1">';

        $k = 0;
        foreach ($others as $tagname => $tag) {
            $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{list:'.$tagname.'}\');insertTag();" id="tr_'.$tagname.'" ><td class="acytdcheckbox"></td><td>'.$tag.'</td></tr>';
            $k = 1 - $k;
        }

        $text .= '</table></div>';

        $text .= '<div class="onelineblockoptions">
					<span class="acyblocktitle">'.acymailing_translation('NEWSLETTER').'</span>
					<table class="acymailing_table" cellpadding="1">';
        $othersMail = array('mailid', 'subject', 'alias', 'key', 'altbody');
        $k = 0;
        foreach ($othersMail as $tag) {
            $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\'{mail:'.$tag.'}\');insertTag();" id="tr_'.$tag.'" ><td class="acytdcheckbox"></td><td>'.$tag.'</td></tr>';
            $k = 1 - $k;
        }
        $text .= '</table></div>';

        echo $text;
    }

    function onAcyDisplayActions(&$type)
    {
        $type['list'] = acymailing_translation('ACYMAILING_LIST');
        $status = array();
        $status[] = acymailing_selectOption(1, acymailing_translation('SUBSCRIBE_TO'));
        $status[] = acymailing_selectOption(0, acymailing_translation('REMOVE_FROM'));
        $status[] = acymailing_selectOption(-1, acymailing_translation('ACY_UNSUB_FROM'));

        $lists = $this->_getLists();
        $otherlists = array();
        $onChange = '';
        if (acymailing_level(3)) {
            $otherlists = acymailing_loadObjectList('SELECT b.listid, b.name FROM #__acymailing_listcampaign as a JOIN #__acymailing_list as b on a.listid = b.listid GROUP BY b.listid ORDER BY b.ordering ASC', 'listid');
            $onChange = 'onchange="onAcyDisplayAction_list(__num__);"';

            $js = "function onAcyDisplayAction_list(num){
				if(!document.getElementById('campaigndelay'+num)) return;
				if(document.getElementById('subliststatus'+num).value == 1 && document.getElementById('sublistvalue'+num).value.indexOf('_campaign') > 0){
					document.getElementById('campaigndelay'+num).style.display = 'inline';
				}else{
					document.getElementById('campaigndelay'+num).style.display = 'none';
				}
			}";
            acymailing_addScript(true, $js);
        }

        $listsdrop = array();
        foreach ($lists as $oneList) {
            if (!empty($otherlists[$oneList->listid])) {
                $listsdrop[] = acymailing_selectOption($oneList->listid.'_campaign', $otherlists[$oneList->listid]->name.' + '.acymailing_translation('CAMPAIGN'));
            }
            $listsdrop[] = acymailing_selectOption($oneList->listid, $oneList->name);
        }

        $return = '<div id="action__num__list">'.acymailing_select($status, "action[__num__][list][status]", 'class="inputbox" size="1" '.$onChange, 'value', 'text', '', 'subliststatus__num__').' '.acymailing_select($listsdrop, "action[__num__][list][selectedlist]", 'class="inputbox" size="1" '.$onChange, 'value', 'text', '', 'sublistvalue__num__');
        if (!empty($otherlists)) {
            $delay = array();
            $delay[] = acymailing_selectOption('day', acymailing_translation('DAYS'));
            $delay[] = acymailing_selectOption('week', acymailing_translation('WEEKS'));
            $delay[] = acymailing_selectOption('month', acymailing_translation('MONTHS'));

            $listHours = array();
            $listHours[] = acymailing_selectOption('', '- -');
            for ($i = 0; $i < 24; $i++) {
                $listHours[] = acymailing_selectOption(($i < 10 ? '0'.$i : $i), ($i < 10 ? '0'.$i : $i));
            }
            $hours = acymailing_select($listHours, 'action[__num__][list][sendhours]', 'class="inputbox" size="1" style="width:60px;"', 'value', 'text', '');

            $listMinutess = array();
            $listMinutess[] = acymailing_selectOption('', '- -');
            for ($i = 0; $i < 60; $i += 5) {
                $listMinutess[] = acymailing_selectOption(($i < 10 ? '0'.$i : $i), ($i < 10 ? '0'.$i : $i));
            }
            $minutes = acymailing_select($listMinutess, 'action[__num__][list][sendminutes]', 'class="inputbox" size="1" style="width:60px;"', 'value', 'text', '');

            $return .= '<br /><span id="campaigndelay__num__">'.acymailing_translation_sprintf('TRIGGER_CAMPAIGN', '<input type="text" name="action[__num__][list][delaynum]" value="0" style="width:50px" />', acymailing_select($delay, "action[__num__][list][delaytype]", 'class="inputbox" size="1" style="width:120px;"', 'value', 'text')).' @ '.$hours.' : '.$minutes;
            $return .= '<br />'.acymailing_translation_sprintf('ACY_CAMPAIGN_NB_FOLLOW_SKIPED', '<input type="text" name="action[__num__][list][skipedfollowups]" value="0" style="width:25px;" />').'</span>';
        }
        $return .= '</div>';

        return $return;
    }

    private function _getLists()
    {
        if (!empty($this->allLists)) {
            return $this->allLists;
        }
        $list = acymailing_get('class.list');
        if (acymailing_isAdmin()) {
            $this->allLists = $list->getLists();
        } else {
            $this->allLists = $list->getFrontendLists();
        }

        return $this->allLists;
    }

    private function _getCampaigns()
    {

        $list = acymailing_get('class.list');
        if (acymailing_isAdmin()) {
            return $list->getAllCampaigns();
        }

        return $list->getFrontendCampaigns();
    }

    function onAcyDisplayFilters(&$type, $context = "massactions")
    {

        if ($this->params->get('displayfilter_'.$context, true) == false) {
            return;
        }

        $type['list'] = acymailing_translation('ACYMAILING_LIST');
        $status = acymailing_get('type.statusfilterlist');
        $status->extra = 'onchange="countresults(__num__);"';

        $lists = $this->_getLists();
        $campaigns = $this->_getCampaigns();
        $listsdrop = array();

        $listsdrop[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('LISTS'));
        foreach ($lists as $oneList) {
            $listsdrop[] = acymailing_selectOption($oneList->listid, $oneList->name);
        }
        $listsdrop[] = acymailing_selectOption('</OPTGROUP>');

        if (count($campaigns) > 0) {
            $listsdrop[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('ACY_CAMPAIGNS'));
            foreach ($campaigns as $campaign) {
                $listsdrop[] = acymailing_selectOption($campaign->listid, $campaign->name);
            }
            $listsdrop[] = acymailing_selectOption('</OPTGROUP>');
        }

        $dates = array();
        $dates[] = acymailing_selectOption(0, acymailing_translation('SUBSCRIPTION_DATE'));
        $dates[] = acymailing_selectOption(1, acymailing_translation('UNSUBSCRIPTION_DATE'));

        $filter = '<div id="filter__num__list">'.$status->display("filter[__num__][list][status]", 1, false).' '.acymailing_select($listsdrop, "filter[__num__][list][selectedlist]", 'class="inputbox" style="max-width:200px" size="1" onchange="countresults(__num__)"', 'value', 'text');
        $filter .= '<br /><input type="text" name="filter[__num__][list][subdateinf]" onclick="displayDatePicker(this,event)" onchange="countresults(__num__)" style="width:60px;" /> < '.acymailing_select($dates, "filter[__num__][list][dates]", 'class="inputbox" style="max-width:200px" size="1" onchange="countresults(__num__)"', 'value', 'text').' < <input type="text" name="filter[__num__][list][subdatesup]" onclick="displayDatePicker(this,event)" onchange="countresults(__num__)" style="width:60px;" /></div>';

        return $filter;
    }

    function onAcyDisplayFilter_list($filter)
    {
        $listClass = acymailing_get('class.list');
        $list = $listClass->get($filter['selectedlist']);
        $listName = !empty($list->name) ? $list->name : $filter['selectedlist'];
        $subComp = array(0 => acymailing_translation('SUBSCRIPTION_DATE'), 1 => acymailing_translation('UNSUBSCRIPTION_DATE'));
        $subStatus = array(
            '1' => acymailing_translation('SUBSCRIBERS'),
            '2' => acymailing_translation('PENDING_SUBSCRIPTION'),
            '-1' => acymailing_translation('UNSUBSCRIBERS'),
            '-2' => acymailing_translation('NO_SUBSCRIPTION')
        );

        return acymailing_translation('ACYMAILING_LIST').' : '.$subStatus[$filter['status']].' '.$listName.' : '.$filter['subdateinf'].' < '.$subComp[$filter['dates']].' < '.$filter['subdatesup'];
    }

    function onAcyProcessFilter_list(&$query, $filter, $num)
    {
        $otherconditions = '';
        $field = empty($filter['dates']) ? 'subdate' : 'unsubdate';
        if (!empty($filter['subdateinf'])) {
            $filter['subdateinf'] = acymailing_replaceDate($filter['subdateinf']);
            if (!is_numeric($filter['subdateinf'])) {
                $filter['subdateinf'] = strtotime($filter['subdateinf']);
            }
            if (!empty($filter['subdateinf'])) {
                $otherconditions .= ' AND list'.$num.'.'.$field.' > '.$filter['subdateinf'];
            }
        }

        if (!empty($filter['subdatesup'])) {
            $filter['subdatesup'] = acymailing_replaceDate($filter['subdatesup']);
            if (!is_numeric($filter['subdatesup'])) {
                $filter['subdatesup'] = strtotime($filter['subdatesup']);
            }
            if (!empty($filter['subdatesup'])) {
                $otherconditions .= ' AND list'.$num.'.'.$field.' < '.$filter['subdatesup'];
            }
        }

        $query->leftjoin['list'.$num] = '#__acymailing_listsub AS list'.$num.' ON sub.subid = list'.$num.'.subid AND list'.$num.'.listid = '.intval($filter['selectedlist']).$otherconditions;
        if ($filter['status'] == -2) {
            $query->where[] = 'list'.$num.'.listid IS NULL';
        } else {
            $query->where[] = 'list'.$num.'.status = '.intval($filter['status']);
        }
    }

    function onAcyProcessFilterCount_list(&$query, $filter, $num)
    {
        $this->onAcyProcessFilter_list($query, $filter, $num);

        return acymailing_translation_sprintf('SELECTED_USERS', $query->count());
    }

    function onAcyProcessAction_list($cquery, $action, $num)
    {
        $listid = intval($action['selectedlist']);
        $listClass = acymailing_get('class.list');
        if (is_numeric($action['selectedlist'])) {
            $myList = $listClass->get($listid);
            if (empty($myList->listid)) {
                return 'ERROR : List '.$listid.' not found';
            }

            if (empty($action['status'])) {
                $query = 'DELETE listremove.* FROM '.acymailing_table('listsub').' AS listremove ';
                $query .= 'JOIN #__acymailing_subscriber AS sub ON listremove.subid = sub.subid ';
                if (!empty($cquery->join)) {
                    $query .= ' JOIN '.implode(' JOIN ', $cquery->join);
                }
                if (!empty($cquery->leftjoin)) {
                    $query .= ' LEFT JOIN '.implode(' LEFT JOIN ', $cquery->leftjoin);
                }
                $query .= ' WHERE listremove.listid = '.$listid;
                if (!empty($cquery->where)) {
                    $query .= ' AND ('.implode(') AND (', $cquery->where).')';
                }
            } elseif ($action['status'] == -1) {
                $query = 'UPDATE '.acymailing_table('listsub').' AS listsub'.$num.' JOIN '.acymailing_table('subscriber').' AS sub ON listsub'.$num.'.subid = sub.subid ';
                if (!empty($cquery->join)) {
                    $query .= ' JOIN '.implode(' JOIN ', $cquery->join);
                }
                if (!empty($cquery->leftjoin)) {
                    $query .= ' LEFT JOIN '.implode(' LEFT JOIN ', $cquery->leftjoin);
                }
                $query .= ' SET listsub'.$num.'.status = -1, listsub'.$num.'.unsubdate = '.time().' WHERE listsub'.$num.'.listid = '.$listid;
                if (!empty($cquery->where)) {
                    $query .= ' AND ('.implode(') AND (', $cquery->where).')';
                }
            } else {
                $query = 'INSERT IGNORE INTO '.acymailing_table('listsub').' (listid,subid,subdate,status) ';
                $query .= $cquery->getQuery(array($listid, 'sub.subid', time(), 1));
            }
            $nbsubscribed = acymailing_query($query);

            if (empty($action['status'])) {
                return acymailing_translation_sprintf('IMPORT_REMOVE', $nbsubscribed, '<b><i>'.$myList->name.'</i></b>');
            } elseif ($action['status'] == -1) {
                return acymailing_translation_sprintf('NB_UNSUB_USERS', $nbsubscribed);
            } else {
                return acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $nbsubscribed, '<b><i>'.$myList->name.'</i></b>');
            }
        }

        $myList = $listClass->get($listid);
        if (empty($myList->listid)) {
            return 'ERROR : List '.$listid.' not found';
        }
        if (empty($action['status'])) {
            $query = 'SELECT listremove.`subid` FROM #__acymailing_listsub as listremove';
            $query .= ' JOIN #__acymailing_subscriber as sub ON listremove.subid = sub.subid ';
            $condition = ' WHERE listremove.listid = '.$listid;
        } elseif ($action['status'] == -1) {
            $query = 'SELECT listunsub.`subid` FROM #__acymailing_listsub as listunsub JOIN #__acymailing_subscriber as sub ON listunsub.subid = sub.subid ';
            $condition = ' WHERE listunsub.listid = '.$listid.' AND listunsub.status != -1';
        } else {
            $query = 'SELECT sub.`subid` FROM #__acymailing_subscriber as sub';
            $query .= ' LEFT JOIN #__acymailing_listsub as listsubscribe ON listsubscribe.subid = sub.subid AND listsubscribe.listid = '.$listid;
            $condition = ' WHERE listsubscribe.subid IS NULL';
        }
        if (!empty($cquery->join)) {
            $query .= ' JOIN '.implode(' JOIN ', $cquery->join);
        }
        if (!empty($cquery->leftjoin)) {
            $query .= ' LEFT JOIN '.implode(' LEFT JOIN ', $cquery->leftjoin);
        }
        $query .= $condition;
        if (!empty($cquery->where)) {
            $query .= ' AND ('.implode(') AND (', $cquery->where).')';
        }
        if (!empty($cquery->orderBy)) {
            $query .= ' ORDER BY '.$cquery->orderBy;
        }
        if (!empty($cquery->limit)) {
            $query .= ' LIMIT '.intval($cquery->limit);
        }
        $subids = acymailing_loadResultArray($query);

        if (!empty($subids)) {
            $listsubClass = acymailing_get('class.listsub');
            $time = time();
            $timeFunction = 'acymailing_getTime';
            if (!isset($action['sendhours']) || strlen($action['sendhours']) < 1) {
                $action['sendhours'] = '%H';
                $timeFunction = 'strftime';
            }
            if (!isset($action['sendminutes']) || strlen($action['sendminutes']) < 1) {
                $action['sendminutes'] = '%M';
            }
            $format = '%Y-%m-%d '.$action['sendhours'].':'.$action['sendminutes'].':00';
            if ($action['status'] == 1 && !empty($action['delaynum'])) {
                $listsubClass->campaigndelay = $timeFunction(strftime($format, strtotime('+'.intval($action['delaynum']).' '.$action['delaytype'])));
            } else {
                $listsubClass->campaigndelay = $timeFunction(strftime($format, $time));
            }
            if ($listsubClass->campaigndelay < $time) {
                $listsubClass->campaigndelay = 0;
            } else {
                $listsubClass->campaigndelay -= $time;
            }

            if (!empty($action['skipedfollowups'])) {
                $action['skipedfollowups'] = intval($action['skipedfollowups']);
                if (!empty($action['skipedfollowups'])) {
                    $listsubClass->skipedfollowups = $action['skipedfollowups'];
                }
            }
            $listsubClass->checkAccess = false;
            $listsubClass->sendNotif = false;
            $listsubClass->sendConf = false;
            foreach ($subids as $subid) {
                if (empty($action['status'])) {
                    $listsubClass->removeSubscription($subid, array($listid));
                } elseif ($action['status'] == -1) {
                    $listsubClass->updateSubscription($subid, array('-1' => array($listid)));
                } else {
                    $listsubClass->addSubscription($subid, array('1' => array($listid)));
                }
            }
        }

        $nbsubscribed = count($subids);
        if (empty($action['status'])) {
            return acymailing_translation_sprintf('IMPORT_REMOVE', $nbsubscribed, '<b><i>'.$myList->name.'</i></b>');
        } elseif ($action['status'] == -1) {
            return acymailing_translation_sprintf('NB_UNSUB_USERS', $nbsubscribed);
        } else {
            return acymailing_translation_sprintf('IMPORT_SUBSCRIBE_CONFIRMATION', $nbsubscribed, '<b><i>'.$myList->name.'</i></b>');
        }
    }

    function acymailing_replaceusertags(&$email, &$user, $send = true)
    {
        $this->_replacelisttags($email, $user, $send);

        if (empty($user->key) && !empty($user->subid)) {
            $user->key = acymailing_generateKey(14);
            acymailing_query('UPDATE '.acymailing_table('subscriber').' SET `key`= '.acymailing_escapeDB($user->key).' WHERE subid = '.(int)$user->subid.' LIMIT 1');
        }

        if (!isset($user->key)) {
            $user->key = '';
        }

        if ($this->unsubscribeLink && !$this->listunsubscribe && $this->params->get('listunsubscribe', 0) && method_exists($email, 'addCustomHeader')) {
            $lang = empty($email->language) ? '' : '&lang='.$email->language;
            $myLink = 'index.php?subid='.intval($user->subid).'&option=com_acymailing&ctrl=user&task=out&mailid='.$email->mailid.'&key='.urlencode($user->key).$this->unsubscribeItem.$lang;

            $mainurl = acymailing_mainURL($myLink);
            $myLink = $mainurl.$myLink;
            if ((bool)$this->params->get('unsubscribetemplate', false)) {
                $myLink .= '&tmpl=component';
            }

            $this->listunsubscribe = true;
            $mailto = $this->params->get('listunsubscribeemail');
            if (empty($mailto)) {
                $mailto = @$email->replyemail;
            }
            if (empty($mailto)) {
                $config = acymailing_config();
                $mailto = $config->get('reply_email');
            }
            $email->addCustomHeader('List-Unsubscribe: <'.$myLink.'>, <mailto:'.$mailto.'?subject=unsubscribe_user_'.$user->subid.'&body=Please%20unsubscribe%20user%20ID%20'.$user->subid.'>');
        }
    }

    function acymailing_replacetags(&$email, $send = true)
    {
        if (acymailing_getVar('none', 'task', '') == 'replacetags') {
            return;
        }

        $this->_replacesubscriptiontags($email);
        $this->_replacemailtags($email);
    }

    private function _replacemailtags(&$email)
    {
        $variables = array('subject', 'body', 'altbody');
        $acypluginsHelper = acymailing_get('helper.acyplugins');
        $result = $acypluginsHelper->extractTags($email, 'mail');
        $tags = array();

        foreach ($result as $key => $oneTag) {
            $field = $oneTag->id;
            if (!empty($email) && !empty($email->$field)) {
                $text = $email->$field;
                $acypluginsHelper->formatString($text, $oneTag);
                $tags[$key] = $text;
            } else {
                $tags[$key] = $oneTag->default;
            }
        }

        foreach ($variables as $var) {
            if (empty($email->$var)) {
                continue;
            }
            $email->$var = str_replace(array_keys($tags), $tags, $email->$var);
        }
    }

    private function _replacelisttags(&$email, &$user, $send)
    {
        if (!empty($email->ReplyTo)) {
            $toDelete = 0;
            foreach ($email->ReplyTo as $i => $replyto) {
                if (trim($i) != '{list:members}') {
                    continue;
                }
                $toDelete = $i;
                break;
            }
            if (!empty($toDelete)) {
                unset($email->ReplyTo[$toDelete]);
                $acyConfig = acymailing_config();
                $listMembers = $this->loadlistmembers($email, $user);
                foreach ($listMembers as $member) {
                    if ($acyConfig->get('add_names', true) && !empty($member->name)) {
                        $replyToName = $email->cleanText(trim($member->name));
                    } else {
                        $replyToName = '';
                    }
                    $email->AddReplyTo($email->cleanText($member->email), $replyToName);
                }
            }
        }

        $this->acypluginsHelper = acymailing_get('helper.acyplugins');
        $tags = $this->acypluginsHelper->extractTags($email, 'list');
        if (empty($tags)) {
            return;
        }

        $replaceTags = array();
        foreach ($tags as $oneTag => $parameter) {
            $method = '_list'.trim(strtolower($parameter->id));

            if (method_exists($this, $method)) {
                $replaceTags[$oneTag] = $this->$method($email, $user, $parameter);
            } else {
                $replaceTags[$oneTag] = 'Method not found : '.$method;
            }
        }

        $this->acypluginsHelper->replaceTags($email, $replaceTags, true);
    }

    private function _getattachedlistid($email, $subid)
    {

        $mailid = $email->mailid;
        $type = strtolower($email->type);

        if (isset($this->lists[$mailid][$subid])) {
            return $this->lists[$mailid][$subid];
        }


        if ($type == 'followup') {
            $listid = acymailing_loadResult(
                'SELECT a.listid
							FROM #__acymailing_listsub AS a
							JOIN #__acymailing_listcampaign AS b
								ON a.listid = b.listid
							JOIN #__acymailing_listmail AS c
								ON b.campaignid = c.listid
							WHERE a.subid = '.intval($subid).'
								AND c.mailid = '.intval($mailid).'
							ORDER BY a.status DESC LIMIT 1'
            );
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if (in_array($type, array('news', 'autonews'))) {
            if (!empty($subid)) {
                $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_listsub as a JOIN #__acymailing_listmail as b ON a.listid = b.listid WHERE a.subid = '.intval($subid).' AND b.mailid = '.intval($mailid).' ORDER BY a.status DESC LIMIT 1');
                if (!empty($listid)) {
                    $this->lists[$mailid][$subid] = $listid;

                    return $listid;
                }
            }

            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_listmail as a JOIN #__acymailing_list as b ON a.listid = b.listid WHERE a.mailid = '.intval($mailid).' ORDER BY b.published DESC , b.visible DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if ($type == 'welcome' && !empty($subid)) {
            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_list as a JOIN #__acymailing_listsub as b ON a.listid = b.listid WHERE a.welmailid = '.intval($mailid).' AND b.subid = '.intval($subid).' ORDER BY b.subdate DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if ($type == 'unsub' && !empty($subid)) {
            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_list as a JOIN #__acymailing_listsub as b ON a.listid = b.listid WHERE a.unsubmailid = '.intval($mailid).' AND b.subid = '.intval($subid).' ORDER BY b.unsubdate DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        $allLists = array_merge(acymailing_getVar('array', 'subscription', '', ''), explode(',', acymailing_getVar('string', 'hiddenlists', '', '')));
        $data = acymailing_getVar('array', 'data', '', '');
        if (!empty($data['listsub'])) {
            $allLists = array_merge($allLists, array_keys($data['listsub']));
        }

        if (!empty($allLists) && in_array($type, array('unsub', 'welcome'))) {
            acymailing_arrayToInteger($allLists);
            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_list as a WHERE (a.welmailid = '.intval($mailid).' OR unsubmailid = '.intval($mailid).') AND listid IN ('.implode(',', $allLists).') ORDER BY a.published DESC, a.visible DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }

            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_list as a WHERE (a.welmailid = '.intval($mailid).' OR unsubmailid = '.intval($mailid).') ORDER BY a.published DESC, a.visible DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }

        if (!empty($allLists)) {
            foreach ($allLists as $listid) {
                if (!empty($listid)) {
                    $this->lists[$mailid][$subid] = intval($listid);

                    return intval($listid);
                }
            }
        }

        if (!empty($subid)) {
            $listid = acymailing_loadResult('SELECT a.listid FROM #__acymailing_listsub as a JOIN #__acymailing_list as b ON a.listid = b.listid WHERE a.subid = '.intval($subid).' ORDER BY b.published DESC , b.visible DESC LIMIT 1');
            if (!empty($listid)) {
                $this->lists[$mailid][$subid] = $listid;

                return $listid;
            }
        }
    }


    private function _listcount(&$email, &$user, &$parameter)
    {
        if (!isset($parameter->listid)) {
            $listid = $this->_getattachedlistid($email, $user->subid);
        } else {
            $listid = $parameter->listid;
        }

        if (empty($listid)) {
            return acymailing_loadResult('SELECT COUNT(subid) FROM #__acymailing_subscriber');
        } else {
            return acymailing_loadResult('SELECT COUNT(subid) FROM #__acymailing_listsub WHERE listid = '.intval($listid).' AND status = 1');
        }
    }

    private function _listsubscription(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return "";
        }
        $listSubClass = acymailing_get('class.listsub');

        return $listSubClass->getSubscriptionString($user->subid);
    }

    private function _listnames(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return "";
        }
        $listSubClass = acymailing_get('class.listsub');
        $usersubscription = $listSubClass->getSubscription($user->subid);
        if (empty($usersubscription)) {
            $subscribedLists = $this->_getFormListNames();
            if (empty($subscribedLists)) {
                return '';
            }

            return implode(isset($parameter->separator) ? $parameter->separator : ', ', $subscribedLists);
        }
        $lists = array();
        if (!empty($usersubscription)) {
            foreach ($usersubscription as $onesub) {
                if ($onesub->status < 1 || empty($onesub->published)) {
                    continue;
                }
                $lists[] = $onesub->name;
            }
        }

        return implode(isset($parameter->separator) ? $parameter->separator : ', ', $lists);
    }


    private function _getFormListNames()
    {
        $allLists = array_merge(acymailing_getVar('array', 'subscription', '', ''), explode(',', acymailing_getVar('string', 'hiddenlists', '', '')));
        $data = acymailing_getVar('array', 'data', '', '');
        if (!empty($data['listsub'])) {
            foreach ($data['listsub'] as $i => $oneList) {
                if ($oneList['status'] != 1) {
                    unset($data['listsub'][$i]);
                }
            }
            $allLists = array_merge($allLists, array_keys($data['listsub']));
        }
        if (empty($allLists)) {
            return array();
        }

        acymailing_arrayToInteger($allLists);
        foreach ($allLists as $i => $oneList) {
            if (empty($oneList)) {
                unset($allLists[$i]);
            }
        }
        if (empty($allLists)) {
            return array();
        }

        return acymailing_loadResultArray('SELECT name FROM #__acymailing_list WHERE listid IN ('.implode(',', $allLists).')');
    }

    private function _listowner(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return "";
        }

        if (!isset($this->listsowner[$listid])) {
            $this->listsowner[$listid] = acymailing_loadObject('SELECT u.* FROM #__acymailing_list as list JOIN #__users as u ON u.id = list.userid WHERE list.listid = '.intval($listid));
        }

        if (!in_array($parameter->field, array('username', 'name', 'email'))) {
            return 'Field not found : '.$parameter->field;
        }

        return @$this->listsowner[$listid]->{$parameter->field};
    }

    private function _loadlist($listid)
    {
        if (isset($this->listsinfo[$listid])) {
            return;
        }

        $this->listsinfo[$listid] = acymailing_loadObject('SELECT * FROM #__acymailing_list WHERE listid = '.intval($listid));
    }

    private function _listname(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return "No list => no name!";
        }

        $this->_loadlist($listid);

        return @$this->listsinfo[$listid]->name;
    }

    private function _listdescription(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return "No list => no description!";
        }

        $this->_loadlist($listid);

        return @$this->listsinfo[$listid]->description;
    }

    private function _listid(&$email, &$user, &$parameter)
    {
        if (empty($user->subid)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return "No list => no ID!";
        }

        return $listid;
    }

    private function loadlistmembers(&$email, &$user)
    {
        if (empty($user->subid)) {
            return '';
        }
        $listid = $this->_getattachedlistid($email, $user->subid);
        if (empty($listid)) {
            return array();
        }

        return acymailing_loadObjectList('SELECT s.email, s.name FROM #__acymailing_listsub AS l JOIN #__acymailing_subscriber AS s ON s.subid=l.subid WHERE l.listid='.intval($listid).' AND l.status=1 AND s.enabled=1 AND s.accept=1');
    }

    private function _replacesubscriptiontags(&$email)
    {
        $match = '#(?:{|%7B)(modify[^}]*|confirm[^}]*|unsubscribe(?:\|[^}]*)?|subscribe[^}]*)(?:}|%7D)(.*)(?:{|%7B)/(modify|confirm|unsubscribe|subscribe)(?:}|%7D)#Uis';
        $variables = array('subject', 'body', 'altbody');
        $found = false;
        $results = array();
        foreach ($variables as $var) {
            if (empty($email->$var)) {
                continue;
            }
            $found = preg_match_all($match, $email->$var, $results[$var]) || $found;
            if (empty($results[$var][0])) {
                unset($results[$var]);
            }
        }

        if (!$found) {
            return;
        }

        $tags = array();
        $this->listunsubscribe = false;
        foreach ($results as $var => $allresults) {
            foreach ($allresults[0] as $i => $oneTag) {
                if (isset($tags[$oneTag])) {
                    continue;
                }
                $tags[$oneTag] = $this->replaceSubscriptionTag($allresults, $i, $email);
            }
        }

        foreach (array_keys($results) as $var) {
            $email->$var = str_replace(array_keys($tags), $tags, $email->$var);
        }
    }

    function replaceSubscriptionTag(&$allresults, $i, &$email)
    {
        $config = acymailing_config();
        $lang = empty($email->language) ? '' : '&lang='.$email->language;

        $parameters = $this->acypluginsHelper->extractTag($allresults[1][$i]);
        $itemId = $this->params->get(strtolower($parameters->id).'itemid', $config->get('itemid', 0));
        $itemId = empty($parameters->itemid) ? $itemId : intval($parameters->itemid);
        $item = empty($itemId) ? '' : '&Itemid='.$itemId;

        if ($parameters->id == 'confirm') { //confirm your subscription link
            $myLink = acymailing_frontendLink('index.php?subid={subtag:subid}&option=com_acymailing&ctrl=user&task=confirm&key={subtag:key|urlencode}'.$item.$lang, true, (bool)$this->params->get('confirmtemplate', false));
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a target="_blank" href="'.$myLink.'">'.$allresults[2][$i].'</a>';
        } elseif ($parameters->id == 'modify') { //modify your subscription link
            $myLink = acymailing_frontendLink('index.php?subid={subtag:subid}&option=com_acymailing&ctrl=user&task=modify&key={subtag:key|urlencode}'.$item.$lang, true, (bool)$this->params->get('modifytemplate', false));
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_modify">'.$allresults[2][$i].'</span></a>';
        } elseif ($parameters->id == 'subscribe') { //add a direct subscription link
            if (empty($parameters->lists)) {
                return 'You must select at least one list';
            }
            $lists = explode(',', $parameters->lists);
            acymailing_arrayToInteger($lists);
            $captchaKey = $config->get('captcha_enabled') ? '&seckey='.$config->get('security_key', '') : '';
            $myLink = acymailing_frontendLink('index.php?option=com_acymailing&ctrl=sub&task=optin&hiddenlists='.implode(',', $lists).'&user[email]={subtag:email|urlencode}'.$item.$lang.$captchaKey);
            if (empty($allresults[2][$i])) {
                return $myLink;
            }

            return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_sub">'.$allresults[2][$i].'</span></a>';
        }//unsubscribe link
        $myLink = acymailing_frontendLink('index.php?subid={subtag:subid}&option=com_acymailing&ctrl=user&task=out&mailid='.$email->mailid.'&key={subtag:key|urlencode}'.$item.$lang, true, (bool)$this->params->get('unsubscribetemplate', false));

        $this->unsubscribeLink = true;
        $this->unsubscribeItem = $item;

        if (empty($allresults[2][$i])) {
            return $myLink;
        }

        return '<a style="text-decoration:none;" target="_blank" href="'.$myLink.'"><span class="acymailing_unsub">'.$allresults[2][$i].'</span></a>';
    }
}//endclass
