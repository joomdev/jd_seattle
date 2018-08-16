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

define('ACYMAILING_CMS', 'Joomla!®');
define('ACYMAILING_COMPONENT', 'com_acymailing');
define('ACYMAILING_DEFAULT_LANGUAGE', 'en-GB');

define('ACYMAILING_BASE', rtrim(JPATH_BASE, DS).DS);
define('ACYMAILING_ROOT', rtrim(JPATH_ROOT, DS).DS);
define('ACYMAILING_FRONT', rtrim(JPATH_SITE, DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_BACK', rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_HELPER', ACYMAILING_BACK.'helpers'.DS);
define('ACYMAILING_CLASS', ACYMAILING_BACK.'classes'.DS);
define('ACYMAILING_TYPE', ACYMAILING_BACK.'types'.DS);
define('ACYMAILING_CONTROLLER', ACYMAILING_BACK.'controllers'.DS);
define('ACYMAILING_CONTROLLER_FRONT', ACYMAILING_FRONT.'controllers'.DS);
define('ACYMAILING_MEDIA', ACYMAILING_ROOT.'media'.DS.ACYMAILING_COMPONENT.DS);
define('ACYMAILING_TEMPLATE', ACYMAILING_MEDIA.'templates'.DS);
define('ACYMAILING_LANGUAGE', ACYMAILING_ROOT.'language'.DS);
define('ACYMAILING_INC', ACYMAILING_FRONT.'inc'.DS);

define('ACYMAILING_MEDIA_URL', rtrim(acymailing_rootURI(), '/').'/media/'.ACYMAILING_COMPONENT.'/');
define('ACYMAILING_IMAGES', ACYMAILING_MEDIA_URL.'images/');
define('ACYMAILING_CSS', ACYMAILING_MEDIA_URL.'css/');
define('ACYMAILING_JS', ACYMAILING_MEDIA_URL.'js/');

define('ACYMAILING_MEDIA_FOLDER', 'media/com_acymailing');

$jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
define('ACYMAILING_J16', version_compare($jversion, '1.6.0', '>='));
define('ACYMAILING_J25', version_compare($jversion, '2.5.0', '>='));
define('ACYMAILING_J30', version_compare($jversion, '3.0.0', '>='));
define('ACYMAILING_J40', version_compare($jversion, '4.0.0', '>='));

define('ACY_ALLOWRAW', defined('JREQUEST_ALLOWRAW') ? JREQUEST_ALLOWRAW : 2);
define('ACY_ALLOWHTML', defined('JREQUEST_ALLOWHTML') ? JREQUEST_ALLOWHTML : 4);

function acymailing_loadEditor(){
    include_once(rtrim(dirname(__DIR__), DS).DS.'compat'.DS.'joomla.editor.php');
}

function acymailing_getTime($date){
    static $timeoffset = null;
    if($timeoffset === null){
        $timeoffset = acymailing_getCMSConfig('offset');

        if(ACYMAILING_J16){
            $dateC = JFactory::getDate($date, $timeoffset);
            $timeoffset = $dateC->getOffsetFromGMT(true);
        }
    }

    return strtotime($date) - $timeoffset * 60 * 60 + date('Z');
}

function acymailing_fileGetContent($url, $timeout = 10){
    ob_start();
    $data = '';
    if(class_exists('JHttpFactory') && method_exists('JHttpFactory', 'getHttp')) {
        $http = JHttpFactory::getHttp();
        try {
            $response = $http->get($url, array(), $timeout);
        } catch (RuntimeException $e) {
            $response = null;
        }

        if ($response !== null && $response->code === 200) $data = $response->body;
    }

    if(empty($data) && function_exists('curl_exec') && filter_var($url, FILTER_VALIDATE_URL)){
        $conn = curl_init($url);
        curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($timeout)){
            curl_setopt($conn, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($conn, CURLOPT_CONNECTTIMEOUT, $timeout);
        }

        $data = curl_exec($conn);
        if($data === false) echo curl_error($conn);
        curl_close($conn);
    }

    if(empty($data) && function_exists('file_get_contents')){
        if(!empty($timeout)){
            ini_set('default_socket_timeout', $timeout);
        }
        $streamContext = stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false)));
        $data = file_get_contents($url, false, $streamContext);
    }

    if(empty($data) && function_exists('fopen') && function_exists('stream_get_contents')){
        $handle = fopen($url, "r");
        if(!empty($timeout)){
            stream_set_timeout($handle, $timeout);
        }
        $data = stream_get_contents($handle);
    }
    $warnings = ob_get_clean();

    if(acymailing_isDebug()) echo $warnings;

    return $data;
}

