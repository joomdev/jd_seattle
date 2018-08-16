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

class plgAcymailingStats extends JPlugin
{
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        if (!isset($this->params)) {
            $plugin = JPluginHelper::getPlugin('acymailing', 'stats');
            $this->params = new acyParameter($plugin->params);
        }
        $this->acypluginsHelper = acymailing_get('helper.acyplugins');
    }

    function acymailing_replacetags(&$email, $send = true)
    {
        $this->statPicture($email, $send);
        if (acymailing_level(3)) {
            $this->_replaceAutoCharts($email);
            $this->_replaceOneCharts($email);
        }
    }

    function statPicture(&$email, $send = true)
    {
        if (!empty($email->altbody)) {
            $email->altbody = str_replace(array('{statpicture}', '{nostatpicture}'), '', $email->altbody);
        }
        if (((isset($email->sendHTML) && !$email->sendHTML) || (isset($email->html) && !$email->html)) || empty($email->type) || !in_array($email->type, array('news', 'autonews', 'followup', 'welcome', 'unsub', 'joomlanotification', 'action')) || strpos($email->body, '{nostatpicture}')) {
            $email->body = str_replace(array('{statpicture}', '{nostatpicture}'), '', $email->body);

            return;
        }

        if (!$send) {
            $pictureLink = ACYMAILING_LIVE.$this->params->get('picture', 'media/com_acymailing/images/statpicture.png');
        } else {
            $config = acymailing_config();
            $itemId = $config->get('itemid', 0);
            $item = empty($itemId) ? '' : '&Itemid='.$itemId;
            $pictureLink = acymailing_frontendLink('index.php?option=com_acymailing&ctrl=statistics&mailid='.$email->mailid.'&subid={subtag:subid}'.$item, false);
        }

        $widthsize = $this->params->get('width', 50);
        $heightsize = $this->params->get('height', 1);
        $width = empty($widthsize) ? '' : ' width="'.$widthsize.'" ';
        $height = empty($heightsize) ? '' : ' height="'.$heightsize.'" ';

        $statPicture = '<img class="spict" alt="'.$this->params->get('alttext', '').'" src="'.$pictureLink.'"  border="0" '.$height.$width.'/>';

        if (strpos($email->body, '{statpicture}')) {
            $email->body = str_replace('{statpicture}', $statPicture, $email->body);
        } elseif (strpos($email->body, '</body>')) {
            $email->body = str_replace('</body>', $statPicture.'</body>', $email->body);
        } else {
            $email->body .= $statPicture;
        }
    }//endfct

    function acymailing_getstatpicture()
    {
        return $this->params->get('picture', 'media/com_acymailing/images/statpicture.png');
    }

    function onAcyDisplayTriggers(&$triggers)
    {
        $triggers['opennews'] = acymailing_translation('ON_OPEN_NEWS');
    }

    function onAcyDisplayFilters(&$type, $context = "massactions")
    {

        if ($context != "massactions" AND !$this->params->get('displayfilter_'.$context, false)) {
            return;
        }

        $type['deliverstat'] = acymailing_translation('STATISTICS');

        $allemails = acymailing_loadObjectList("SELECT `mailid`,CONCAT(`subject`,' [',".acymailing_escapeDB(acymailing_translation('ACY_ID').' ').", CAST(`mailid` AS char),']') as 'value' FROM `#__acymailing_mail` WHERE `type` IN('news','welcome','unsub','followup','notification','joomlanotification') ORDER BY `senddate` DESC LIMIT 5000");
        $element = new stdClass();
        $element->mailid = 0;
        $element->value = acymailing_translation('EMAIL_NAME');
        array_unshift($allemails, $element);

        $actions = array();
        $actions[] = acymailing_selectOption('open', acymailing_translation('OPEN'));
        $actions[] = acymailing_selectOption('notopen', acymailing_translation('NOT_OPEN'));
        $actions[] = acymailing_selectOption('failed', acymailing_translation('FAILED'));
        if (acymailing_level(3)) {
            $actions[] = acymailing_selectOption('bounce', acymailing_translation('BOUNCES'));
        }
        $actions[] = acymailing_selectOption('htmlsent', acymailing_translation('SENT_HTML'));
        $actions[] = acymailing_selectOption('textsent', acymailing_translation('SENT_TEXT'));
        $actions[] = acymailing_selectOption('notsent', acymailing_translation('NOT_SENT'));

        $return = '<div id="filter__num__deliverstat">'.acymailing_select($actions, "filter[__num__][deliverstat][action]", 'class="inputbox" onchange="countresults(__num__);" size="1"', 'value', 'text');
        $return .= ' '.acymailing_select($allemails, "filter[__num__][deliverstat][mailid]", 'onchange="countresults(__num__)" class="inputbox" size="1" style="max-width:200px"', 'mailid', 'value').'</div>';

        return $return;
    }

    function onAcyProcessFilterCount_deliverstat(&$query, $filter, $num)
    {
        $this->onAcyProcessFilter_deliverstat($query, $filter, $num);

        return acymailing_translation_sprintf('SELECTED_USERS', $query->count());
    }

    function onAcyDisplayFilter_deliverstat($filter)
    {
        $statTxt = array(
            'open' => acymailing_translation('OPEN'),
            'notopen' => acymailing_translation('NOT_OPEN'),
            'failed' => acymailing_translation('FAILED'),
            'bounce' => acymailing_translation('BOUNCES'),
            'htmlsent' => acymailing_translation('SENT_HTML'),
            'textsent' => acymailing_translation('SENT_TEXT'),
            'notsent' => acymailing_translation('NOT_SENT')
        );

        if (empty($filter['mailid'])) {
            $mailSubject = acymailing_translation('EMAIL_NAME');
        } else {
            $mailClass = acymailing_get('class.mail');
            $mail = $mailClass->get($filter['mailid']);
            $mailSubject = $mail->subject.' [ID '.$filter['mailid'].' ]';
        }

        return acymailing_translation('STATISTICS').': '.$statTxt[$filter['action']].' '.$mailSubject;
    }

    function onAcyProcessFilter_deliverstat(&$query, $filter, $num)
    {

        $alias = 'stats'.$num;
        $jl = '#__acymailing_userstats AS '.$alias.' ON '.$alias.'.subid = sub.subid';
        if (!empty($filter['mailid'])) {
            $jl .= ' AND '.$alias.'.mailid = '.intval($filter['mailid']);
        }

        $query->leftjoin[$alias] = $jl;

        if ($filter['action'] == 'open') {
            $where = $alias.'.open > 0';
        } elseif ($filter['action'] == 'notopen') {
            if (empty($filter['mailid'])) {
                unset($query->leftjoin[$alias]);
                $usersNeverOpened = acymailing_loadResultArray('SELECT subid FROM #__acymailing_userstats GROUP BY subid HAVING MAX(open) = 0');
                if (empty($usersNeverOpened)) {
                    $usersNeverOpened = array(0);
                }
                $where = 'sub.subid IN ('.implode(',', $usersNeverOpened).')';
            } else {
                $where = $alias.'.open = 0';
            }
        } elseif ($filter['action'] == 'failed') {
            $where = $alias.'.fail = 1';
        } elseif ($filter['action'] == 'bounce') {
            $where = $alias.'.bounce = 1';
        } elseif ($filter['action'] == 'htmlsent') {
            $where = $alias.'.html = 1';
        } elseif ($filter['action'] == 'textsent') {
            $where = $alias.'.html = 0';
        } elseif ($filter['action'] == 'notsent') {
            $where = $alias.'.subid IS NULL';
        }

        $query->where[] = $where;
    }

    function acymailing_getPluginType()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acymailing_translation('STATISTICS');
        $onePlugin->function = 'acymailing_stats_show';
        $onePlugin->help = 'plugin-stats';

        return $onePlugin;
    }

    function acymailing_stats_show()
    {
        if (!function_exists('imageCreateTrueColor')) {
            acymailing_display('The "php_gd2" extension is not enabled on your server, you will have to ask your host to enable it');

            return;
        }

        $pageInfo = new stdClass();
        $pageInfo->filter = new stdClass();
        $pageInfo->filter->order = new stdClass();
        $pageInfo->limit = new stdClass();
        $pageInfo->elements = new stdClass();

        $paramBase = ACYMAILING_COMPONENT.'.stats';

        $pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.mailid', 'cmd');
        $pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
        if (strtolower($pageInfo->filter->order->dir) !== 'desc') {
            $pageInfo->filter->order->dir = 'asc';
        }
        $pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
        $pageInfo->search = strtolower(trim($pageInfo->search));
        $pageInfo->filter_list = acymailing_getUserVar($paramBase.".filter_list", 'filter_list', '', 'int');
        $pageInfo->filter_type = acymailing_getUserVar($paramBase.".filter_type", 'filter_type', '', 'int');
        $pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
        $pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');
        $pageInfo->contentfilter = acymailing_getUserVar($paramBase.".contentfilter", 'contentfilter', 'new', 'string');
        $pageInfo->contentorder = acymailing_getUserVar($paramBase.".contentorder", 'contentorder', 'id', 'string');
        $pageInfo->contentorderdir = acymailing_getUserVar($paramBase.".contentorderdir", 'contentorderdir', 'DESC', 'string');
        $pageInfo->cols = acymailing_getUserVar($paramBase.".cols", 'cols', '1', 'string');

        $query = 'SELECT SQL_CALC_FOUND_ROWS a.*, GROUP_CONCAT(c.name SEPARATOR ", ") AS listname, u.name AS username
					FROM '.acymailing_table('mail').' AS a 
					JOIN '.acymailing_table('listmail').' AS b ON a.mailid = b.mailid 
					JOIN '.acymailing_table('list').' AS c ON b.listid = c.listid 
					JOIN '.acymailing_table('users', false).' AS u ON a.userid = u.id ';

        $searchFields = array('a.mailid', 'a.subject', 'u.name');

        if (!empty($pageInfo->search)) {
            $searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
            $filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE ".$searchVal;
        }
        $filters[] = 'a.type = "news"';
        $filters[] = 'a.senddate IS NOT NULL';

        if (!acymailing_isAdmin()) {
            $listClass = acymailing_get('class.list');
            $this->lists = $listClass->getFrontendLists();
            if (empty($this->lists)) {
                return;
            }

            $this->listids = array();
            foreach ($this->lists as $oneList) {
                $this->listids[] = $oneList->listid;
            }
            $filters[] = 'c.listid IN ('.implode(',', $this->listids).')';
        }

        if (!empty($pageInfo->filter_list)) {
            $filters[] = 'b.listid = '.intval($pageInfo->filter_list);
        }
        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        $query .= ' GROUP BY a.mailid ';

        if (!empty($pageInfo->filter->order->value)) {
            $query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
        }

        $rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

        if (!empty($pageInfo->search)) {
            $rows = acymailing_search($pageInfo->search, $rows);
        }

        $pageInfo->elements->total = acymailing_loadResult('SELECT FOUND_ROWS()');
        $pageInfo->elements->page = count($rows);

        $pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

        $type = acymailing_getVar('string', 'type');

        $listFilter = $this->_categories($pageInfo->filter_list);
        ?>
        <script language="javascript" type="text/javascript">
            <!--
            var selectedContents = [];
            function applyContent(contentid, rowClass){
                var tmp = selectedContents.indexOf(contentid);
                if(tmp != -1){
                    window.document.getElementById('content' + contentid).className = rowClass;
                    delete selectedContents[tmp];
                }else{
                    window.document.getElementById('content' + contentid).className = 'selectedrow';
                    selectedContents.push(contentid);
                }
                updateTag();
            }

            function updateTag(){
                var tag = '';
                var otherinfo = '';
                var tmp = 0;

                <?php
                ?>
                for(var i = 0; i < document.adminForm.cbdisplay.length; i++){
                    if(!document.adminForm.cbdisplay[i].checked) continue;
                    if(tmp == 0){
                        tmp += 1;
                        otherinfo += "| display:" + document.adminForm.cbdisplay[i].value;
                    }else{
                        otherinfo += ", " + document.adminForm.cbdisplay[i].value;
                    }
                }

                for(var key in selectedContents){
                    if(selectedContents.hasOwnProperty(key) && selectedContents[key] && !isNaN(key)){
                        tag = tag + '{acystats:' + selectedContents[key] + otherinfo + '}<br />';
                    }
                }
                setTag(tag);
            }

            var selectedCat = [];
            function applyAuto(catid, rowClass){
                if(catid == 'all'){
                    <?php
                    $listids = array();
                    foreach ($this->catvalues as $oneCat) {
                        if (empty($oneCat->value)) {
                            continue;
                        }
                        $listids[] = $oneCat->value;
                    }
                    echo 'var listids = ['.implode(',', $listids).'];';
                    ?>

                    if(window.document.getElementById('catall').className == 'selectedrow'){
                        window.document.getElementById('catall').className = "row0";
                        rowClass = "row0";
                    }else{
                        window.document.getElementById('catall').className = "selectedrow";
                        rowClass = "selectedrow";
                    }

                    listids.forEach(function(listid){
                        window.document.getElementById('cat' + listid).className = rowClass;
                        if(rowClass == "row0"){
                            delete selectedCat[listid];
                        }else{
                            selectedCat[listid] = 'selectedone';
                        }
                    });
                }else{
                    window.document.getElementById('catall').className = 'row0';
                    if(selectedCat[catid]){
                        window.document.getElementById('cat' + catid).className = rowClass;
                        delete selectedCat[catid];
                    }else{
                        window.document.getElementById('cat' + catid).className = 'selectedrow';
                        selectedCat[catid] = 'selectedone';
                    }
                }
                updateTagAuto();
            }

            function updateTagAuto(){
                var otherinfo = '';
                var tmp = 0;

                var tagselect = document.adminForm.tagselect;
                for(i = 0; i < tagselect.length; i++){
                    if(tagselect[i].value == '') continue;
                    if(tmp == 0){
                        tmp += 1;
                        otherinfo += "| tags:" + tagselect[i].value;
                    }else{
                        otherinfo += "," + tagselect[i].value;
                    }
                }
                tmp = 0;

                for(var i = 0; i < document.adminForm.cbdisplayauto.length; i++){
                    if(!document.adminForm.cbdisplayauto[i].checked) continue;
                    if(tmp == 0){
                        tmp += 1;
                        otherinfo += "| display:" + document.adminForm.cbdisplayauto[i].value;
                    }else{
                        otherinfo += ", " + document.adminForm.cbdisplayauto[i].value;
                    }
                }

                if(document.adminForm.max_article.value){
                    otherinfo += "| max:" + document.adminForm.max_article.value;
                }

                if(document.adminForm.contentorder.value){
                    otherinfo += "| order:" + document.adminForm.contentorder.value + "," + document.adminForm.contentorderdir.value;
                }

                <?php if($type == 'autonews'){ ?>
                if(document.adminForm.min_article.value){
                    otherinfo += "| min:" + document.adminForm.min_article.value;
                }

                if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){
                    otherinfo += "| filter:" + document.adminForm.contentfilter.value;
                }

                if(document.adminForm.delay && document.adminForm.delay.value){
                    otherinfo += "| delay:" + document.adminForm.delay.value;
                }
                <?php } ?>
                var tag = '{autoacystats:';

                var lists = '';
                for(var icat in selectedCat){
                    if(selectedCat.hasOwnProperty(icat) && selectedCat[icat] == 'selectedone'){
                        lists += icat + '-';
                    }
                }

                if(lists.length == 0){
                    setTag('');
                    return;
                }

                tag += lists + otherinfo + '}<br />';

                setTag(tag);
            }
            //-->
        </script>
        <?php
        $fieldsDisplay = array();
        $fieldsDisplay[] = array('title' => 'sent', 'label' => 'ACY_SENT', 'checked' => '');
        $fieldsDisplay[] = array('title' => 'status', 'label' => 'STATUS', 'checked' => 'yes');
        $fieldsDisplay[] = array('title' => 'devices', 'label' => 'ACY_STAT_MOBILE_USAGE', 'checked' => 'yes');
        $fieldsDisplay[] = array('title' => 'browsers', 'label' => 'ACY_STAT_BROWSER', 'checked' => '');
        $fieldsDisplay[] = array('title' => 'clicks', 'label' => 'CLICKED_LINK', 'checked' => 'yes');

        $tabs = acymailing_get('helper.acytabs');
        echo $tabs->startPane('stats_tab');
        echo $tabs->startPanel(acymailing_translation('NEWSLETTERS'), 'stats_listings');
        ?>
        <br style="font-size:1px"/>
        <div class="onelineblockoptions">
            <table width="100%" class="acymailing_table">
                <tr>
                    <td nowrap="nowrap"><?php echo acymailing_translation('ACY_GRAPHS'); ?></td>
                    <?php
                    $i = 1;
                    foreach ($fieldsDisplay as $oneField) {
                        if ($i == 4) {
                            echo '</tr><tr><td/>';
                            $i = 1;
                        }
                        echo '<td nowrap="nowrap"><input type="checkbox" name="cbdisplay" value="'.$oneField['title'].'" id="'.$oneField['title'].'" '.(($oneField['checked'] == 'yes') ? 'checked' : '').' onclick="updateTag();"/><label style="margin-left:5px" for="'.$oneField['title'].'">'.trim(acymailing_translation($oneField['label']), ':').'</label></td>';
                        $i++;
                    }
                    while ($i != 4) {
                        echo '<td/>';
                        $i++;
                    }
                    ?>
                </tr>
            </table>
        </div>
        <div class="onelineblockoptions">
            <table class="acymailing_table_options">
                <tr>
                    <td nowrap="nowrap" width="100%">
                        <input placeholder="<?php echo acymailing_translation('ACY_SEARCH'); ?>" type="text" name="search" id="acymailingsearch" value="<?php echo $pageInfo->search; ?>" class="text_area" onchange="document.adminForm.submit();"/>
                        <button class="acymailing_button" onclick="this.form.submit();"><?php echo acymailing_translation('JOOMEXT_GO'); ?></button>
                        <button class="acymailing_button" onclick="document.getElementById('acymailingsearch').value='';this.form.submit();"><?php echo acymailing_translation('JOOMEXT_RESET'); ?></button>
                    </td>
                    <td nowrap="nowrap">
                        <?php echo $listFilter; ?>
                    </td>
                </tr>
            </table>
            <table class="acymailing_table" cellpadding="1" width="100%">
                <thead>
                <tr>
                    <th></th>
                    <th class="title">
                        <?php echo acymailing_gridSort(acymailing_translation('JOOMEXT_SUBJECT'), 'a.subject', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
                    </th>
                    <th class="title">
                        <?php echo acymailing_gridSort(acymailing_translation('ACY_AUTHOR'), 'u.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
                    </th>
                    <th class="title">
                        <?php echo acymailing_gridSort(acymailing_translation('LISTS'), 'b.listid', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
                    </th>
                    <th class="title titleid">
                        <?php echo acymailing_gridSort(acymailing_translation('ACY_ID'), 'a.mailid', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
                    </th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <td colspan="5">
                        <?php
                        echo $pagination->getListFooter();
                        if (ACYMAILING_J30) {
                            $paginationNb = array();
                            foreach (array(5, 10, 15, 20, 25, 30, 50, 100) as $oneOption) {
                                $paginationNb[] = acymailing_selectOption($oneOption, $oneOption);
                            }
                            $paginationNb[] = acymailing_selectOption(0, acymailing_translation('ACY_ALL'));
                            echo 'Display #'.acymailing_select($paginationNb, 'limit', 'size="1" style="width:60px" onchange="acymailing.submitform();"', 'value', 'text', $pageInfo->limit->value).'<br />';
                        }
                        echo $pagination->getResultsCounter();
                        ?>
                    </td>
                </tr>
                </tfoot>
                <tbody>
                <?php
                $k = 0;
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $row->subject = acyEmoji::Decode($row->subject);
                        ?>
                        <tr id="content<?php echo $row->mailid; ?>" class="<?php echo "row$k"; ?>" onclick="applyContent(<?php echo $row->mailid.",'row$k'" ?>);" style="cursor:pointer;">
                            <td class="acytdcheckbox"></td>
                            <td style="text-align:center;">
                                <?php echo strlen($row->subject) > 200 ? substr($row->subject, 0, 200).'...' : $row->subject; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php echo $row->username; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php echo strlen($row->listname) > 150 ? substr($row->listname, 0, 150).'...' : $row->listname; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php echo $row->mailid; ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>

        <?php
        echo $tabs->endPanel();
        echo $tabs->startPanel(acymailing_translation('ACY_PER_LIST'), 'stats_auto');
        ?>

        <br style="font-size:1px"/>
        <div class="onelineblockoptions">
            <table width="100%" class="acymailing_table">
                <tr>
                    <?php
                    ?>
                    <td nowrap="nowrap"><?php echo acymailing_translation('ACY_GRAPHS'); ?></td>
                    <?php
                    $i = 1;
                    foreach ($fieldsDisplay as $oneField) {
                        if ($i == 4) {
                            echo '</tr><tr><td/>';
                            $i = 1;
                        }
                        echo '<td nowrap="nowrap"><input type="checkbox" name="cbdisplayauto" value="'.$oneField['title'].'" id="'.$oneField['title'].'auto" '.(($oneField['checked'] == 'yes') ? 'checked' : '').' onclick="updateTagAuto();"/><label style="margin-left:5px" for="'.$oneField['title'].'auto">'.trim(acymailing_translation($oneField['label']), ':').'</label></td>';
                        $i++;
                    }
                    while ($i != 4) {
                        echo '<td/>';
                        $i++;
                    }
                    ?>
                </tr>
                <tr>
                    <td nowrap="nowrap">
                        <label for="max_article"><?php echo acymailing_translation('TAGS'); ?></label>
                    </td>
                    <td nowrap="nowrap" colspan="3">
                        <?php $tagfieldtype = acymailing_get('type.tagfield');
                        $tagfieldtype->onclick = 'updateTagAuto();';
                        echo $tagfieldtype->display('tagselect', 'tags'); ?>
                    </td>
                </tr>
                <tr>
                    <td nowrap="nowrap">
                        <label for="max_article"><?php echo acymailing_translation('ACY_MAX_NEWSLETTERS'); ?></label>
                    </td>
                    <td nowrap="nowrap">
                        <input type="text" id="max_article" name="max_article" style="width:50px" value="20" onchange="updateTagAuto();"/>
                    </td>
                    <td nowrap="nowrap">
                        <?php echo acymailing_translation('ACY_ORDER'); ?>
                    </td>
                    <td nowrap="nowrap">
                        <?php
                        $values = array('mailid' => 'ACY_ID', 'senddate' => 'SEND_DATE', 'subject' => 'JOOMEXT_SUBJECT');
                        echo $this->acypluginsHelper->getOrderingField($values, $pageInfo->contentorder, $pageInfo->contentorderdir);
                        ?>
                    </td>
                </tr>
                <?php if ($type == 'autonews') { ?>
                    <tr>
                        <td nowrap="nowrap">
                            <label for="min_article"><?php echo acymailing_translation('ACY_MIN_NEWSLETTERS'); ?></label>
                        </td nowrap="nowrap">
                        <td nowrap="nowrap">
                            <input type="text" id="min_article" name="min_article" style="width:50px" value="1" onchange="updateTagAuto();"/>
                        </td>
                        <td nowrap="nowrap">
                            <?php echo acymailing_translation('JOOMEXT_FILTER'); ?>
                        </td>
                        <td nowrap="nowrap">
                            <?php
                            $choice = array();
                            $choice[] = acymailing_selectOption("", acymailing_translation('ACY_ALL'));
                            $choice[] = acymailing_selectOption("new", acymailing_translation('ACY_NEWLY_SENT'));

                            echo acymailing_select($choice, 'contentfilter', 'size="1" onchange="updateTagAuto();" style="max-width:200px;"', 'value', 'text', $pageInfo->contentfilter);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td nowrap="nowrap" colspan="4">
                            <?php echo acymailing_translation_sprintf('ACY_SENT_MORE_THAN', '<input type="text" name="delay" style="width:30px" value="3" onchange="updateTagAuto();"/>'); ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
        <div class="onelineblockoptions">
            <table class="acymailing_table" cellpadding="1" width="100%">
                <tr id="catall" class="<?php echo "row0"; ?>" onclick="applyAuto('all','<?php echo "row0" ?>');" style="cursor:pointer;">
                    <td class="acytdcheckbox"></td>
                    <td><?php echo acymailing_translation('ACY_ALL'); ?></td>
                </tr>
                <?php
                if (!empty($this->catvalues)) {
                    foreach ($this->catvalues as $oneCat) {
                        if (empty($oneCat->value)) {
                            continue;
                        }
                        ?>
                        <tr id="cat<?php echo $oneCat->value ?>" class="<?php echo "row0"; ?>" onclick="applyAuto(<?php echo $oneCat->value ?>,'<?php echo "row0" ?>');" style="cursor:pointer;">
                            <td class="acytdcheckbox"></td>
                            <td>
                                <?php
                                echo $oneCat->text;
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
        echo $tabs->endPanel();
        echo $tabs->endPane();
    }

    private function _categories($filter_list)
    {
        if (empty($this->lists)) {
            $listClass = acymailing_get('class.list');
            $rows = $listClass->getLists();
        } else {
            $rows = $this->lists;
        }

        $this->catvalues = array();
        $this->catvalues[] = acymailing_selectOption(0, acymailing_translation('ALL_LISTS'));

        foreach ($rows as $oneList) {
            $this->catvalues[] = acymailing_selectOption($oneList->listid, $oneList->name);
        }

        return acymailing_select($this->catvalues, 'filter_list', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', intval($filter_list));
    }

    private function _replaceAutoCharts(&$email)
    {
        $this->acymailing_generateautonews($email);
        if (empty($this->tags)) {
            return;
        }
        $this->acypluginsHelper->replaceTags($email, $this->tags, true);
    }

    function acymailing_generateautonews(&$email)
    {
        $tags = $this->acypluginsHelper->extractTags($email, 'autoacystats');
        $return = new stdClass();
        $return->status = true;
        $return->message = '';

        if (empty($tags)) {
            return $return;
        }

        foreach ($tags as $oneTag => $parameter) {
            if (isset($this->tags[$oneTag])) {
                continue;
            }
            if (empty($parameter->id)) {
                $this->tags[$oneTag] = '';
                continue;
            }

            $allcats = explode('-', $parameter->id);
            $selectedArea = array();
            foreach ($allcats as $oneCat) {
                if (empty($oneCat)) {
                    continue;
                }
                $selectedArea[] = intval($oneCat);
            }

            $query = 'SELECT DISTINCT a.mailid FROM '.acymailing_table('mail').' AS a ';

            $where = array();
            $where[] = 'a.type = "news"';
            $where[] = 'a.senddate IS NOT NULL';

            if (!empty($selectedArea)) {
                $query .= 'JOIN '.acymailing_table('listmail').' AS b ON a.mailid = b.mailid ';
                $where[] = 'b.listid IN ('.implode(',', $selectedArea).')';
            }

            if (!empty($parameter->tags)) {
                $selectedtags = explode(',', $parameter->tags);
                acymailing_arrayToInteger($selectedtags);

                $query .= 'JOIN '.acymailing_table('tagmail').' AS tm ON a.mailid = tm.mailid ';
                $where[] = 'tm.tagid IN ('.implode(',', $selectedtags).')';
            }

            if (!empty($parameter->filter) && !empty($email->params['lastgenerateddate'])) {
                $where[] = 'a.senddate > \''.intval($email->params['lastgenerateddate']).'\'';
            }

            if (!empty($parameter->delay)) {
                $where[] = 'a.senddate < \''.intval(time() - $parameter->delay * 86400).'\'';
            }

            if (!empty($where)) {
                $query .= ' WHERE ('.implode(') AND (', $where).')';
            }

            if (!empty($parameter->order)) {
                $ordering = explode(',', $parameter->order);
                if ($ordering[0] == 'rand') {
                    $query .= ' ORDER BY rand()';
                } else {
                    $query .= ' ORDER BY a.'.acymailing_secureField(trim($ordering[0])).' '.acymailing_secureField(trim($ordering[1]));
                }
            }

            $start = '';
            if (!empty($parameter->start)) {
                $start = intval($parameter->start).',';
            }
            if (empty($parameter->max)) {
                $parameter->max = 20;
            }
            $query .= ' LIMIT '.$start.intval($parameter->max);


            $allArticles = acymailing_loadResultArray($query);

            if (!empty($parameter->min) && count($allArticles) < $parameter->min) {
                $return->status = false;
                $return->message = 'Not enough statistics for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
            }

            $stringTag = '';
            if (!empty($allArticles)) {
                if (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'autoacystats.php')) {
                    ob_start();
                    require(ACYMAILING_MEDIA.'plugins'.DS.'autoacystats.php');
                    $stringTag = ob_get_clean();
                } else {
                    $arrayElements = array();
                    unset($parameter->id);
                    $numArticle = 1;
                    foreach ($allArticles as $oneArticleId) {
                        $numArticle++;
                        $args = array();
                        $args[] = 'acystats:'.$oneArticleId;
                        foreach ($parameter as $oneParam => $val) {
                            $args[] = $oneParam.':'.$val;
                        }
                        $arrayElements[] = '{'.implode('|', $args).'}';
                    }
                    $stringTag = $this->acypluginsHelper->getFormattedResult($arrayElements, $parameter);
                }
            }
            $this->tags[$oneTag] = $stringTag;
        }

        return $return;
    }

    private function _replaceOneCharts(&$email)
    {
        $tags = $this->acypluginsHelper->extractTags($email, 'acystats');
        if (empty($tags)) {
            return;
        }

        require_once(ACYMAILING_INC.'phpImg'.DS.'library.php');


        $tagsReplaced = array();
        foreach ($tags as $i => $oneTag) {
            if (isset($tagsReplaced[$i])) {
                continue;
            }
            $tagsReplaced[$i] = $this->_chartImages($oneTag);
        }

        $this->acypluginsHelper->replaceTags($email, $tagsReplaced, true);
    }

    function _chartImages($tag)
    {
        if (empty($tag->display)) {
            return '';
        }

        $tag->display = explode(',', $tag->display);
        foreach ($tag->display as $i => $oneDisplay) {
            $oneDisplay = trim($oneDisplay);
            $tag->$oneDisplay = true;
        }

        $newsStats = acymailing_loadObject('SELECT s.*, m.subject FROM '.acymailing_table('stats').' AS s JOIN '.acymailing_table('mail').' AS m ON s.mailid = m.mailid WHERE m.mailid = '.intval($tag->id));

        if (empty($newsStats)) {
            if (acymailing_isAdmin()) {
                acymailing_enqueueMessage('There are no statistics for the newsletter n°'.$tag->id, 'notice');
            }

            return '';
        }

        $colors = array(
            array(154, 195, 248, 1), //blue
            array(200, 230, 134, 1), // green
            array(230, 110, 101, 1), // red
            array(174, 148, 210, 1), // purple
            array(249, 167, 87, 1), // orange
            array(46, 187, 180, 1), // green
            array(255, 133, 203, 1), // pink
            array(255, 208, 65, 1), // yellow
            array(99, 140, 166, 1) // blue-grey
        );

        $varFields = array();
        foreach ($newsStats as $fieldName => $oneField) {
            $varFields['{'.$fieldName.'}'] = $oneField;
        }

        $result = '';
        $charts = array();
        $day = date('Y_m_d');

        if (!empty($tag->sent)) {
            $oneChart = new stdClass();
            $oneChart->title = acymailing_translation('ACY_SENT');

            $values = array('SENT_HTML' => $newsStats->senthtml, 'SENT_TEXT' => $newsStats->senttext);
            asort($values);
            $values = array_reverse($values);

            $filename = intval($tag->id).'_sent_'.$day.'.png';
            if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
            } else {
                $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                if ($created) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                }
            }

            $oneChart->legend = $values;
            if (!empty($oneChart->chart)) {
                $charts[] = $oneChart;
            }
        }

        if (!empty($tag->status)) {
            $oneChart = new stdClass();
            $oneChart->title = acymailing_translation('STATUS');

            $values = array(
                'OPEN_UNIQUE' => $newsStats->openunique - $newsStats->unsub,
                'NOT_OPEN' => $newsStats->senthtml + $newsStats->senttext - $newsStats->openunique - $newsStats->bounceunique - $newsStats->fail,
                'ACTION_BOUNCE' => $newsStats->bounceunique,
                'FAILED' => $newsStats->fail,
                'UNSUBSCRIBED' => $newsStats->unsub,
            );
            asort($values);
            $values = array_reverse($values);

            $filename = intval($tag->id).'_status_'.$day.'.png';
            if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
            } else {
                $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                if ($created) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                }
            }

            $oneChart->legend = $values;
            if (!empty($oneChart->chart)) {
                $charts[] = $oneChart;
            }
        }

        if (!empty($tag->devices)) {
            $ismobilestats = acymailing_loadObjectList('SELECT COUNT(*) as nbMobile, is_mobile FROM '.acymailing_table('userstats').' WHERE is_mobile IS NOT NULL AND mailid = '.intval($tag->id).' GROUP BY is_mobile', 'is_mobile');

            if (!empty($ismobilestats)) {
                $oneChart = new stdClass();
                $oneChart->title = acymailing_translation('ACY_STAT_MOBILE_USAGE');

                $valNoMob = empty($ismobilestats[0]) ? 0 : intval($ismobilestats[0]->nbMobile);
                $valMob = empty($ismobilestats[1]) ? 0 : intval($ismobilestats[1]->nbMobile);

                $values = array(
                    'ACY_STAT_NOMOBILE' => $valNoMob,
                    'ACY_STAT_MOBILE' => $valMob,
                );
                asort($values);
                $values = array_reverse($values);

                $filename = intval($tag->id).'_devices_'.$day.'.png';
                if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                } else {
                    $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                    if ($created) {
                        $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                    }
                }

                $oneChart->legend = $values;
                if (!empty($oneChart->chart)) {
                    $charts[] = $oneChart;
                }
            }


            $mobileosstats = acymailing_loadObjectList('SELECT COUNT(mobile_os) as nbOS, mobile_os FROM '.acymailing_table('userstats').' WHERE mobile_os IS NOT NULL AND mobile_os <> \'\' AND mailid = '.intval($tag->id).' GROUP BY mobile_os ORDER BY nbOS DESC', 'nbOS');

            if (!empty($mobileosstats)) {
                $oneChart = new stdClass();
                $oneChart->title = acymailing_translation('ACY_STAT_MOBILE_USAGE');

                $values = array();
                foreach ($mobileosstats as $oneStat) {
                    $values[$oneStat->mobile_os] = $oneStat->nbOS;
                }
                asort($values);
                $values = array_reverse($values);

                $filename = intval($tag->id).'_mobile_os_'.$day.'.png';
                if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                } else {
                    $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                    if ($created) {
                        $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                    }
                }

                $oneChart->legend = $values;
                if (!empty($oneChart->chart)) {
                    $charts[] = $oneChart;
                }
            }
        }

        if (!empty($tag->browsers)) {
            $browserstats = acymailing_loadObjectList('SELECT COUNT(browser) as nbBrowser, browser FROM '.acymailing_table('userstats').' WHERE browser IS NOT NULL AND mailid = '.intval($tag->id).' GROUP BY browser ORDER BY nbBrowser DESC', 'nbBrowser');

            if (!empty($browserstats)) {
                $oneChart = new stdClass();
                $oneChart->title = acymailing_translation('ACY_STAT_BROWSER');

                $values = array();
                foreach ($browserstats as $oneStat) {
                    $values[$oneStat->browser] = $oneStat->nbBrowser;
                }
                asort($values);
                $values = array_reverse($values);

                $filename = intval($tag->id).'_browsers_'.$day.'.png';
                if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                } else {
                    $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                    if ($created) {
                        $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                    }
                }

                $oneChart->legend = $values;
                if (!empty($oneChart->chart)) {
                    $charts[] = $oneChart;
                }
            }
        }

        if (!empty($tag->clicks)) {
            $clicked = acymailing_loadResult('SELECT COUNT(DISTINCT subid) as nbClick FROM '.acymailing_table('urlclick').' WHERE mailid = '.intval($tag->id));

            $oneChart = new stdClass();
            $oneChart->title = acymailing_translation('CLICK_STATISTICS');

            $values = array('CLICKED_LINK' => $clicked, 'ACY_NOT_CLICK' => $newsStats->senthtml + $newsStats->senttext - $clicked);
            asort($values);
            $values = array_reverse($values);

            $filename = intval($tag->id).'_clicks_'.$day.'.png';
            if (file_exists(ACYMAILING_MEDIA.'statistic_charts'.DS.$filename)) {
                $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
            } else {
                $created = piechartToImage($filename, 200, 200, array_values($values), $colors);
                if ($created) {
                    $oneChart->chart = '<img src="'.ACYMAILING_LIVE.'media/com_acymailing/statistic_charts/'.$filename.'"/>';
                }
            }

            $oneChart->legend = $values;
            if (!empty($oneChart->chart)) {
                $charts[] = $oneChart;
            }
        }

        if (!empty($charts)) {
            $colors = array('9ac3f8', 'c7e586', 'e56e65', 'ae93d1', 'f8a657', '2ebbb4', 'ff85cb', 'ffd041', '638ca6');

            $i = 0;
            $newsStats->subject = acyEmoji::Decode($newsStats->subject);
            $result = '<div class="acymailing_content_stats" style="margin-bottom: 20px;"><h2 class="acymailing_title">'.$newsStats->subject.'</h2><table style="width:100%;"><tr>';

            foreach ($charts as $oneChart) {
                if ($i % 2 == 0 && $i != 0) {
                    $result .= '</tr><tr>';
                }

                $result .= '<td style="text-align: center;" valign="top">';
                $result .= '<div class="acymailing_chart_title" style="text-align: center;">'.$oneChart->title.'</div>';
                $result .= $oneChart->chart;

                $result .= '<table class="chartlegends" style="width:100%;">';
                $j = 0;
                $total = array_sum($oneChart->legend);
                $others = 0;
                $elements = count($oneChart->legend) - 1;
                foreach ($oneChart->legend as $legend => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    if (empty($colors[$j])) {
                        $others += $value;

                        if ($j != $elements) {
                            $j++;
                            continue;
                        }

                        $result .= '<tr><td><div style="display: inline-block;width: 8px;height:8px;border-radius:4px;background-color: #424242;"></div> ';
                        $result .= acymailing_translation('OTHER').' ('.$others.': '.round($others * 100 / $total).'%)</td></tr>';
                        break;
                    }

                    $result .= '<tr><td><div style="display: inline-block;width: 8px;height:8px;border-radius:4px;background-color: #'.$colors[$j].';"></div> ';
                    $result .= acymailing_translation($legend).' ('.$value.': '.round($value * 100 / $total).'%)</td></tr>';

                    $j++;
                }
                $result .= '</table>';

                $result .= '</td>';
                $i++;
            }
            if ($i % 2 != 0) {
                echo '<td/>';
            }

            $result .= '</tr></table></div>';
        }

        if (file_exists(ACYMAILING_MEDIA.'plugins'.DS.'acystats.php')) {
            ob_start();
            require(ACYMAILING_MEDIA.'plugins'.DS.'acystats.php');
            $result = ob_get_clean();
            $result = str_replace(array_keys($varFields), $varFields, $result);
        }

        $result = $this->acypluginsHelper->removeJS($result);

        return $result;
    }
}//endclass
