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

class DynamicsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultTask('popup');
    }

    public function popup()
    {
        $plugins = acym_trigger('dynamicText');
        $isAutomation = acym_getVar('string', 'automation');

        $js = 'function setTag(tagvalue, element){
                    var $allRows = jQuery(".acym__listing__row__popup");
                    $allRows.removeClass("selected_row");
                    element.addClass("selected_row");
                    window.document.getElementById(\'dtextcode\').value = tagvalue;
               }';
        $js .= 'try{window.parent.previousSelection = window.parent.getPreviousSelection(); }catch(err){window.parent.previousSelection=false; }';

        acym_addScript(true, $js);

        $tab = acym_get('helper.tab');

        $data = [
            "type" => acym_getVar('string', 'type', 'news'),
            "plugins" => $plugins,
            "tab" => $tab,
            "automation" => $isAutomation,
        ];

        parent::display($data);
    }

    public function replaceDummy()
    {
        $mailId = acym_getVar('int', 'mailId', 0);
        if (!empty($mailId)) {
            $mailClass = acym_get('class.mail');
            $email = $mailClass->getOneById($mailId);
        }

        if (empty($email)) {
            $email = new stdClass();
            $email->id = 0;
            $email->name = '';
            $email->subject = '';
            $email->from_name = '';
            $email->from_email = '';
            $email->reply_to_name = '';
            $email->reply_to_email = '';
            $email->bcc = '';
        }

        $email->creation_date = acym_date('now', 'Y-m-d H:i:s', false);
        $email->creator_id = acym_currentUserId();
        $email->thumbnail = '';
        $email->drag_editor = '1';
        $email->library = '0';
        $email->type = 'standard';
        $email->template = '0';
        $email->settings = '';
        $email->stylesheet = '';
        $email->attachments = '';

        $email->body = acym_getVar('string', 'code', '');


        acym_trigger('replaceContent', [&$email]);

        $userClass = acym_get('class.user');
        $userEmail = acym_currentUserEmail();
        $user = $userClass->getOneByEmail($userEmail);

        if (empty($user)) {
            $user = new stdClass();
            $user->email = acym_currentUserEmail();
            $user->name = acym_currentUserName();
            $user->cms_id = acym_currentUserId();
            $user->confirmed = 0;
            $user->source = ACYM_CMS;

            $userClass->checkVisitor = false;
            $user->id = $userClass->save($user);
        }

        acym_trigger('replaceUserInformation', [&$email, &$user, false]);

        echo $email->body;
        exit;
    }

    function trigger()
    {
        $plugin = acym_getVar('cmd', 'plugin', '');
        $trigger = acym_getVar('cmd', 'trigger', '');
        if (empty($plugin) || empty($trigger)) {
            exit;
        }

        acym_trigger($trigger, [], $plugin);

        exit;
    }
}