function acymailing_formToken(){
    return JHTML::_('form.token');
}

function acymailing_checkToken(){
    if(ACYMAILING_J40){
        \JSession::checkToken() or die('Invalid Token');;
    }else{
        if(!JRequest::checkToken() && !JRequest::checkToken('get')){
            if(!ACYMAILING_J16) die('Invalid Token');
            JSession::checkToken() || JSession::checkToken('get') || die('Invalid Token');
        }
    }
}

function acymailing_getFormToken() {
    if(ACYMAILING_J30) return JSession::getFormToken().'=1';
    return JUtility::getToken().'=1';
}

function acymailing_translation($key, $jsSafe = false, $interpretBackSlashes = true){
    return JText::_($key, $jsSafe, $interpretBackSlashes);
}

function acymailing_translation_sprintf(){
    $args = func_get_args();
    $return = "return JText::sprintf('".array_shift($args)."'";
    foreach($args as $oneArg){
        $return .= ",'".str_replace("'", "\\'", $oneArg)."'";
    }
    $return .= ');';
    return eval($return);
}

function acymailing_route($url, $xhtml = true, $ssl = null){
    return JRoute::_($url, $xhtml, $ssl);
}

function acymailing_getVar($type, $name, $default = null, $hash = 'default', $mask = 0){
    if(ACYMAILING_J40){
        if($mask & ACY_ALLOWRAW) $type = 'RAW';
        elseif($mask & ACY_ALLOWHTML) $type = 'HTML';

        return JFactory::getApplication()->input->get($name, $default, $type);
    }
    return JRequest::getVar($name, $default, $hash, $type, $mask);
}

function acymailing_setVar($name, $value = null, $hash = 'method', $overwrite = true){
    if(ACYMAILING_J40) return JFactory::getApplication()->input->set($name, $value);
    return JRequest::setVar($name, $value, $hash, $overwrite);
}

function acymailing_raiseError($level, $code, $msg, $info = null){
    return JError::raise($level, $code, $msg, $info);
}

function acymailing_getGroupsByUser($userid = null, $recursive = null){
    if(ACYMAILING_J16){
        if($userid === null){
            $userid = acymailing_currentUserId();
            $recursive = true;
        }

        jimport('joomla.access.access');
        return JAccess::getGroupsByUser($userid, $recursive);
    }

    $my = JFactory::getUser($userid);
    return array($my->gid);
}

function acymailing_getGroups(){
    $groups = acymailing_loadObjectList('SELECT a.*, a.title as text, a.id as value, COUNT(ugm.user_id) AS nbusers FROM #__usergroups AS a LEFT JOIN #__user_usergroup_map ugm ON a.id = ugm.group_id GROUP BY a.id', 'id');
    uasort($groups, 'acymailing_compareGroups');
    return $groups;
}

function acymailing_compareGroups($a, $b){
	if(empty($a->lft) || empty($b->lft)) return 0;
	return ($a->lft < $b->lft) ? -1 : 1;
}

function acymailing_getLanguages($installed = false){
    $result = array();

    $path = acymailing_getLanguagePath(ACYMAILING_ROOT);
    $dirs = acymailing_getFolders($path);

    $languages = acymailing_loadObjectList('SELECT * FROM #__languages', 'lang_code');

    foreach($dirs as $dir){
        if(strlen($dir) != 5 || $dir == "xx-XX") continue;
        if($installed && (empty($languages[$dir]) || $languages[$dir]->published != 1)) continue;

        $xmlFiles = acymailing_getFiles($path.DS.$dir, '^([-_A-Za-z]*)\.xml$');
        $xmlFile = reset($xmlFiles);
        if(empty($xmlFile)){
            $data = array();
        }else{
            if(ACYMAILING_J40){
                $data = \JInstaller::parseXMLInstallFile(ACYMAILING_LANGUAGE.$dir.DS.$xmlFile);
            }else{
                $data = JApplicationHelper::parseXMLLangMetaFile(ACYMAILING_LANGUAGE.$dir.DS.$xmlFile);
            }
        }

        $lang = new stdClass();
        $lang->sef = empty($languages[$dir]) ? null : $languages[$dir]->sef;
        $lang->language = strtolower($dir);
        $lang->name = empty($data['name']) ? (empty($languages[$dir]) ? $dir : $languages[$dir]->title_native) : $data['name'];
        $lang->exists = file_exists(ACYMAILING_LANGUAGE.$dir.DS.$dir.'.com_acymailing.ini');
        $lang->content = empty($languages[$dir]) ? false : $languages[$dir]->published == 1;

        $result[$dir] = $lang;
    }

    return $result;
}

