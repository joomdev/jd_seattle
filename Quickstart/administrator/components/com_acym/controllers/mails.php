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

class MailsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_TEMPLATES')] = acym_completeLink('mails');
        $this->loadScripts = array(
            'edit' => array('colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'),
            'apply' => array('colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'),
            'test' => array('colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'),
        );
        header('X-XSS-Protection:0');
    }

    public function listing()
    {
        acym_setVar("layout", "listing");

        $searchFilter = acym_getVar('string', 'mails_search', '');
        $tagFilter = acym_getVar('string', 'mails_tag', '');
        $ordering = acym_getVar('string', 'mails_ordering', 'id');
        $status = acym_getVar('string', 'mails_status', '');
        $orderingSortOrder = acym_getVar('string', 'mails_ordering_sort_order', 'desc');

        $mailsPerPage = 12;
        $page = acym_getVar('int', 'mails_pagination_page', 1);

        $mailClass = acym_get('class.mail');
        $matchingMails = $mailClass->getMatchingMails(
            array(
                'ordering' => $ordering,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'status' => $status,
                'ordering_sort_order' => $orderingSortOrder,
            )
        );

        if ($page > 1 && empty(count($matchingMails['mails']))) {
            acym_setVar('mails_pagination_page', 1);
            $this->listing();

            return;
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }
        $mailsData = array(
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'status' => $status,
            'mailNumberPerStatus' => $matchingMails['status'],
            'orderingSortOrder' => $orderingSortOrder,
        );

        return parent::display($mailsData);
    }

    public function choose()
    {
        acym_setVar("layout", "choose");

        $this->breadcrumb[acym_translation('ACYM_CREATE')] = "";

        $searchFilter = acym_getVar('string', 'mailchoose_search', '');
        $tagFilter = acym_getVar('string', 'mailchoose_tag', 0);
        $ordering = acym_getVar('string', 'mailchoose_ordering', 'creation_date');
        $orderingSortOrder = acym_getVar('string', 'mailchoose_ordering_sort_order', 'DESC');
        $type = acym_getVar('string', 'mailchoose_type', 'custom');

        $mailsPerPage = 12;
        $page = acym_getVar('int', 'mailchoose_pagination_page', 1);

        $mailClass = acym_get('class.mail');
        $matchingMails = $mailClass->getMatchingMails(
            array(
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'type' => $type,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }

        $mailsData = array(
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'type' => $type,
        );


        parent::display($mailsData);
    }

    public function edit()
    {
        $mailClass = acym_get('class.mail');
        $tempId = acym_getVar("int", "id");
        $mailClass = acym_get('class.mail');
        $typeEditor = acym_getVar('string', 'type_editor');
        $notification = acym_getVar("cmd", "notification");
        $return = empty(acym_getVar('string', 'return', '')) ? '' : urldecode(acym_getVar('string', 'return', ''));
        $type = acym_getVar('string', 'type');

        if (!empty($notification)) {
            $mail = $mailClass->getOneByName($notification);
            if (!empty($mail->id)) {
                $tempId = $mail->id;
            }
        }

        if ('automation' == $type || empty($tempId)) {
            $fromId = acym_getVar("int", "from");

            if (empty($fromId)) {
                $mail = new stdClass();
                $mail->name = '';
                $mail->subject = '';
                $mail->tags = array();
                $mail->type = '';
                $mail->body = '';
                $mail->editor = $typeEditor;
            } else {
                $mail = $mailClass->getOneById($fromId);
                $mail->editor = $mail->drag_editor == 0 ? 'html' : 'acyEditor';
            }

            if (!empty($type)) {
                $mail->type = $type;
            }

            if ('automation' != $type || empty($fromId)) $mail->id = 0;
            $this->breadcrumb[acym_translation('ACYM_CREATE_TEMPLATE')] = acym_completeLink('mails&task=edit&type_editor='.$typeEditor.(!empty($fromId) ? '&from='.$fromId : '').'&type='.$type.(!empty($return) ? '&return='.urlencode($return) : ''));
        } else {
            $mail = $mailClass->getOneById($tempId);
            $mail->editor = $mail->drag_editor == 0 ? 'html' : 'acyEditor';

            if (empty($notification)) {
                $this->breadcrumb[htmlspecialchars($mail->name)] = acym_completeLink('mails&task=edit&id='.$mail->id);
            } else {
                $return = empty($return) ? $_SERVER['HTTP_REFERER'] : $return;

                $notifName = acym_translation('ACYM_NOTIFICATIION_'.strtoupper(substr($mail->name, 4)));
                if (strpos($notifName, 'ACYM_NOTIFICATIION_') !== false) {
                    $notifName = $mail->name;
                }
                $this->breadcrumb[htmlspecialchars($notifName)] = acym_completeLink('mails&task=edit&notification='.$mail->name.'&return='.urlencode($return));
            }


            if (strpos($mail->stylesheet, '[class="') !== false) {
                acym_enqueueMessage(acym_translation('ACYM_WARNING_STYLESHEET_NOT_CORRECT'), 'warning');
            }
        }

        $data = array(
            "mail" => $mail,
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'config' => acym_config(),
        );

        if (!empty($return)) {
            $data['return'] = $return;
        }

        acym_setVar("layout", "edit");
        parent::display($data);
    }

    public function editor_wysid()
    {
        acym_setVar("layout", "editor_wysid");

        parent::display();
    }

    public function store()
    {
        acym_checkToken();

        $mailClass = acym_get('class.mail');
        $formData = acym_getVar('array', 'mail', array());
        $mail = new stdClass();
        $allowedFields = acym_getColumns('mail');
        $return = acym_getVar('string', 'return');
        $fromAutomation = false;
        if (!empty($return) && strpos($return, 'automation') !== false) $fromAutomation = true;
        foreach ($formData as $name => $data) {
            if (!in_array($name, $allowedFields)) {
                continue;
            }
            $mail->{$name} = $data;
        }

        if ($fromAutomation) {
            acym_setVar('from', $mail->id);
            acym_setVar('type', 'automation');
            acym_setVar('type_editor', 'acyEditor');
        }

        if (empty($mail->subject) && !empty($mail->type) && $mail->type != 'standard') {
            return false;
        }

        $mail->tags = acym_getVar("array", "template_tags", array());
        $mail->body = acym_getVar('string', 'editor_content', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->settings = acym_getVar('string', 'editor_settings', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->stylesheet = acym_getVar('string', 'editor_stylesheet', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->thumbnail = $fromAutomation ? '' : acym_getVar('string', 'editor_thumbnail', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->template = $fromAutomation ? 2 : 1;
        $mail->library = 0;
        $mail->drag_editor = strpos($mail->body, 'acym__wysid__template') === false ? 0 : 1;
        if (empty($mail->id)) {
            $mail->creation_date = date("Y-m-d H:i:s");
        }
        $mailID = $mailClass->save($mail);
        if (!empty($mailID)) {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            if ($fromAutomation) {
                acym_setVar('from', $mailID);
                acym_setVar('type', 'automation');
                acym_setVar('type_editor', 'acyEditor');
            } else {
                acym_setVar('mailID', $mailID);
            }

            return $mailID;
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);
            if (!empty($mailClass->errors)) {
                acym_enqueueNotification($mailClass->errors, 'error', 0);
            }

            return false;
        }
    }

    public function apply()
    {
        $this->store();
        $mailId = acym_getVar('int', 'mailID', 0);
        acym_setVar('id', $mailId);
        $this->edit();
    }

    public function save()
    {
        $mailid = $this->store();

        $return = str_replace('{mailid}', empty($mailid) ? '' : $mailid, acym_getVar('string', 'return'));
        if (empty($return)) {
            $this->listing();
        } else {
            acym_redirect($return);
        }
    }

    public function getTemplateAjax()
    {
        $searchFilter = acym_getVar('string', 'search', '');
        $tagFilter = acym_getVar('string', 'tag', 0);
        $ordering = 'creation_date';
        $orderingSortOrder = 'DESC';
        $type = acym_getVar('string', 'type', 'custom');
        $editor = acym_getVar('string', 'editor');

        $mailsPerPage = 12;
        $page = acym_getVar('int', 'pagination_page_ajax', 1);
        $page != 'undefined' ? : $page = '1';

        $mailClass = acym_get('class.mail');
        $matchingMails = $mailClass->getMatchingMails(
            array(
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'type' => $type,
                'editor' => $editor,
            )
        );

        $return = '<div class="grid-x grid-padding-x grid-padding-y grid-margin-x grid-margin-y xxlarge-up-6 large-up-4 medium-up-3 small-up-1 cell acym__template__choose__list">';

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }

            $return .= '<div class="cell grid-x acym__templates__oneTpl acym__listing__block" id="'.htmlspecialchars($oneTemplate->id).'">
                <div class="cell acym__templates__pic text-center">
                    <a href="'.acym_completeLink(acym_getVar('cmd', 'ctrl').'&task=edit&step=editEmail&from='.htmlspecialchars($oneTemplate->id), false, false, true);

            $return .= !empty($this->data['campaignInformation']) ? '&id='.$this->data['campaignInformation'] : '';

            $return .= '">
                        <img src="'.htmlspecialchars(((strpos($oneTemplate->thumbnail, 'default_template_thumbnail') === false && strpos($oneTemplate->thumbnail, 'default_template') === false) ? ACYM_TEMPLATE_THUMBNAILS.$oneTemplate->thumbnail : $oneTemplate->thumbnail)).'"/>
                    </a>';
            if ($oneTemplate->drag_editor) {
                $return .= '<div class="acym__templates__choose__ribbon ribbon">
                                    <div class="acym__templates__choose__ribbon__label acym__color__white acym__background-color__blue">AcyEditor</div>
                                </div>';
            }

            if (strlen($oneTemplate->name) > 55) {
                $oneTemplate->name = substr($oneTemplate->name, 0, 50).'...';
            }
            $return .= '</div>
                            <div class="cell grid-x acym__templates__footer text-center">
                                <div class="cell acym__templates__footer__title" title="<?php echo htmlspecialchars($oneTemplate->name); ?>">'.htmlspecialchars($oneTemplate->name).'</div>
                                <div class="cell">'.acym_date(htmlspecialchars($oneTemplate->creation_date), 'M. j, Y').'</div>
                            </div>
                        </div>';
        }

        $return .= '</div>';

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        $return .= $pagination->displayAjax();

        echo $return;
        exit;
    }

    public function getMailContent()
    {
        $mailClass = acym_get('class.mail');
        $from = acym_getVar('string', 'from', '');

        if (empty($from)) {
            echo 'error';
            exit;
        }

        $echo = $mailClass->getOneById($from);

        if ($echo->drag_editor == 0) {
            echo 'no_new_editor';
            exit;
        }

        $echo = array('mailSettings' => $echo->settings, 'content' => $echo->body, 'stylesheet' => $echo->stylesheet);

        $echo = json_encode($echo);

        echo $echo;
        exit;
    }

    public function test()
    {
        $this->store();
        $mailId = acym_getVar('int', 'mailID', 0);
        acym_setVar('id', $mailId);

        $mailClass = acym_get('class.mail');
        $mail = $mailClass->getOneById($mailId);

        if (empty($mail)) {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_NOT_FOUND'), 'error', 5000);

            return $this->edit();
        }

        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->autoAddUser = true;
        $mailerHelper->checkConfirmField = false;
        $mailerHelper->report = false;

        $currentEmail = acym_currentUserEmail();
        if ($mailerHelper->sendOne($mailId, $currentEmail)) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_SEND_SUCCESS', $mail->name, $currentEmail), 'info', 5000);
        } else {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_SEND_ERROR', $mail->name, $currentEmail), 'error', 5000);
        }

        $this->edit();
    }

    public function setNewThumbnail()
    {
        $contentThumbnail = acym_getVar('string', 'content', '');
        $file = acym_getVar('string', 'thumbnail', '');

        $config = acym_config();

        if (empty($file)) {
            $thumbNb = $config->get('numberThumbnail', 2);
            $file = 'thumbnail_'.($thumbNb + 1).'.png';
            $newConfig = new stdClass();
            $newConfig->numberThumbnail = $thumbNb + 1;
            $config->save($newConfig);
        }

        if (ACYM_CMS == 'WordPress') {
            $wpUploadFolder = wp_upload_dir();
            $uploadsFolder = $wpUploadFolder['basedir'].DS.'acymailing'.DS.'thumbnails'.DS;
        } else {
            $uploadsFolder = ACYM_MEDIA.'images'.DS.'thumbnails'.DS;
        }

        acym_createFolder($uploadsFolder);
        file_put_contents($uploadsFolder.$file, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $contentThumbnail)));
        echo $file;

        exit;
    }

    public function loadCSS()
    {
        $idMail = acym_getVar('int', 'id', 0);
        if (empty($idMail)) {
            exit;
        }

        $mailClass = acym_get('class.mail');
        $mail = $mailClass->getOneById($idMail);

        echo $mailClass->buildCSS($mail->stylesheet);
        exit;
    }

    public function doUploadTemplate()
    {
        $mailClass = acym_get('class.mail');
        $mailClass->doupload();

        $this->listing();
    }
}
