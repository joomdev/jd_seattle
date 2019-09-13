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

class MailsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $type = acym_getVar('string', 'type');
        $this->breadcrumb[acym_translation('automation' != $type ? 'ACYM_TEMPLATES' : 'ACYM_AUTOMATION')] = acym_completeLink('automation' != $type ? 'mails' : 'automation');
        $this->loadScripts = [
            'edit' => ['colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'],
            'apply' => ['colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'],
            'test' => ['colorpicker', 'datepicker', 'editor', 'thumbnail', 'foundation-email', 'parse-css'],
        ];
        header('X-XSS-Protection:0');
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');

        $searchFilter = acym_getVar('string', 'mails_search', '');
        $tagFilter = acym_getVar('string', 'mails_tag', '');
        $ordering = acym_getVar('string', 'mails_ordering', 'id');
        $status = acym_getVar('string', 'mails_status', 'standard');
        $orderingSortOrder = acym_getVar('string', 'mails_ordering_sort_order', 'desc');

        $mailsPerPage = 12;
        $page = acym_getVar('int', 'mails_pagination_page', 1);

        $mailClass = acym_get('class.mail');
        $matchingMails = $mailClass->getMatchingMails(
            [
                'ordering' => $ordering,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'status' => $status,
                'ordering_sort_order' => $orderingSortOrder,
            ]
        );

        $matchingMailsNb = count($matchingMails['mails']);

        if (empty($matchingMailsNb)) {
            if ($page > 1) {
                acym_setVar('mails_pagination_page', 1);
                $this->listing();

                return;
            } elseif (!empty($status)) {
                acym_setVar('mails_status', '');
                $this->listing();

                return;
            }
        }

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }

        $mailsData = [
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'status' => $status,
            'mailNumberPerStatus' => $matchingMails['status'],
            'orderingSortOrder' => $orderingSortOrder,
        ];
        parent::display($mailsData);

        return;
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
            [
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'type' => $type,
            ]
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }

        $mailsData = [
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'type' => $type,
        ];


        parent::display($mailsData);
    }

    public function edit()
    {
        $tempId = acym_getVar("int", "id");
        $mailClass = acym_get('class.mail');
        $typeEditor = acym_getVar('string', 'type_editor');
        $notification = acym_getVar("cmd", "notification");
        $return = acym_getVar('string', 'return', '');
        $return = empty($return) ? '' : urldecode($return);
        $type = acym_getVar('string', 'type');

        if (!empty($notification)) {
            $mail = $mailClass->getOneByName($notification);
            if (!empty($mail->id)) {
                $tempId = $mail->id;
            }
        }

        $isAutomationAdmin = false;

        if (strpos($type, 'automation') !== false || empty($tempId)) {
            $fromId = acym_getVar("int", "from");
            if ($type == 'automation_admin') {
                $type = 'automation';
                $isAutomationAdmin = true;
            }

            if (empty($fromId)) {
                $mail = new stdClass();
                $mail->name = '';
                $mail->subject = '';
                $mail->preheader = '';
                $mail->tags = [];
                $mail->type = '';
                $mail->body = '';
                $mail->editor = 'automation' == $type ? 'acyEditor' : $typeEditor;
                $mail->headers = '';
                $mail->thumbnail = null;
            } else {
                $mail = $mailClass->getOneById($fromId);
                if (0 == $mail->drag_editor) {
                    $mail->editor = 'html';
                } else {
                    $mail->editor = !empty($typeEditor) ? $typeEditor : 'acyEditor';
                }
            }

            if (!empty($type)) $mail->type = $type;

            if ('automation' != $type || empty($fromId)) $mail->id = 0;
            $this->breadcrumb[acym_translation('automation' != $type ? 'ACYM_CREATE_TEMPLATE' : 'ACYM_NEW_EMAIL')] = acym_completeLink('mails&task=edit&type_editor='.$typeEditor.(!empty($fromId) ? '&from='.$fromId : '').'&type='.$type.(!empty($return) ? '&return='.urlencode($return) : ''));
        } else {
            $mail = $mailClass->getOneById($tempId);
            $mail->editor = $mail->drag_editor == 0 ? 'html' : 'acyEditor';
            if (!empty($typeEditor)) $mail->editor = $typeEditor;

            if (empty($notification)) {
                $this->breadcrumb[acym_escape($mail->name)] = acym_completeLink('mails&task=edit&id='.$mail->id);
            } else {
                if (empty($return)) {
                    $return = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
                }

                $notifName = acym_translation('ACYM_NOTIFICATIION_'.strtoupper(substr($mail->name, 4)));
                if (strpos($notifName, 'ACYM_NOTIFICATIION_') !== false) {
                    $notifName = $mail->name;
                }
                $this->breadcrumb[acym_escape($notifName)] = acym_completeLink('mails&task=edit&notification='.$mail->name.'&return='.urlencode($return));
            }


            if (strpos($mail->stylesheet, '[class="') !== false) {
                acym_enqueueMessage(acym_translation('ACYM_WARNING_STYLESHEET_NOT_CORRECT'), 'warning');
            }
        }

        $config = acym_config();

        $data = [
            "mail" => $mail,
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'config' => acym_config(),
            'isAutomationAdmin' => $isAutomationAdmin,
            'social_icons' => $config->get('social_icons', '{}'),
        ];

        if (!empty($return)) $data['return'] = $return;

        acym_setVar("layout", "edit");
        parent::display($data);
    }

    public function editor_wysid()
    {
        acym_setVar("layout", "editor_wysid");

        parent::display();
    }

    public function store($ajax = false)
    {
        acym_checkToken();

        $mailClass = acym_get('class.mail');
        $formData = acym_getVar('array', 'mail', []);
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

        $mail->tags = acym_getVar("array", "template_tags", []);
        $mail->body = acym_getVar('string', 'editor_content', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->settings = acym_getVar('string', 'editor_settings', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->stylesheet = acym_getVar('string', 'editor_stylesheet', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->headers = acym_getVar('string', 'editor_headers', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->thumbnail = $fromAutomation ? '' : acym_getVar('string', 'editor_thumbnail', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->template = $fromAutomation ? 2 : 1;
        $mail->library = 0;
        $mail->drag_editor = strpos($mail->body, 'acym__wysid__template') === false ? 0 : 1;
        if ($fromAutomation) $mail->type = 'automation';
        if (empty($mail->id)) {
            $mail->creation_date = acym_date('now', 'Y-m-d H:i:s', false);
        }
        $mailID = $mailClass->save($mail);
        if (!empty($mailID)) {
            if (!$ajax) acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            if ($fromAutomation) {
                acym_setVar('from', $mailID);
                acym_setVar('type', 'automation');
                acym_setVar('type_editor', 'acyEditor');
            } else {
                acym_setVar('mailID', $mailID);
            }

            return $mailID;
        } else {
            if (!$ajax) acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);
            if (!empty($mailClass->errors)) {
                if (!$ajax) acym_enqueueNotification($mailClass->errors, 'error', 0);
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

    public function autoSave()
    {
        $mailClass = acym_get('class.mail');
        $mail = new stdClass();

        $mail->id = acym_getVar('int', 'mailId', 0);
        $mail->autosave = acym_getVar('string', 'autoSave', '', 'REQUEST', ACYM_ALLOWRAW);

        if (empty($mail->id) || !$mailClass->autoSave($mail)) {
            echo 'error';
        } else {
            echo 'saved';
        }

        exit;
    }

    public function getTemplateAjax()
    {
        $searchFilter = acym_getVar('string', 'search', '');
        $tagFilter = acym_getVar('string', 'tag', 0);
        $ordering = 'creation_date';
        $orderingSortOrder = 'DESC';
        $type = acym_getVar('string', 'type', 'custom');
        $editor = acym_getVar('string', 'editor');
        $automation = acym_getVar('string', 'automation');

        $mailsPerPage = 12;
        $page = acym_getVar('int', 'pagination_page_ajax', 1);
        $page != 'undefined' ? : $page = '1';

        $mailClass = acym_get('class.mail');
        $matchingMails = $mailClass->getMatchingMails(
            [
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'type' => $type,
                'editor' => $editor,
                'automation' => $automation,
            ]
        );

        $return = '<div class="grid-x grid-padding-x grid-padding-y grid-margin-x grid-margin-y xxlarge-up-6 large-up-4 medium-up-3 small-up-1 cell acym__template__choose__list">';

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }

            $return .= '<div class="cell grid-x acym__templates__oneTpl acym__listing__block" id="'.acym_escape($oneTemplate->id).'">
                <div class="cell acym__templates__pic text-center">';

            $thumbnail = $oneTemplate->thumbnail;
            if (strpos($oneTemplate->thumbnail, 'default_template') === false) {
                $thumbnail = ACYM_TEMPLATE_THUMBNAILS.$oneTemplate->thumbnail;
            }

            $url = acym_getVar('cmd', 'ctrl').'&task=edit&step=editEmail&from='.intval($oneTemplate->id);
            if (!empty($this->data['campaignInformation'])) $url .= '&id='.intval($this->data['campaignInformation']);
            $return .= '<a href="'.acym_completeLink($url, false, false, true).'">';


            $return .= '<img src="'.acym_escape($thumbnail).'" alt="template thumbnail"/>';
            $return .= '</a>';
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
                                <div class="cell acym__templates__footer__title" title="'.acym_escape($oneTemplate->name).'">'.acym_escape($oneTemplate->name).'</div>
                                <div class="cell">'.acym_date($oneTemplate->creation_date, 'M. j, Y').'</div>
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

        $echo = ['mailSettings' => $echo->settings, 'content' => $echo->body, 'stylesheet' => $echo->stylesheet];

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
            $this->edit();

            return;
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
        acym_checkToken();
        $contentThumbnail = acym_getVar('string', 'content', '');
        $file = acym_getVar('string', 'thumbnail', '');

        $config = acym_config();

        if (empty($file) || strpos($file, 'http') === 0) {
            $thumbNb = $config->get('numberThumbnail', 2);
            $file = 'thumbnail_'.($thumbNb + 1).'.png';
            $newConfig = new stdClass();
            $newConfig->numberThumbnail = $thumbNb + 1;
            $config->save($newConfig);
        }

        $extension = acym_fileGetExt($file);
        if (strpos($file, 'thumbnail_') === false || !in_array($extension, ['png', 'jpeg', 'jpg', 'gif'])) exit;

        acym_createFolder(ACYM_UPLOAD_FOLDER_THUMBNAIL);
        file_put_contents(ACYM_UPLOAD_FOLDER_THUMBNAIL.$file, base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $contentThumbnail)));
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

    public function setNewIconShare()
    {
        $socialName = acym_getVar('string', 'social', '');
        $extension = pathinfo($_FILES['file']['name']);
        $newPath = ACYM_UPLOAD_FOLDER.'socials'.DS.$socialName.'.'.$extension['extension'];

        if (!acym_uploadFile($_FILES['file']['tmp_name'], ACYM_ROOT.$newPath) || empty($socialName)) {
            echo 'error';
            exit;
        }

        $config = acym_config();
        $newConfig = new stdClass();
        $newConfig->social_icons = json_decode($config->get('social_icons', '{}'), true);

        $newImg = acym_rootURI().$newPath;

        $newConfig->social_icons[$socialName] = $newImg;
        $newConfig->social_icons = json_encode($newConfig->social_icons);
        $config->save($newConfig);

        echo $newImg;
        exit;
    }

    public function deleteMailAutomation()
    {
        $mailClass = acym_get('class.mail');
        $mailId = acym_getVar('int', 'id', 0);

        if (!empty($mailId)) $mailClass->delete($mailId);


        exit;
    }

    public function duplicateMailAutomation()
    {
        $mailClass = acym_get('class.mail');
        $mailId = acym_getVar('int', 'id', 0);
        $prevMail = acym_getVar('int', 'previousId');

        if (!empty($prevMail)) $mailClass->delete($prevMail);

        if (empty($mailId)) {
            echo json_encode(['error' => acym_translation_sprintf('ACYM_NOT_FOUND', acym_translation('ACYM_ID'))]);
            exit;
        }

        $mail = $mailClass->getOneById($mailId);

        if (empty($mail)) {
            echo json_encode(['error' => acym_translation_sprintf('ACYM_NOT_FOUND', acym_translation('ACYM_EMAIL'))]);
            exit;
        }

        $newMail = new stdClass();
        $newMail->name = $mail->name.'_copy';
        $newMail->thumbnail = '';
        $newMail->type = 'automation';
        $newMail->drag_editor = $mail->drag_editor;
        $newMail->library = 0;
        $newMail->body = $mail->body;
        $newMail->subject = $mail->subject;
        $newMail->template = 2;
        $newMail->from_name = $mail->from_name;
        $newMail->from_email = $mail->from_email;
        $newMail->reply_to_name = $mail->reply_to_name;
        $newMail->reply_to_email = $mail->reply_to_email;
        $newMail->bcc = $mail->bcc;
        $newMail->settings = $mail->settings;
        $newMail->stylesheet = $mail->stylesheet;
        $newMail->attachments = $mail->attachments;
        $newMail->headers = $mail->headers;

        $newMail->id = $mailClass->save($newMail);

        if (empty($newMail->id)) {
            echo json_encode(['error' => acym_translation('ACYM_COULD_NOT_DUPLICATE_EMAIL')]);
            exit;
        }

        echo json_encode($newMail);
        exit;
    }

    public function saveAjax()
    {
        $return = $this->store(true);
        echo json_encode(['error' => !$return ? acym_translation('ACYM_ERROR_SAVING') : '', 'data' => $return]);
        exit;
    }
}