function acymailing_languageFolder($code){
    return ACYMAILING_LANGUAGE.$code.DS;
}

function acymailing_cleanSlug($slug){
    $method = acymailing_getCMSConfig('unicodeslugs', 0) == 1 ? 'stringURLUnicodeSlug' : 'stringURLSafe';
    return JFilterOutput::$method(trim($slug));
}

function acymailing_punycode($email, $method = 'emailToPunycode'){
    if(empty($email) || version_compare(JVERSION, '3.1.2', '<')) return $email;
    $email = JStringPunycode::$method($email);
    return $email;
}

function acymailing_extractArchive($archive, $destination){
    return JArchive::extract($archive, $destination);
}

function acymailing_selectOption($value, $text = '', $optKey = 'value', $optText = 'text', $disable = false){
    return JHTML::_('select.option', $value, $text, $optKey, $optText, $disable);
}

function acymailing_gridID($rowNum, $recId, $checkedOut = false, $name = 'cid', $stub = 'cb'){
    return JHTML::_('grid.id', $rowNum, $recId, $checkedOut, $name, $stub);
}

function acymailing_select($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false){
    return JHTML::_('select.genericlist', $data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate);
}

function acymailing_radio($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false, $vertical = false){
    $element = class_exists('JHtmlAcyselect') ? 'acyselect' : 'select';
    return JHTML::_($element.'.radiolist', $data, $name, $attribs, $optKey, $optText, $selected, $idtag, $translate, $vertical);
}

function acymailing_calendar($value, $name, $id, $format = '%Y-%m-%d', $attribs = null){
    return JHTML::_('calendar', $value, $name, $id, $format, $attribs);
}

function acymailing_date($input = 'now', $format = null, $tz = true, $gregorian = false){
    return JHTML::_('date', $input, $format, $tz, $gregorian);
}

function acymailing_boolean($name, $attribs = null, $selected = null, $yes = 'JOOMEXT_YES', $no = 'JOOMEXT_NO', $id = false){
    $element = class_exists('JHtmlAcyselect') ? 'acyselect' : 'select';
    return JHTML::_($element.'.booleanlist', $name, $attribs, $selected, $yes, $no, $id);
}

function acymailing_addScript($raw, $script, $type = "text/javascript", $defer = false, $async = false){
    $acyDocument = acymailing_getGlobal('doc');

    if($raw){
        $acyDocument->addScriptDeclaration($script, $type);
    }else{
        $acyDocument->addScript($script, $type, $defer, $async);
    }
}

function acymailing_addStyle($raw, $style, $type = 'text/css', $media = null, $attribs = array()){
    $acyDocument = acymailing_getGlobal('doc');

    if($raw){
        $acyDocument->addStyleDeclaration($style, $type);
    }else{
        $acyDocument->addStyleSheet($style, $type, $media, $attribs);
    }
}

function acymailing_addMetadata($meta, $data, $name = 'name'){
    $acyDocument = acymailing_getGlobal('doc');

    $acyDocument->setMetaData($meta, $data, $name);
}

function acymailing_trigger($method, $args = array()){
    if(ACYMAILING_J40) return \JFactory::getApplication()->triggerEvent($method, $args);

    global $acydispatcher;
    if($acydispatcher === null){
        $acydispatcher = JDispatcher::getInstance();
    }
    return @$acydispatcher->trigger($method, $args);
}

function acymailing_isAdmin(){
    $acyapp = acymailing_getGlobal('app');

    return $acyapp->isAdmin();
}

