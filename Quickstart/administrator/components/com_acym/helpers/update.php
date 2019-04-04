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

class acymupdateHelper
{
    var $errors = array();

    var $bounceVersion = 1;

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;
    }

    public function installBounceRules()
    {
        $bounceClass = acym_get('class.bounce');
        if ($bounceClass->getOrderingNumber() > 0) {
            return;
        }
        $config = acym_config();

        $forwardEmail = ($config->get('replyto_email') != $config->get('bounce_email')) ? $config->get('replyto_email') : $config->get('from_email');
        if (empty($forwardEmail)) {
            $forwardEmail = acym_currentUserEmail();
        }

        $query = "INSERT INTO `#__acym_rule` (`id`, `name`, `ordering`, `regex`, `executed_on`, `action_message`, `action_user`, `active`, `increment_stats`, `execute_action_after`) VALUES";
        $query .= "(1, 'ACYM_ACTION_REQUIRED', 1, 'action *requ|verif', '[\"subject\"]', '{\"0\":\"delete_message\",\"1\":\"forward_message\",\"forward_to\":\"$forwardEmail\"}', '[]', 1, 0, 0),";
        $query .= "(2, 'ACYM_ACKNOWLEDGMENT_RECEIPT_SUBJECT', 2, '(out|away) *(of|from)|vacation|holiday|absen|congés|recept|acknowledg|thank you for', '[\"subject\"]', '[\"delete_message\"]', '[]', 1, 0, 0),";
        $query .= "(3, 'ACYM_FEEDBACK_LOOP', 3, 'feedback|staff@hotmail.com|complaints@.{0,15}email-abuse.amazonses.com|complaint about message', '[\"senderInfo\",\"subject\"]', '[\"save_message\",\"delete_message\"]', '[\"unsubscribe_user\"]', 1, 0, 0),";
        $query .= "(4, 'ACYM_FEEDBACK_LOOP_BODY', 4, 'Feedback-Type.{1,5}abuse', '[\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"unsubscribe_user\"]', 1, 1, 0),";
        $query .= "(5, 'ACYM_MAILBOX_FULL', 5, '((mailbox|mailfolder|storage|quota|space|inbox) *(is)? *(over)? *(exceeded|size|storage|allocation|full|quota|maxi))|status(-code)? *(:|=)? *5.2.2|quota-issue|not *enough.{1,20}space|((over|exceeded|full|exhausted) *(allowed)? *(mail|storage|quota))', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(6, 'ACYM_BLOCKED_GOOGLE_GROUPS', 6, 'message *rejected *by *Google *Groups', '[\"body\"]', '[\"delete_message\"]', '[]', 1, 1, 0),";
        $query .= "(7, 'ACYM_MAILBOX_DOESNT_EXIST_1', 7, '(Invalid|no such|unknown|bad|des?activated|inactive|unrouteable) *(mail|destination|recipient|user|address|person)|bad-mailbox|inactive-mailbox|not listed in.{1,20}directory|RecipNotFound|(user|mailbox|address|recipients?|host|account|domain) *(is|has been)? *(error|disabled|failed|unknown|unavailable|not *(found|available)|.{1,30}inactiv)|no *mailbox *here|user does.?n.t have.{0,30}account', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(8, 'ACYM_MESSAGE_BLOCKED_RECIPIENTS', 8, 'blocked *by|block *list|look(ed)? *like *spam|spam-related|spam *detected| CXBL | CDRBL | IPBL | URLBL |(unacceptable|banned|offensive|filtered|blocked|unsolicited) *(content|message|e?-?mail)|service refused|(status(-code)?|554) *(:|=)? *5.7.1|administratively *denied|blacklisted *IP|policy *reasons|rejected.{1,10}spam|junkmail *rejected|throttling *constraints|exceeded.{1,10}max.{1,40}hour|comply with required standards|421 RP-00|550 SC-00|550 DY-00|550 OU-00', '[\"body\"]', '{\"0\":\"delete_message\",\"1\":\"forward_message\",\"forward_to\":\"''$forwardEmail''\"}', '[]', 1, 1, 0),";
        $query .= "(9, 'ACYM_MAILBOX_DOESNT_EXIST_2', 9, 'status(-code)? *(:|=)? *5.(1.[1-6]|0.0|4.[0123467])|recipient *address *rejected|does *not *like *recipient', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(10, 'ACYM_DOMAIN_NOT_EXIST', 10, 'No.{1,10}MX *(record|host)|host *does *not *receive *any *mail|bad-domain|connection.{1,10}mail.{1,20}fail|domain.{1,10}not *exist|fail.{1,10}establish *connection', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(11, 'ACYM_TEMPORARY_FAILURES', 11, 'has.*been.*delayed|delayed *mail|message *delayed|message-expired|temporar(il)?y *(failure|unavailable|disable|offline|unable)|deferred|delayed *([0-9]*) *(hour|minut)|possible *mail *loop|too *many *hops|delivery *time *expired|Action: *delayed|status(-code)? *(:|=)? *4.4.6|will continue to be attempted|unable to deliver in.*Status: 4.4.7', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(12, 'ACYM_FAILED_PERM', 12, 'failed *permanently|permanent.{1,20}(failure|error)|not *accepting *(any)? *mail|does *not *exist|no *valid *route|delivery *failure', '[\"subject\",\"body\"]', '[\"save_message\",\"delete_message\"]', '[\"block_user\"]', 1, 1, 0),";
        $query .= "(13, 'ACYM_ACKNOWLEDGMENT_RECEIPT_BODY', 13, 'vacances|holiday|vacation|absen|urlaub', '[\"body\"]', '[\"delete_message\"]', '[]', 1, 0, 0),";
        $query .= "(14, 'ACYM_FINAL_RULE', 14, '.', '[\"senderInfo\",\"subject\"]', '{\"0\":\"delete_message\",\"1\":\"forward_message\",\"forward_to\":\"$forwardEmail\"}', '[]', 1, 1, 0);";

        acym_query($query);

        $newConfig = new stdClass();
        $newConfig->bounceVersion = $this->bounceVersion;
        $config->save($newConfig);
    }

    function addUpdateSite()
    {
        $config = acym_config();

        $newconfig = new stdClass();
        $newconfig->website = ACYM_LIVE;
        $newconfig->max_execution_time = 0;

        $config->save($newconfig);

        acym_query("DELETE FROM #__updates WHERE element = 'com_acym'");

        $update_site_id = acym_loadResult("SELECT update_site_id FROM #__update_sites WHERE location LIKE '%component=acymailing%' AND type LIKE 'extension'");

        $object = new stdClass();
        $object->name = 'AcyMailing';
        $object->type = 'extension';
        $object->location = ACYM_UPDATEMEURL.'updatexml&component=acymailing&cms=joomla&level='.$config->get('level').'&version='.$config->get('version');
        if (acym_level(1)) {
            $object->location .= '&li='.urlencode(base64_encode(ACYM_LIVE));
        }

        $object->enabled = 1;

        if (empty($update_site_id)) {
            $update_site_id = acym_insertObject("#__update_sites", $object);
        } else {
            $object->update_site_id = $update_site_id;
            acym_updateObject("#__update_sites", $object, 'update_site_id');
        }

        $extension_id = acym_loadResult("SELECT extension_id FROM #__extensions WHERE `element` = 'com_acym' AND type LIKE 'component'");
        if (empty($update_site_id) || empty($extension_id)) {
            return false;
        }

        $query = 'INSERT IGNORE INTO #__update_sites_extensions (update_site_id, extension_id) values ('.$update_site_id.','.$extension_id.')';
        acym_query($query);

        return true;
    }

    function installLanguages($output = true)
    {
        $siteLanguages = acym_getLanguages();
        if (!empty($siteLanguages[ACYM_DEFAULT_LANGUAGE])) {
            unset($siteLanguages[ACYM_DEFAULT_LANGUAGE]);
        }

        $installedLanguages = array_keys($siteLanguages);
        if (empty($installedLanguages)) {
            return;
        }

        if (!$output) {
            $newConfig = new stdClass();
            $newConfig->installlang = implode(',', $installedLanguages);
            $config = acym_config();
            $config->save($newConfig);

            return;
        }

        $js = '
			var xhr = new XMLHttpRequest();
			xhr.open("GET", "'.acym_prepareAjaxURL('file').'&task=installLanguages&languages='.implode(',', $installedLanguages).'");
			xhr.onload = function(){
				container = document.getElementById("acym_div");
				container.innerHTML = xhr.responseText+container.innerHTML;
			};
			xhr.send();';
        acym_addScript(true, $js);
    }

    function installBackLanguages($onlyCode = '')
    {
        if ('Joomla' != 'Joomla') {
            return;
        }

        $menuStrings = array(
            'ACYM_USERS',
            'ACYM_CUSTOM_FIELDS',
            'ACYM_LISTS',
            'ACYM_TEMPLATES',
            'ACYM_CAMPAIGNS',
            'ACYM_QUEUE',
            'ACYM_AUTOMATION',
            'ACYM_STATISTICS',
            'ACYM_BOUNCE_HANDLING',
            'ACYM_CONFIGURATION',
            'ACYM_MENU_PROFILE',
            'ACYM_MENU_PROFILE_DESC',
            'ACYM_MENU_ARCHIVE',
            'ACYM_MENU_ARCHIVE_DESC'
        );

        if (empty($onlyCode)) {
            $siteLanguages = array_keys(acym_getLanguages());
        } else {
            $siteLanguages = array($onlyCode);
        }

        foreach ($siteLanguages as $code) {

            $path = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym.ini';
            if (!file_exists($path)) {
                continue;
            }

            $content = file_get_contents($path);
            if (empty($content)) {
                continue;
            }


            $menuFileContent = 'ACYM="AcyMailing 6"'."\r\n";
            $menuFileContent .= 'COM_ACYM="AcyMailing 6"'."\r\n";
            $menuFileContent .= 'COM_ACYM_CONFIGURATION="AcyMailing 6"'."\r\n";

            foreach ($menuStrings as $oneString) {
                preg_match('#'.$oneString.'="(.*)"#i', $content, $matches);
                if (empty($matches[1])) {
                    continue;
                }
                $menuFileContent .= $oneString.'="'.$matches[1].'"'."\r\n";
            }

            $menuPath = ACYM_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acym.sys.ini';

            if (!acym_writeFile($menuPath, $menuFileContent)) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_FAIL_SAVE_FILE', $menuPath), 'error', 0);
            }
        }
    }

    function installFields()
    {
        $query = "INSERT IGNORE INTO #__acym_field (`id`, `name`, `type`, `value`, `active`, `default_value`, `required`, `ordering`, `option`, `core`, `backend_profile`, `backend_listing`, `backend_filter`, `frontend_form`, `frontend_profile`, `frontend_listing`, `frontend_filter`, `access`) VALUES
    (1, 'ACYM_NAME', 'text', NULL, 1, NULL, 0, 1, '{\"editable_user_creation\":\"1\",\"editable_user_modification\":\"1\",\"error_message\":\"\",\"error_message_invalid\":\"\",\"size\":\"\",\"rows\":\"\",\"columns\":\"\",\"format\":\"\",\"custom_text\":\"\",\"css_class\":\"\",\"authorized_content\":{\"0\":\"all\",\"regex\":\"\"}}', 1, 1, 1, 0, 1, 1, 1, 0, 'all'),
    (2, 'ACYM_EMAIL', 'text', NULL, 1, NULL, 1, 2, '{\"editable_user_creation\":\"1\",\"editable_user_modification\":\"1\",\"error_message\":\"\",\"error_message_invalid\":\"\",\"size\":\"\",\"rows\":\"\",\"columns\":\"\",\"format\":\"\",\"custom_text\":\"\",\"css_class\":\"\",\"authorized_content\":{\"0\":\"all\",\"regex\":\"\"}}', 1, 1, 1, 0, 1, 1, 1, 0, 'all');";
        acym_query($query);
    }

    function installTemplate()
    {
        $names = array('default_template', 'default_template_2');
        foreach ($names as $name) {
            $query = "INSERT INTO `#__acym_mail` (`name`, `creation_date`, `thumbnail`, `drag_editor`, `library`, `type`, `body`, `subject`, `template`, `from_name`, `from_email`, `reply_to_name`, `reply_to_email`, `bcc`, `settings`, `stylesheet`, `attachments`, `creator_id`) VALUES
                     ('".str_replace('_', ' ', $name)."', '".date('Y-m-d H:i:s')."', ".acym_escapeDB(ACYM_IMAGES.'img_template'.DS.$name.'.png').", 1, 1, 'standard', ".acym_escapeDB(str_replace('{acym_media}', ACYM_IMAGES, file_get_contents(ACYM_BACK.'templates'.DS.$name.DS.'content.txt'))).", 'Subject', 1, NULL, NULL, NULL, NULL, NULL, ".acym_escapeDB(file_get_contents(ACYM_BACK.'templates'.DS.$name.DS.'settings.txt')).", '', NULL, 1);";
            acym_query($query);
        }

        acym_deleteFolder(ACYM_BACK.'templates');
    }

    function installNotifications()
    {
        $searchSettings = array(
            'offset' => 0,
            'mailsPerPage' => 9000,
            'key' => 'name',
        );

        $mailClass = acym_get('class.mail');
        $notifications = $mailClass->getMailsByType('notification', $searchSettings);
        $notifications = $notifications['mails'];

        $addNotif = array();

        if (empty($notifications['acy_report'])) {
            $addNotif[] = array(
                'name' => 'acy_report',
                'subject' => 'AcyMailing Cron Report {mainreport}',
                'content' => '<p>{report}</p><p>{detailreport}</p>',
            );
        }

        if (empty($notifications['acy_confirm'])) {
            $addNotif[] = array(
                'name' => 'acy_confirm',
                'subject' => '{subtag:name|ucfirst}, {trans:ACYM_PLEASE_CONFIRM_SUBSCRIPTION}',
                'content' => $this->getFormatedNotification(
                    '<h1 style="font-size: 24px;">Hello {subtag:name|ucfirst},</h1>
                    <p>{trans:ACYM_CONFIRM_MESSAGE}</p>
                    <p>{trans:ACYM_CONFIRM_MESSAGE_ACTIVATE}</p>
                    <p style="text-align: center;"><strong>{confirm}{trans:ACYM_CONFIRM_SUBSCRIPTION}{/confirm}</strong></p>'
                ),
            );
        }

        if (!empty($addNotif)) {
            foreach ($addNotif as $oneNotif) {
                $notif = new stdClass();
                $notif->type = 'notification';
                $notif->library = 1;
                $notif->template = 0;
                $notif->drag_editor = 1;
                $notif->creator_id = acym_currentUserId();
                $notif->creation_date = date('Y-m-d H:i:s', time());
                $notif->name = $oneNotif['name'];
                $notif->subject = $oneNotif['subject'];
                $notif->body = $oneNotif['content'];

                $mailClass->save($notif);
            }
        }
    }

    private function getFormatedNotification($content)
    {
        $begining = '<div id="acym__wysid__template" class="cell"><table class="body"><tbody><tr><td align="center" class="center acym__wysid__template__content" valign="top" style="background-color: rgb(239, 239, 239); padding: 40px 0px;"><center><table align="center"><tbody><tr><td class="acym__wysid__row ui-droppable ui-sortable" style="min-height: 0px; display: table-cell;"><table class="row acym__wysid__row__element" bgcolor="#dadada"><tbody style="background-color: rgb(218, 218, 218);" bgcolor="#ffffff"><tr><th class="small-12 medium-12 large-12 columns acym__wysid__row__element__th"><table class="acym__wysid__column" style="min-height: 0px; display: table;"><tbody class="ui-sortable" style="min-height: 0px; display: table-row-group;"><tr class="acym__wysid__column__element ui-draggable" style="position: relative; top: inherit; left: inherit; right: inherit; bottom: inherit; height: auto;"><td class="large-12 acym__wysid__column__element__td" style="outline: rgb(0, 163, 254) dashed 0px; outline-offset: -1px;"><span class="acy-editor__space acy-editor__space--focus" style="display: block; padding: 0px; margin: 0px; height: 10px;"></span></td></tr></tbody></table></th></tr></tbody></table><table class="row acym__wysid__row__element" bgcolor="#ffffff"><tbody style="background-color: rgb(255, 255, 255);" bgcolor="#ffffff"><tr><th class="small-12 medium-12 large-12 columns"><table class="acym__wysid__column" style="min-height: 0px; display: table;"><tbody class="ui-sortable" style="min-height: 0px; display: table-row-group;"><tr class="acym__wysid__column__element ui-draggable" style="position: relative; top: inherit; left: inherit; right: inherit; bottom: inherit; height: auto;"><td class="large-12 acym__wysid__column__element__td" style="outline: rgb(0, 163, 254) dashed 0px; outline-offset: -1px;"><div class="acym__wysid__tinymce--text mce-content-body" style="position: relative;" spellcheck="false">';
        $ending = '</div></td></tr></tbody></table></th></tr></tbody></table><table class="row acym__wysid__row__element" bgcolor="#dadada" style="position: relative; z-index: 100; top: 0px; left: 0px;"><tbody style="background-color: rgb(218, 218, 218);" bgcolor="#ffffff"><tr><th class="small-12 medium-12 large-12 columns acym__wysid__row__element__th"><table class="acym__wysid__column" style="min-height: 0px; display: table;"><tbody class="ui-sortable" style="min-height: 0px; display: table-row-group;"><tr class="acym__wysid__column__element ui-draggable" style="position: relative; top: inherit; left: inherit; right: inherit; bottom: inherit; height: auto;"><td class="large-12 acym__wysid__column__element__td" style="outline: rgb(0, 163, 254) dashed 0px; outline-offset: -1px;"><span class="acy-editor__space acy-editor__space--focus" style="display: block; padding: 0px; margin: 0px; height: 10px;"></span></td></tr></tbody></table></th></tr></tbody></table></td></tr></tbody></table></center></td></tr></tbody></table></div>';

        return $begining.$content.$ending;
    }

    function installExtensions()
    {
        $dirs = acym_getFolders(ACYM_BACK.'extensions');

        if (empty($dirs)) {
            return true;
        }
        $installer = JInstaller::getInstance();

        foreach ($dirs as $oneExtension) {
            $installer->install(ACYM_BACK.'extensions'.DS.$oneExtension);
        }

        $extensionsName = array('AcyMailing v6 JCE integration');

        acym_query("UPDATE #__extensions SET enabled = 1 WHERE name IN ('".implode("', '", $extensionsName)."')");

        acym_deleteFolder(ACYM_BACK.'extensions');
    }
}