function acymailing_getUserVar($key, $request, $default = null, $type = 'none'){
    $acyapp = acymailing_getGlobal('app');

    return $acyapp->getUserStateFromRequest($key, $request, $default, $type);
}

function acymailing_getCMSConfig($varname, $default = null){
    if(ACYMAILING_J30) {
        $acyapp = acymailing_getGlobal('app');
        $result = $acyapp->getCfg($varname, $default);
    }else{
        $conf = JFactory::getConfig();
        $val = $conf->getValue('config.'.$varname);

        $result = empty($val) ? $default : $val;
    }

    if ($varname == 'list_limit') {
        $possibilities = array(5, 10, 15, 20, 25, 30, 50, 100);
        $closest = 5;
        foreach ($possibilities as $possibility) {
            if (abs($result - $closest) > abs($result - $possibility)) {
                $closest = $possibility;
            }
        }
        $result = $closest;
    }

    return $result;
}

function acymailing_redirect($url, $msg = '', $msgType = 'message'){
    $acyapp = acymailing_getGlobal('app');

    if(ACYMAILING_J40){
        if(!empty($msg)){
            acymailing_enqueueMessage($msg, $msgType);
        }
        return $acyapp->redirect($url);
    }else{
        return $acyapp->redirect($url, $msg, $msgType);
    }
}

function acymailing_getLanguageTag(){
    $acylanguage = JFactory::getLanguage();

    return $acylanguage->getTag();
}

function acymailing_getLanguageLocale(){
    $acylanguage = JFactory::getLanguage();

    return $acylanguage->getLocale();
}

function acymailing_setLanguage($lang){
    $acylanguage = JFactory::getLanguage();

    $acylanguage->setLanguage($lang);
}

function acymailing_baseURI($pathonly = false){
    return JURI::base($pathonly);
}

function acymailing_rootURI($pathonly = false, $path = null){
    return JURI::root($pathonly, $path);
}

function acymailing_generatePassword($length = 8){
    return JUserHelper::genrandompassword($length);
}

function acymailing_currentUserId($email = null){
    if(!empty($email)){
        return acymailing_loadResult('SELECT id FROM '.acymailing_table('users', false).' WHERE email = '.acymailing_escapeDB($email));
    }

    $acymy = JFactory::getUser();

    return $acymy->id;
}

function acymailing_currentUserName($userid = null){
    if(!empty($userid)){
        $special = JFactory::getUser($userid);
        return $special->name;
    }

    $acymy = JFactory::getUser();

    return $acymy->name;
}

function acymailing_currentUserEmail($userid = null){
    if(!empty($userid)){
        $special = JFactory::getUser($userid);
        return $special->email;
    }

    $acymy = JFactory::getUser();

    return $acymy->email;
}

function acymailing_authorised($action, $assetname = null){
    $acymy = JFactory::getUser();

    return $acymy->authorise($action, $assetname);
}

function acymailing_loadLanguageFile($extension = 'joomla', $basePath = JPATH_SITE, $lang = null, $reload = false, $default = true){
    $acylanguage = JFactory::getLanguage();

    $acylanguage->load($extension, $basePath, $lang, $reload, $default);
}

function acymailing_getGlobal($type){
    $variables = array(
        'db' => array('acydb', 'getDBO'),
        'doc' => array('acyDocument', 'getDocument'),
        'app' => array('acyapp', 'getApplication')
    );

    global ${$variables[$type][0]};
    if(${$variables[$type][0]} === null){
        $method = $variables[$type][1];
        ${$variables[$type][0]} = JFactory::$method();
    }
    return ${$variables[$type][0]};
}

function acymailing_escapeDB($value){
    $acydb = acymailing_getGlobal('db');

    return $acydb->quote($value);
}

function acymailing_query($query){
    $acydb = acymailing_getGlobal('db');
    $acydb->setQuery($query);

    $method = ACYMAILING_J40 ? 'execute' : 'query';

    $result = $acydb->$method();
    if(!$result) return false;
    return $acydb->getAffectedRows();
}

function acymailing_loadObjectList($query, $key = '', $offset = null, $limit = null){
    $acydb = acymailing_getGlobal('db');

    $acydb->setQuery($query, $offset, $limit);
    return $acydb->loadObjectList($key);
}

function acymailing_loadObject($query){
    $acydb = acymailing_getGlobal('db');

    $acydb->setQuery($query);
    return $acydb->loadObject();
}

function acymailing_loadResult($query){
    $acydb = acymailing_getGlobal('db');

    $acydb->setQuery($query);
    return $acydb->loadResult();
}

function acymailing_loadResultArray($query){
    if(is_string($query)){
        $acydb = acymailing_getGlobal('db');
        $acydb->setQuery($query);
    }else{
        $acydb = $query;
    }

    if(ACYMAILING_J30) return $acydb->loadColumn();
    return $acydb->loadResultArray();
}

function acymailing_getEscaped($value, $extra = false) {
    $acydb = acymailing_getGlobal('db');

    if(ACYMAILING_J30) return $acydb->escape($value, $extra);
    return $acydb->getEscaped($value, $extra);
}

function acymailing_getDBError(){
    $acydb = acymailing_getGlobal('db');

    return $acydb->getErrorMsg();
}

function acymailing_insertObject($table, $element){
    $acydb = acymailing_getGlobal('db');
    $acydb->insertObject($table, $element);

    return $acydb->insertid();
}

function acymailing_insertID(){
    $acydb = acymailing_getGlobal('db');
    return $acydb->insertid();
}

function acymailing_updateObject($table, $element, $pkey){
    $acydb = acymailing_getGlobal('db');
    return $acydb->updateObject($table, $element, $pkey);
}

function acymailing_getColumns($table){
    $acydb = acymailing_getGlobal('db');
    
    if(ACYMAILING_J30) return $acydb->getTableColumns($table);
    $allfields = $acydb->getTableFields($table);
    return reset($allfields);
}

function acymailing_getPrefix(){
    $acydb = acymailing_getGlobal('db');
    return $acydb->getPrefix();
}

function acymailing_getTableList(){
    $acydb = acymailing_getGlobal('db');
    return $acydb->getTableList();
}

function acymailing_completeLink($link, $popup = false, $redirect = false){
    if($popup || acymailing_isNoTemplate()) $link .= '&'.acymailing_noTemplate();
    return acymailing_route('index.php?option='.ACYMAILING_COMPONENT.'&ctrl='.$link, !$redirect);
}

function acymailing_noTemplate(){
    return 'tmpl=component';
}

function acymailing_isNoTemplate(){
    return acymailing_getVar('cmd', 'tmpl') == 'component';
}

function acymailing_setNoTemplate($status = true){
    if($status) acymailing_setVar('tmpl', 'component');
    else acymailing_setVar('tmpl', '');
}

function acymailing_cmsLoaded(){
    defined('_JEXEC') or die('Restricted access');
}

function acymailing_formOptions($order = null, $task = ''){
    echo '<input type="hidden" name="option" value="'.ACYMAILING_COMPONENT.'"/>';
    echo '<input type="hidden" name="task" value="'.$task.'"/>';
    echo '<input type="hidden" name="ctrl" value="'.acymailing_getVar('cmd', 'ctrl', '').'"/>';
    if($order) {
        echo '<input type="hidden" name="boxchecked" value="0"/>';
        echo '<input type="hidden" name="filter_order" value="'.$order->value.'"/>';
        echo '<input type="hidden" name="filter_order_Dir" value="'.$order->dir.'"/>';
    }
    echo acymailing_formToken();
}

function acymailing_enqueueMessage($message, $type = 'success'){
    $result = is_array($message) ? implode('<br/>', $message) : $message;

    if(acymailing_isAdmin()){
        if(ACYMAILING_J30){
            $type = str_replace(array('notice', 'message'), array('info', 'success'), $type);
        }else{
            $type = str_replace(array('message', 'notice', 'warning'), array('info', 'warning', 'error'), $type);
        }
    }else{
        if(ACYMAILING_J30){
            $type = str_replace(array('success', 'info'), array('message', 'notice'), $type);
        }else{
            $type = str_replace(array('success', 'error', 'warning', 'info'), array('message', 'warning', 'notice', 'message'), $type);
        }
    }

    $acyapp = acymailing_getGlobal('app');

    $acyapp->enqueueMessage($result, $type);
}

function acymailing_displayMessages(){
    $acyapp = acymailing_getGlobal('app');
    $messages = $acyapp->getMessageQueue(true);
    if(empty($messages)) return;

    $sorted = array();
    foreach ($messages as $oneMessage) {
        $sorted[$oneMessage['type']][] = $oneMessage['message'];
    }

    foreach ($sorted as $type => $message) {
        acymailing_display($message, $type);
    }
}

function acymailing_editCMSUser($userid){
    return acymailing_route('index.php?option=com_users&view=user&layout=edit&id='.$userid);
}

function acymailing_prepareAjaxURL($url){
    return htmlspecialchars_decode(acymailing_completeLink($url, true));
}

function acymailing_cmsACL(){
    if(!ACYMAILING_J16 || !acymailing_authorised('core.admin', 'com_acymailing')) return '';

    $return = urlencode(base64_encode((string)JUri::getInstance()));
    return '
        <span class="acyblocktitle">'.acymailing_translation('ACY_JOOMLA_PERMISSIONS').'</span>
        <a class="acymailing_button_grey" style="color:#666;" target="_blank" href="index.php?option=com_config&view=component&component=com_acymailing&path=&return='.$return.'">'.acymailing_translation('JTOOLBAR_OPTIONS').'</a><br/>
    </div>
    <div class="onelineblockoptions">
		<span class="acyblocktitle">'.acymailing_translation('ACY_ACL').'</span>';
}

function acymailing_isDebug(){
    return defined('JDEBUG') && JDEBUG;
}

function acymailing_setPageTitle($title){
    if(empty($title)){
        $title = acymailing_getCMSConfig('sitename');
    }elseif(acymailing_getCMSConfig('sitename_pagetitles', 0) == 1){
        $title = acymailing_translation_sprintf('ACY_JPAGETITLE', acymailing_getCMSConfig('sitename'), $title);
    }elseif(acymailing_getCMSConfig('sitename_pagetitles', 0) == 2){
        $title = acymailing_translation_sprintf('ACY_JPAGETITLE', $title, acymailing_getCMSConfig('sitename'));
    }
    $document = JFactory::getDocument();
    $document->setTitle($title);
}

function acymailing_importPlugin($family, $name = null){
    JPluginHelper::importPlugin($family, $name);
}

function acymailing_getPlugin($type, $name = null){
    return JPluginHelper::getPlugin($type, $name);
}

function acymailing_isPluginEnabled($type, $name = null){
    return JPluginHelper::isEnabled($type, $name);
}

function acymailing_getLanguagePath($basePath = ACYMAILING_BASE, $language = null){
    return JLanguage::getLanguagePath(rtrim($basePath, DS), $language);
}

function acymailing_userEditLink(){
    if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_comprofiler'.DS.'comprofiler.php')){
        $editLink = 'index.php?option=com_comprofiler&task=edit&cid[]=';
    }elseif(!ACYMAILING_J16){
        $editLink = 'index.php?option=com_users&task=edit&cid[]=';
    }else{
        $editLink = 'index.php?option=com_users&task=user.edit&id=';
    }
    return $editLink;
}

function acymailing_filterText($text){
    if(ACYMAILING_J25) return JComponentHelper::filterText($text);
    return $text;
}

function acymailing_checkPluginsFolders(){
    $folders = array(ACYMAILING_ROOT.'plugins' => '', ACYMAILING_ROOT.'plugins'.DS.'user' => '', ACYMAILING_ROOT.'plugins'.DS.'system' => '');
    $results = array('', '', '');
    foreach($folders as $oneFolderToCheck => &$result){
        if(!is_writable($oneFolderToCheck)){
            $writableIssue = true;
            break;
        }
    }
    if(!empty($writableIssue)){
        $results = array();
        foreach($folders as $oneFolderToCheck => &$result){
            $results[] = ' : <span style="color:'.(is_writable($oneFolderToCheck) ? 'green;">OK' : 'red;">Not writable').'</span>';
        }
    }
    $errorPluginTxt = 'Some required AcyMailing plugins have not been installed.<br />Please make sure your plugins folders are writables by checking the user/group permissions:<br />* Joomla / Plugins'.$results[0].'<br />* Joomla / Plugins / User'.$results[1].'<br />* Joomla / Plugins / System'.$results[0].'<br />';
    if(empty($writableIssue)) $errorPluginTxt .= 'Please also empty your plugins cache: System => Clear cache => com_plugins => Delete<br />';
    acymailing_display($errorPluginTxt.'<a href="index.php?option=com_acymailing&amp;ctrl=update&amp;task=install">'.acymailing_translation('ACY_ERROR_INSTALLAGAIN').'</a>', 'warning');
}

function acymailing_askLog($current = true, $message = 'ACY_NOTALLOWED', $type = 'error'){
    $usercomp = ACYMAILING_J16 ? 'com_users' : 'com_user';
    $url = 'index.php?option='.$usercomp.'&view=login';
    if($current) $url .= '&return='.base64_encode(acymailing_currentURL());
    acymailing_redirect($url, acymailing_translation($message), $type);
}

function acymailing_frontendLink($link, $newsletter = true, $popup = false, $complete = false){
    if($complete) $link = 'index.php?option=com_acymailing&ctrl='.$link;

    if($popup) $link .= '&'.acymailing_noTemplate();
    $config = acymailing_config();

    if($config->get('use_sef', 0) && strpos($link, '&ctrl=cron') === false){

        if($newsletter) return '{acyfrontsef}'.$link.'{/acyfrontsef}';

        $sefLink = acymailing_fileGetContent(acymailing_rootURI().'index.php?option=com_acymailing&ctrl=url&task=sef&urls[0]='.base64_encode($link));
        $json = json_decode($sefLink, true);
        if($json == null){
            if(!empty($sefLink) && acymailing_isDebug()) acymailing_enqueueMessage('Error trying to get the sef link: '.$sefLink);
        }else{
            $link = array_shift($json);
            return $link;
        }
    }

    $mainurl = acymailing_mainURL($link);

    return $mainurl.$link;
}

function acymailing_addBreadcrumb($title, $link = ''){
    $acyapp = acymailing_getGlobal('app');
    $pathway = $acyapp->getPathway();
    $pathway->addItem($title, $link);
}

function acymailing_getMenu(){
    global $Itemid;

    $jsite = JFactory::getApplication('site');
    $menus = $jsite->getMenu();
    $menu = $menus->getActive();

    if(empty($menu) && !empty($Itemid)){
        $menus->setActive($Itemid);
        $menu = $menus->getItem($Itemid);
    }
    
    return $menu;
}

function acymailing_getTitle(){
    $document = acymailing_getGlobal('doc');
    return $document->getTitle();
}

jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

if(ACYMAILING_J30){
    class acymailingBridgeController extends JControllerLegacy{
        function __construct($config = array()){
            parent::__construct($config);
            global $acymailingCmsUserVars;
            $this->cmsUserVars = $acymailingCmsUserVars;
        }
    }

    class acymailingView extends JViewLegacy{
        var $chosen = true;

        function __construct($config = array()){
            parent::__construct($config);
            global $acymailingCmsUserVars;
            $this->cmsUserVars = $acymailingCmsUserVars;
        }

        function display($tpl = null){
            if($this->chosen && acymailing_isAdmin()){
                JHtml::_('formbehavior.chosen', 'select');
            }

            return parent::display($tpl);
        }
    }
}else{
    class acymailingBridgeController extends JController{
        function __construct($config = array()){
            parent::__construct($config);
            global $acymailingCmsUserVars;
            $this->cmsUserVars = $acymailingCmsUserVars;
        }
    }
    class acymailingView extends JView{
        function __construct($config = array()){
            parent::__construct($config);
            global $acymailingCmsUserVars;
            $this->cmsUserVars = $acymailingCmsUserVars;
        }
    }
}

acymailing_boolean('acymailing');
$config = acymailing_config();
if(!ACYMAILING_J40 && ACYMAILING_J30 && (acymailing_isAdmin() || $config->get('bootstrap_frontend', 0))){
    require(ACYMAILING_BACK.'compat'.DS.'bootstrap.php');
}else{
    class JHtmlAcyselect extends JHTMLSelect{
    }
}

global $acymailingCmsUserVars;
$acymailingCmsUserVars = new stdClass();
$acymailingCmsUserVars->table = 'users';
$acymailingCmsUserVars->name = 'name';
$acymailingCmsUserVars->username = 'username';
$acymailingCmsUserVars->id = 'id';
$acymailingCmsUserVars->email = 'email';
$acymailingCmsUserVars->registered = 'registerDate';
$acymailingCmsUserVars->blocked = 'block';
