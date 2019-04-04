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

class CampaignsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_CAMPAIGNS')] = acym_completeLink('campaigns');
        $this->loadScripts = array(
            'edit' => array('colorpicker', 'datepicker', 'thumbnail', 'foundation-email', 'parse-css'),
            'save' => array('colorpicker', 'datepicker', 'thumbnail', 'foundation-email', 'parse-css'),
        );
        acym_setVar('edition', '1');
        header('X-XSS-Protection:0');
    }

    public function listing()
    {
        acym_setVar("layout", "listing");
        $status = acym_getVar('string', "campaigns_status", '');
        $searchFilter = acym_getVar('string', 'campaigns_search', '');
        $tagFilter = acym_getVar('string', 'campaigns_tag', '');
        $ordering = acym_getVar('string', 'campaigns_ordering', 'id');
        $orderingSortOrder = acym_getVar('string', 'campaigns_ordering_sort_order', 'desc');

        $campaignsPerPage = acym_getCMSConfig('list_limit', 20);
        $page = acym_getVar('int', 'campaigns_pagination_page', 1);

        $campaignClass = acym_get('class.campaign');
        $matchingCampaigns = $campaignClass->getMatchingCampaigns(
            array(
                'ordering' => $ordering,
                'search' => $searchFilter,
                'campaignsPerPage' => $campaignsPerPage,
                'offset' => ($page - 1) * $campaignsPerPage,
                'tag' => $tagFilter,
                'ordering_sort_order' => $orderingSortOrder,
                'status' => $status,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingCampaigns['total'], $page, $campaignsPerPage);

        $data = array(
            'allCampaigns' => $matchingCampaigns['campaigns'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'allStatusFilter' => $this->getCountStatusFilter($matchingCampaigns['campaigns']),
            'pagination' => $pagination,
            "search" => $searchFilter,
            'ordering' => $ordering,
            'status' => $status,
            'tag' => $tagFilter,
            'orderingSortOrder' => $orderingSortOrder,
        );

        parent::display($data);
    }

    public function chooseTemplate()
    {
        acym_setVar('layout', 'choose_email');
        acym_setVar('step', 'chooseTemplate');

        $campaignId = acym_getVar("int", "id", 0);
        $campaignClass = acym_get('class.campaign');
        $searchFilter = acym_getVar('string', 'mailchoose_search', '');
        $tagFilter = acym_getVar('string', 'mailchoose_tag', '');
        $ordering = acym_getVar('string', 'mailchoose_ordering', 'creation_date');
        $orderingSortOrder = acym_getVar('string', 'mailchoose_ordering_sort_order', 'DESC');
        $type = acym_getVar('string', 'mailchoose_type', 'custom');
        $campaign = $campaignClass->getOneByIdWithMail($campaignId);

        if (!empty($campaign)) {
            $this->breadcrumb[htmlspecialchars($campaign->name)] = '';
        } else {
            $this->breadcrumb[acym_translation('ACYM_NEW_CAMPAIGN')] = '';
        }

        if (!empty($campaign->sent)) {
            $this->summary();

            return;
        }

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
                'onlyStandard' => true,
            )
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }

        $data = array(
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'type' => $type,
            'campaignID' => $campaignId,
        );


        parent::display($data);
    }

    public function editEmail()
    {
        acym_setVar('layout', 'edit_email');
        acym_setVar('numberattachment', '0');
        acym_setVar('step', 'editEmail');

        $editor = acym_get('helper.editor');
        $mailClass = acym_get('class.mail');
        $mailId = acym_getVar("int", "from", 0);
        $campaignId = acym_getVar("int", "id", 0);
        $typeEditor = acym_getVar('string', 'type_editor', '');

        $editLink = 'campaigns&task=edit&step=editEmail';

        if (empty($campaignId)) {
            $campaign = new stdClass();
            $campaign->id = 0;
            $campaign->name = '';
            $campaign->tags = array();
            $campaign->subject = '';
            $campaign->body = '';
            $campaign->settings = null;
        } else {
            $campaignClass = acym_get('class.campaign');
            $campaign = $campaignClass->getOneByIdWithMail($campaignId);
            if (empty($mailId)) {
                $mailId = $campaign->mail_id;
            }
            $editLink .= '&id='.$campaignId;
        }

        if (!empty($campaign->sent)) {
            $this->summary();

            return;
        }

        if ($mailId == -1) {
            $campaign->name = '';
            $campaign->tags = array();
            $campaign->subject = '';
            $campaign->body = '';
            $campaign->settings = null;
            $campaign->attachments = array();
            $campaign->stylesheet = '';
        } elseif (!empty($mailId)) {
            $mail = $mailClass->getOneById($mailId);
            $campaign->tags = $mail->tags;
            $campaign->subject = $mail->subject;
            $campaign->body = $mail->body;
            $campaign->settings = $mail->settings;
            $campaign->stylesheet = $mail->stylesheet;
            if (!empty($mail->attachments)) {
                $campaign->attachments = json_decode($mail->attachments);
            } else {
                $campaign->attachments = array();
            }
            if (empty($campaignId)) {
                $editLink .= '&from='.$mailId;
            }
        }
        $this->breadcrumb[htmlspecialchars($campaign->name)] = acym_completeLink($editLink);

        $editor->content = $campaign->body;
        if (!empty($campaign->settings)) {
            $editor->settings = $campaign->settings;
        }

        $maxupload = (acym_bytes(ini_get('upload_max_filesize')) > acym_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

        if (!empty($campaign->stylesheet)) {
            $editor->stylesheet = $campaign->stylesheet;
        }
        if (!empty($typeEditor)) {
            $editor->editor = $typeEditor;
        } else if (strpos($editor->content, 'acym__wysid__template') !== false) {
            $editor->editor = 'acyEditor';
        }

        $needDisplayStylesheet = ($editor->editor != 'acyEditor' || empty($editor->editor)) ? '<input type="hidden" name="editor_stylesheet" value="'.$campaign->stylesheet.'">' : '';

        $editor->mailId = empty($mailId) ? 0 : $mailId;

        $data = array(
            'campaignID' => $campaign->id,
            'mailInformation' => $campaign,
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'editor' => $editor,
            'maxupload' => $maxupload,
            'needDisplayStylesheet' => $needDisplayStylesheet,
        );

        parent::display($data);
    }

    public function recipients()
    {
        acym_setVar("layout", "recipients");
        $campaignId = acym_getVar("int", "id");
        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        acym_setVar('step', 'recipients');

        if (!empty($campaignId)) {
            $currentCampaign = $campaignClass->getOneByIdWithMail($campaignId);
            $this->breadcrumb[htmlspecialchars($currentCampaign->name)] = acym_completeLink('campaigns&task=edit&step=recipients&id='.$campaignId);
        } else {
            $currentCampaign = new stdClass();
            $this->breadcrumb[acym_translation('ACYM_NEW_CAMPAIGN')] = acym_completeLink('campaigns&task=edit&step=recipients');
        }

        if (!empty($currentCampaign->sent)) {
            $this->summary();

            return;
        }

        $campaign = array(
            'campaignInformation' => $campaignId,
        );

        if (!empty($currentCampaign->mail_id)) {
            $campaignLists = $mailClass->getAllListsByMailId($currentCampaign->mail_id);
            $campaign['campaignListsId'] = array();
            foreach ($campaignLists as $campaignList) {
                $campaign['campaignListsId'][] = $campaignList->id;
            }
            $campaign['campaignListsSelected'] = json_encode($campaign['campaignListsId']);
        }

        parent::display($campaign);
    }

    public function sendSettings()
    {
        acym_setVar("layout", "send_settings");
        acym_setVar('step', 'sendSettings');
        $campaignId = acym_getVar("int", "id");
        $campaignClass = acym_get('class.campaign');
        $campaignInformation = empty($campaignId) ? null : $campaignClass->getOneById($campaignId);

        if (is_null($campaignInformation)) {
            acym_enqueueNotification(acym_translation("ACYM_CANT_GET_CAMPAIGN_INFORMATION"), 'error', 0);
            $this->listing();

            return;
        }

        $from = acym_getVar("string", "from");
        $config = acym_config();

        $campaignClass = acym_get('class.campaign');
        $currentCampaign = $campaignClass->getOneByIdWithMail($campaignId);
        $this->breadcrumb[htmlspecialchars($currentCampaign->name)] = acym_completeLink('campaigns&task=edit&step=sendSettings&id='.$campaignId);

        if (!empty($currentCampaign->sent)) {
            $this->summary();

            return;
        }


        if (!empty(acym_getVar("array", "lists"))) {
            $this->addRecipients();
        }

        $campaign = array();

        $campaign['currentCampaign'] = $currentCampaign;
        $campaign['from'] = $from;
        $campaign['suggestedDate'] = acym_date('1534771620', 'j M Y H:i');
        $campaign['senderInformations'] = new stdClass();
        $campaign['config_values'] = new stdClass();

        empty($currentCampaign->from_name) ? $campaign['senderInformations']->from_name = '' : $campaign['senderInformations']->from_name = $currentCampaign->from_name;
        empty($currentCampaign->from_email) ? $campaign['senderInformations']->from_email = '' : $campaign['senderInformations']->from_email = $currentCampaign->from_email;
        empty($currentCampaign->reply_to_name) ? $campaign['senderInformations']->reply_to_name = '' : $campaign['senderInformations']->reply_to_name = $currentCampaign->reply_to_name;
        empty($currentCampaign->reply_to_email) ? $campaign['senderInformations']->reply_to_email = '' : $campaign['senderInformations']->reply_to_email = $currentCampaign->reply_to_email;

        $campaign['config_values']->from_name = empty($config->get('from_name')) ? '' : $config->get('from_name');
        $campaign['config_values']->from_email = empty($config->get('from_email')) ? '' : $config->get('from_email');
        $campaign['config_values']->reply_to_name = empty($config->get('replyto_name')) ? '' : $config->get('replyto_name');
        $campaign['config_values']->reply_to_email = empty($config->get('replyto_email')) ? '' : $config->get('replyto_email');

        return parent::display($campaign);
    }

    public function saveEditEmail()
    {
        acym_checkToken();

        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $formData = acym_getVar('array', 'mail', array());
        $allowedFields = acym_getColumns('mail');
        $campaignId = acym_getVar("int", "id", 0);

        if (empty($campaignId)) {
            $mail = new stdClass();
            $mail->creation_date = date("Y-m-d H:i:s");
            $mail->type = 'standard';
            $mail->template = 0;
            $mail->library = 0;

            $campaign = new stdClass();
            $campaign->draft = 1;
            $campaign->active = 1;
            $campaign->scheduled = 0;
            $campaign->sent = 0;
        } else {
            $campaign = $campaignClass->getOneById($campaignId);
            $mail = $mailClass->getOneById($campaign->mail_id);
        }

        foreach ($formData as $name => $data) {
            if (!in_array($name, $allowedFields)) {
                continue;
            }
            $mail->{acym_secureDBColumn($name)} = $data;
        }

        if (empty($mail->name)) {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);

            return $this->listing();
        }

        $mail->body = acym_getVar('string', 'editor_content', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->settings = acym_getVar('string', 'editor_settings', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->stylesheet = acym_getVar('string', 'editor_stylesheet', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->drag_editor = strpos($mail->body, 'acym__wysid__template') === false ? 0 : 1;

        $mail->tags = acym_getVar("array", "template_tags", array());

        $newAttachments = array();
        $attachments = acym_getVar('array', 'attachments', array());
        $config = acym_config();
        if (!empty($attachments)) {
            foreach ($attachments as $id => $filepath) {
                if (empty($filepath)) {
                    continue;
                }
                $attachment = new stdClass();
                $attachment->filename = $filepath;
                $attachment->size = filesize(ACYM_ROOT.$filepath);
                $extension = substr($attachment->filename, strrpos($attachment->filename, '.'));

                if (preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $attachment->filename)) {
                    acym_enqueueMessage(acym_translation_sprintf('ACYM_ACCEPTED_TYPE', substr($attachment->filename, strrpos($attachment->filename, '.') + 1), $config->get('allowed_files')), 'notice');
                    continue;
                }
                $attachment->filename = str_replace(array('.', ' '), '_', substr($attachment->filename, 0, strpos($attachment->filename, $extension))).$extension;

                $newAttachments[] = $attachment;
            }
            if (!empty($mail->attachments) && is_array(json_decode($mail->attachments))) {
                $newAttachments = array_merge(json_decode($mail->attachments), $newAttachments);
            }
            $mail->attachments = $newAttachments;
        }

        if (empty($mail->attachments)) {
            unset($mail->attachments);
        }
        if (!empty($mail->attachments) && !is_string($mail->attachments)) {
            $mail->attachments = json_encode($mail->attachments);
        }

        if ($mailID = $mailClass->save($mail)) {
            if (acym_getVar('string', 'nextstep', '') == 'listing') {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            }
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);
            if (!empty($mailClass->errors)) {
                acym_enqueueNotification($mailClass->errors, 'error', 0);
            }

            return $this->listing();
        }

        $campaign->mail_id = $mailID;
        $campaign->id = $campaignClass->save($campaign);

        acym_setVar("id", $campaign->id);

        $this->edit();
    }

    public function saveRecipients()
    {
        $allLists = json_decode(acym_getVar("string", "lists_selected"));
        $campaignId = acym_getVar("int", "id");

        $campaignClass = acym_get('class.campaign');
        $currentCampaign = $campaignClass->getOneByIdWithMail($campaignId);
        if (!empty($currentCampaign->mail_id)) {
            $campaignClass->manageListsToCampaign($allLists, $currentCampaign->mail_id);
            if (acym_getVar('string', 'nextstep', '') == 'listing') {
                acym_enqueueNotification(acym_translation_sprintf("ACYM_LIST_IS_SAVED", $currentCampaign->name), 'success', 8000);
            }
        }

        $this->edit();
    }

    public function saveSendSettings()
    {
        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $campaignId = acym_getVar('int', 'id');
        $senderInformation = acym_getVar('', 'senderInformation');
        $isScheduled = acym_getVar('string', 'isScheduled');
        $sendingDate = acym_getVar('string', 'sendingDate');

        $campaignInformation = $campaignClass->getOneById($campaignId);

        if (is_null($campaignInformation)) {
            acym_enqueueNotification(acym_translation("ACYM_CAMPAIGN_DOESNT_EXISTS"), 'error', 0);

            return $this->listing();
        }
        $currentCampaign = $campaignClass->getOneById($campaignId);
        empty($currentCampaign->mail_id) ? : $currentMail = $mailClass->getOneById($currentCampaign->mail_id);

        if (empty($currentMail) || empty($senderInformation)) {
            return $this->listing();
        }

        $currentMail->from_name = $senderInformation['from_name'];
        $currentMail->from_email = $senderInformation['from_email'];
        $currentMail->reply_to_name = $senderInformation['reply_to_name'];
        $currentMail->reply_to_email = $senderInformation['reply_to_email'];
        $currentMail->bcc = $senderInformation['bcc'];


        $mailClass->save($currentMail);

        if (!empty($isScheduled)) {
            if ($isScheduled == 'true') {
                $currentCampaign->scheduled = 1;
                if (!empty($sendingDate)) {
                    if (acym_getTime($sendingDate) < time()) {
                        acym_enqueueNotification(acym_translation('ACYM_CANT_SET_DATE_IN_PAST'), 'error', 5000);

                        return $this->listing();
                    } else {
                        $currentCampaign->sending_date = acym_date(acym_getTime($sendingDate), 'Y-m-d H:i:s', false);
                    }
                }
            } else {
                $currentCampaign->scheduled = 0;
                $currentCampaign->sending_date = null;
            }
        } else {
            return $this->listing();
        }

        if ($campaignClass->save($currentCampaign)) {
            if (acym_getVar('string', 'nextstep', '') == 'listing') {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            }
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);
            if (!empty($campaignClass->errors)) {
                acym_enqueueNotification($campaignClass->errors, 'error', 0);
            }

            return $this->listing();
        }

        $this->edit();
    }

    public function duplicate()
    {
        $campaignSelected = acym_getVar('int', 'elements_checked')[0];

        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $tagCalss = acym_get('class.tag');

        $campaign = $campaignClass->getOneById($campaignSelected);

        unset($campaign->id);
        $campaign->draft = 1;
        $campaign->sent = 0;

        $mail = $mailClass->getOneById($campaign->mail_id);
        $oldMailId = $mail->id;
        unset($mail->id);
        $mail->creation_date = date("Y-m-d H:i:s");
        $mail->name .= '_copy';
        $idNewMail = $mailClass->save($mail);

        $campaign->mail_id = $idNewMail;
        $campaignIdNew = $campaignClass->save($campaign);

        $allLists = $campaignClass->getListsForCampaign($oldMailId);

        $campaignClass->manageListsToCampaign($allLists, $idNewMail);

        $this->listing();

        return;
    }

    public function saveSummary()
    {
        $this->edit();
    }

    public function summary()
    {
        acym_setVar('step', 'summary');
        acym_setVar("layout", "summary");
        $campaignId = acym_getVar('int', 'id');
        $campaignClass = acym_get('class.campaign');

        $campaign = empty($campaignId) ? null : $campaignClass->getOneByIdWithMail($campaignId);

        if (is_null($campaign)) {
            acym_enqueueNotification(acym_translation("ACYM_CANT_GET_CAMPAIGN_INFORMATION"), 'error', 0);
            $this->listing();

            return;
        }

        $userClass = acym_get('class.user');
        $mailClass = acym_get('class.mail');
        $config = acym_config();
        $nbSubscribers = 0;

        $campaignLists = $mailClass->getAllListsWithCountSubscribersByMailIds([$campaign->mail_id]);
        $mailData = $mailClass->getOneById($campaign->mail_id);

        if (!empty($campaignLists)) {
            $listsIds = array();
            foreach ($campaignLists as $oneList) {
                $listsIds[] = $oneList->list_id;
            }
            $listClass = acym_get('class.list');
            $nbSubscribers = $listClass->getSubscribersCount($listsIds);
        }

        $mailData->from_name = empty($mailData->from_name) ? $config->get('from_name') : $mailData->from_name;
        $mailData->from_email = empty($mailData->from_email) ? $config->get('from_email') : $mailData->from_email;

        if (!empty($mailData->reply_to_name)) {
            $replytoName = $mailData->reply_to_name;
        } elseif ($config->get('from_as_replyto') == 0 && !empty($config->get('replyto_name'))) {
            $replytoName = $config->get('replyto_name');
        } else {
            $replytoName = $config->get('from_name');
        }
        if (!empty($mailData->reply_to_email)) {
            $replytoEmail = $mailData->reply_to_email;
        } elseif ($config->get('from_as_replyto') == 0 && !empty($config->get('replyto_email'))) {
            $replytoEmail = $config->get('replyto_email');
        } else {
            $replytoEmail = $config->get('from_email');
        }
        $mailData->reply_to_name = $replytoName;
        $mailData->reply_to_email = $replytoEmail;

        $campaignType = empty($campaign->scheduled) ? "now" : "scheduled";

        acym_trigger('replaceContent', array(&$mailData, false));
        $receiver = $userClass->getOneByEmail(acym_currentUserEmail());
        if (empty($receiver)) {
            $receiver = new stdClass();
            $receiver->email = acym_currentUserEmail();
            $newID = $userClass->save($receiver);
            $receiver = $userClass->getOneById($newID);
        }
        acym_trigger('replaceUserInformation', array(&$mailData, &$receiver, false));

        $data = array(
            'campaignInformation' => $campaign,
            'mailInformation' => $mailData,
            'listsReceiver' => $campaignLists,
            'nbSubscribers' => $nbSubscribers,
            'campaignType' => $campaignType,
        );

        $this->breadcrumb[htmlspecialchars($campaign->name)] = acym_completeLink('campaigns&task=edit&step=summary&id='.$campaign->id);
        parent::display($data);
    }

    public function stopSending()
    {
        acym_checkToken();

        $campaignID = acym_getVar('int', 'stopSendingCampaignId');
        $campaignClass = acym_get('class.campaign');

        if (!empty($campaignID)) {
            $campaign = new stdClass();
            $campaign->id = $campaignID;
            $campaign->active = 0;
            $campaign->draft = 1;

            if (!empty($campaignClass->save($campaign))) {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            } else {
                acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
            }
        } else {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
        }
        $this->listing();
    }

    public function stopScheduled()
    {
        acym_checkToken();

        $campaignID = acym_getVar('int', 'stopScheduledCampaignId');
        $campaignClass = acym_get('class.campaign');

        if (!empty($campaignID)) {
            $campaign = new stdClass();
            $campaign->id = $campaignID;
            $campaign->active = 0;
            $campaign->draft = 1;

            if (!empty($campaignClass->save($campaign))) {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            } else {
                acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
            }
        } else {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
        }
        $this->listing();
    }

    public function confirmCampaign()
    {
        $campaignId = acym_getVar('int', 'id');
        $campaignSendingDate = acym_getVar('string', 'sending_date');

        $campaignClass = acym_get('class.campaign');

        $campaign = new stdClass();
        $campaign->id = $campaignId;
        $campaign->draft = 0;
        $campaign->active = 1;

        $resultSave = $campaignClass->save($campaign);

        if ($resultSave) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_CONFIRMED_CAMPAIGN', acym_date($campaignSendingDate, 'j F Y H:s')), 'success', 8000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_CANT_CONFIRM_CAMPAIGN').' : '.end($campaignClass->errors), 'error', 0);
        }

        $this->listing();
    }

    public function saveAsDraftCampaign()
    {
        $campaignId = acym_getVar('int', 'id');
        $campaignClass = acym_get('class.campaign');

        $campaign = new stdClass();
        $campaign->id = $campaignId;
        $campaign->draft = 1;
        $campaign->active = 0;

        $resultSave = $campaignClass->save($campaign);

        if ($resultSave) {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_SUCCESSFULLY_SAVE_AS_DRAFT'), 'success', 8000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED').' : '.end($campaignClass->errors), 'error', 0);
        }

        $this->listing();
    }

    public function getAll()
    {
        $campaignClass = acym_get('class.campaign');
        $listClass = acym_get('class.list');

        $allCampaigns = $campaignClass->getAll();

        foreach ($allCampaigns as $campaign) {
            $campaign->tags = $campaignClass->getAllTagsByCampaignId($campaign->id);
            if (!empty($campaignClass->getAllListsByCampaignId($campaign->id)[0]->name)) {
                $campaign->lists = $campaignClass->getAllListsByCampaignId($campaign->id);
                $campaign->subscribers = 0;
                foreach ($campaign->lists as $list) {
                    $campaign->subscribers += $listClass->getSubscribersCountByListId($list->id);
                }
            }
            $campaign->trigger = $campaignClass->getAllTriggerByCampaignId($campaign->id);
            if (empty($campaign->trigger->automation_id)) {
                $campaign->trigger = null;
            }

            $campaign->sending = 0;
        }

        return $allCampaigns;
    }

    public function getCountStatusFilter($allCampaigns)
    {
        $allCountStatus = new stdClass();
        $allCountStatus->sending = 0;
        $allCountStatus->scheduled = 0;
        $allCountStatus->sent = 0;
        $allCountStatus->draft = 0;

        foreach ($allCampaigns as $campaign) {
            $allCountStatus->scheduled += $campaign->scheduled;
            $allCountStatus->sent += $campaign->sent;
            $allCountStatus->draft += $campaign->draft;
        }

        return $allCountStatus;
    }

    public function cancelDashboardAndGetCampaignsAjax()
    {
        $campaignId = acym_getVar('int', 'id');
        $campaignClass = acym_get('class.campaign');

        if (!empty($campaignId)) {
            $campaign = new stdClass();
            $campaign->id = $campaignId;
            $campaign->active = 0;
            $campaign->draft = 1;

            if (!empty($campaignClass->save($campaign))) {

                $campaigns = $campaignClass->getCampaignForDashboard();

                if (empty($campaigns)) {
                    echo '<h1 class="acym__dashboard__active-campaings__none">'.acym_translation('ACYM_NONE_OF_YOUR_CAMPAIGN_SCHEDULED_GO_SCHEDULE_ONE').'</h1>';
                    exit;
                }

                $echo = '';

                foreach ($campaigns as $campaign) {
                    $echo .= '<div class="cell grid-x acym__dashboard__active-campaings__one-campaing">
                        <a class="acym__dashboard__active-campaings__one-campaing__title medium-4 small-12" href="'.acym_completeLink('campaigns&task=edit&step=editEmail&id=').$campaign->id.'">'.$campaign->name.'</a>
                        <div class="acym__dashboard__active-campaings__one-campaing__state medium-2 small-12 acym__background-color__blue text-center"><span>'.acym_translation('ACYM_SCHEDULED').' : '.acym_getDate($campaign->sending_date, 'M. j, Y').'</span></div>
                        <div class="medium-6 small-12"><p id="'.$campaign->id.'" class="acym__dashboard__active-campaings__one-campaing__action acym__color__dark-gray">'.acym_translation('ACYM_CANCEL_SCHEDULING').'</p></div>
                    </div>
                    <hr class="cell small-12">';
                }
                echo $echo;
                exit;
            } else {
                echo 'error';
                exit;
            }
        } else {
            echo 'error';
            exit;
        }
    }

    public function addQueue()
    {
        acym_checkToken();

        $campaignID = acym_getVar('int', 'id', 0);

        if (empty($campaignID)) {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_NOT_FOUND'), 'error', 10000);
        } else {
            $campaignClass = acym_get('class.campaign');
            $campaign = $campaignClass->getOneByIdWithMail($campaignID);

            if ($campaign->sent) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_CAMPAIGN_ALREADY_SENT', $campaign->name), 'error', 10000);

                return $this->listing();
            }

            $status = $campaignClass->send($campaignID);

            if ($status) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_CAMPAIGN_ADDED_TO_QUEUE', $campaign->name), 'info');
            } else {
                if (empty($campaignClass->errors)) {
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_ERROR_QUEUE_CAMPAIGN', $campaign->name), 'error', 10000);
                } else {
                    acym_enqueueNotification($campaignClass->errors, 'error', 6000);
                }
            }
        }

        $this->listing();
    }

    public function countNumberOfRecipients()
    {
        $listsSelected = acym_getVar("array", "listsSelected", array());
        acym_arrayToInteger($listsSelected);

        $query = "SELECT COUNT(DISTINCT hasList.user_id) 
                    FROM #__acym_user_has_list AS hasList 
                    JOIN #__acym_user as user 
                        ON hasList.user_id = user.id
                    WHERE hasList.status = 1 
                        AND user.active = 1 
                        AND hasList.list_id IN ('".implode($listsSelected, "','")."')";

        $config = acym_config();
        if ($config->get('require_confirmation', 1) == 1) {
            $query .= ' AND user.confirmed = 1 ';
        }

        echo intval(acym_loadResult($query));
        exit;
    }

    public function deleteAttach()
    {
        $mailid = acym_getVar('int', 'mail', 0);
        $attachid = acym_getVar('int', 'id', 0);

        if (!empty($mailid) && $attachid >= 0) {
            $mailClass = acym_get('class.mail');

            return $mailClass->deleteOneAttachment($mailid, $attachid);
        } else {
            echo 'error';
        }
    }

    public function test()
    {
        $result = new stdClass();
        $result->type = 'info';
        $result->timer = 5000;
        $result->message = '';

        $campaignId = acym_getVar('int', 'id', 0);

        $campaignClass = acym_get('class.campaign');
        $campaign = $campaignClass->getOneById($campaignId);

        if (empty($campaign)) {
            $result->type = 'error';
            $result->timer = '';
            $result->message = acym_translation('ACYM_CAMPAIGN_NOT_FOUND');
            exit;
        }

        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->autoAddUser = true;
        $mailerHelper->checkConfirmField = false;
        $mailerHelper->report = false;


        $report = array();

        $testEmails = explode(',', acym_getVar('string', 'test_emails'));
        foreach ($testEmails as $oneAddress) {
            if (!$mailerHelper->sendOne($campaign->mail_id, $oneAddress, true)) {
                $result->type = 'error';
                $result->timer = '';
            }

            if (!empty($mailerHelper->reportMessage)) {
                $report[] = $mailerHelper->reportMessage;
            }
        }

        $result->message = implode('<br/>', $report);
        echo json_encode($result);
        exit;
    }

    public function tests()
    {
        $campaignClass = acym_get('class.campaign');
        acym_setVar('step', 'tests');
        acym_setVar('layout', 'tests');
        $campaignId = acym_getVar('int', 'id', 0);

        $campaign = $campaignClass->getOneByIdWithMail($campaignId);

        if (empty($campaign->id)) {
            acym_enqueueNotification(acym_translation("ACYM_CANT_GET_CAMPAIGN_INFORMATION"), 'error', 0);
            $this->listing();

            return;
        }

        $testEmails = acym_getVar('array', 'test_emails', array(acym_currentUserEmail()));
        foreach ($testEmails as $oneEmail) {
            $defaultEmails[$oneEmail] = $oneEmail;
        }

        $data = array(
            'id' => $campaign->id,
            'test_emails' => $defaultEmails,
            'upgrade' => !acym_level(2) ? true : false,
            'version' => 'enterprise',
        );

        $this->breadcrumb[htmlspecialchars($campaign->name)] = acym_completeLink('campaigns&task=edit&step=tests&id='.$campaign->id);
        parent::display($data);
    }

    public function saveTests()
    {
        $this->edit();
    }

    public function checkContent()
    {
        $campaignId = acym_getVar('int', 'id', 0);
        $campaignClass = acym_get('class.campaign');
        $campaign = $campaignClass->getOneByIdWithMail($campaignId);

        $spamWords = array(
            "4U",
            "you are a winner",
            "For instant access",
            "Accept credit cards",
            "Claims you registered with",
            "For just $",
            "Act now!",
            "Don’t hesitate!",
            "Click below",
            "Free access",
            "Additional income",
            "Click here",
            "Free cell phone",
            "Addresses on CD",
            "Click to remove",
            "Free consultation",
            "All natural",
            "Free DVD",
            "Amazing",
            "Compare rates",
            "Free grant money",
            "Apply Online",
            "Compete for your business",
            "Free hosting",
            "As seen on",
            "Confidentially on all orders",
            "Free installation",
            "Auto email removal",
            "Congratulations",
            "Free investment",
            "Avoid bankruptcy",
            "Consolidate debt and credit",
            "Free leads",
            "Be amazed",
            "Copy accurately",
            "Free membership",
            "Be your own boss",
            "Copy DVDs",
            "Free money",
            "Being a member",
            "Credit bureaus",
            "Free offer",
            "Big bucks",
            "Credit card offers",
            "Free preview",
            "Bill 1618",
            "Cures baldness",
            "Free priority mail",
            "Billing address",
            "Dear email",
            "Free quote",
            "Billion dollars",
            "Dear friend",
            "Free sample",
            "Brand new pager",
            "Dear somebody",
            "Free trial",
            "Bulk email",
            "Different reply to",
            "Free website",
            "Buy direct",
            "Dig up dirt on friends",
            "Full refund",
            "Buying judgments",
            "Direct email",
            "Get It Now",
            "Cable converter",
            "Direct marketing",
            "Get paid",
            "Call free",
            "Discusses search engine listings",
            "Get started now",
            "Call now",
            "Do it today",
            "Gift certificate",
            "Calling creditors",
            "Don’t delete",
            "Great offer",
            "Can’t live without",
            "Drastically reduced",
            "Guarantee",
            "Cancel at any time",
            "Earn per week",
            "Have you been turned down?",
            "Cannot be combined with any other offer",
            "Easy terms",
            "Hidden assets",
            "Cash bonus",
            "Eliminate bad credit",
            "Home employment",
            "Cashcashcash",
            "Email harvest",
            "Human growth hormone",
            "Casino",
            "Email marketing",
            "If only it were that easy",
            "Cell phone cancer scam",
            "Expect to earn",
            "In accordance with laws",
            "Cents on the dollar",
            "Fantastic deal",
            "Increase sales",
            "Check or money order",
            "Fast Viagra delivery",
            "Increase traffic",
            "Claims not to be selling anything",
            "Financial freedom",
            "Insurance",
            "Claims to be in accordance with some spam law",
            "Find out anything",
            "Investment decision",
            "Claims to be legal",
            "For free",
            "It's effective",
            "Join millions of Americans",
            "No questions asked",
            "Reverses aging",
            "Laser printer",
            "No selling",
            "Risk free",
            "Limited time only",
            "No strings attached",
            "Round the world",
            "Long distance phone offer",
            "Not intended",
            "S 1618",
            "Lose weight spam",
            "Off shore",
            "Safeguard notice",
            "Lower interest rates",
            "Offer expires",
            "Satisfaction guaranteed",
            "Lower monthly payment",
            "Offers coupon",
            "Save $",
            "Lowest price",
            "Offers extra cash",
            "Save big money",
            "Luxury car",
            "Offers free (often stolen) passwords",
            "Save up to",
            "Mail in order form",
            "Once in lifetime",
            "Score with babes",
            "Marketing solutions",
            "One hundred percent free",
            "Section 301",
            "Mass email",
            "One hundred percent guaranteed",
            "See for yourself",
            "Meet singles",
            "One time mailing",
            "Sent in compliance",
            "Member stuff",
            "Online biz opportunity",
            "Serious cash",
            "Message contains disclaimer",
            "Online pharmacy",
            "Serious only",
            "MLM",
            "Only $",
            "Shopping spree",
            "Money back",
            "Opportunity",
            "Sign up free today",
            "Money making",
            "Opt in",
            "Social security number",
            "Month trial offer",
            "Order now",
            "Special promotion",
            "More Internet traffic",
            "Order status",
            "Stainless steel",
            "Mortgage rates",
            "Orders shipped by priority mail",
            "Stock alert",
            "Multi level marketing",
            "Outstanding values",
            "Stock disclaimer statement",
            "Name brand",
            "Pennies a day",
            "Stock pick",
            "New customers only",
            "People just leave money laying around",
            "Stop snoring",
            "New domain extensions",
            "Please read",
            "Strong buy",
            "Nigerian",
            "Potential earnings",
            "Stuff on sale",
            "No age restrictions",
            "Print form signature",
            "Subject to credit",
            "No catch",
            "Print out and fax",
            "Supplies are limited",
            "No claim forms",
            "Produced and sent out",
            "Take action now",
            "No cost",
            "Profits",
            "Talks about hidden charges",
            "No credit check",
            "Promise you …!",
            "Talks about prizes",
            "No disappointment",
            "Pure profit",
            "Tells you it’s an ad",
            "No experience",
            "Real thing",
            "Terms and conditions",
            "No fees",
            "Refinance home",
            "The best rates",
            "No gimmick",
            "Removal instructions",
            "The following form",
            "No inventory",
            "Remove in quotes",
            "They keep your money — no refund!",
            "No investment",
            "Remove subject",
            "They’re just giving it away",
            "No medical exams",
            "Removes wrinkles",
            "This isn’t junk",
            "No middleman",
            "Reply remove subject",
            "This isn’t spam",
            "No obligation",
            "Requires initial investment",
            "University diplomas",
            "No purchase necessary",
            "Reserves the right",
            "Unlimited",
            "Unsecured credit/debt",
            "We honor all",
            "Will not believe your eyes",
            "Urgent",
            "Weekend getaway",
            "Winner",
            "US dollars",
            "What are you waiting for?",
            "Winning",
            "Vacation offers",
            "While supplies last",
            "Work at home",
            "Viagra and other drugs",
            "While you sleep",
            "You have been selected",
            "Wants credit card",
            "Who really wins?",
            "Your income",
            "We hate spam",
            "Why pay more?",
        );

        $errors = array();
        foreach ($spamWords as $oneWord) {
            if ((bool)preg_match('#'.preg_quote($oneWord, '#').'#Uis', $campaign->subject.$campaign->body)) {
                $errors[] = $oneWord;
            }
        }

        if (count($errors) > 2) {
            echo acym_translation('ACYM_TESTS_CONTENT_DESC');
            echo '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
        }
        exit;
    }

    public function checkLinks()
    {
        $campaignId = acym_getVar('int', 'id', 0);
        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $campaign = $campaignClass->getOneById($campaignId);
        $mail = $mailClass->getOneById($campaign->mail_id);

        acym_trigger('replaceContent', array(&$mail, false));
        $userClass = acym_get('class.user');
        $receiver = $userClass->getOneByEmail(acym_currentUserEmail());
        if (empty($receiver)) {
            $receiver = new stdClass();
            $receiver->email = acym_currentUserEmail();
            $newID = $userClass->save($receiver);
            $receiver = $userClass->getOneById($newID);
        }
        acym_trigger('replaceUserInformation', array(&$mail, &$receiver, false));

        preg_match_all('# (href|src)="([^"]+)"#Uis', acym_absoluteURL($mail->body), $URLs);

        $errors = array();
        $processed = array();
        foreach ($URLs[2] as $oneURL) {
            if (in_array($oneURL, $processed)) {
                continue;
            }
            $processed[] = $oneURL;

            $headers = @get_headers($oneURL);
            $headers = is_array($headers) ? implode("\n ", $headers) : $headers;

            if (empty($headers) || preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers) !== 1) {
                $errors[] = '<a target="_blank" href="'.$oneURL.'">'.(strlen($oneURL) > 50 ? substr($oneURL, 0, 25).'...'.substr($oneURL, strlen($oneURL) - 20) : $oneURL).'</a>';
            }
        }

        if (!empty($errors)) {
            echo '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
        }

        exit;
    }

    public function checkSPAM()
    {
        $result = new stdClass();
        $result->type = 'error';
        $result->message = '';

        $campaignId = acym_getVar('int', 'id', 0);
        $campaignClass = acym_get('class.campaign');
        $campaign = $campaignClass->getOneByIdWithMail($campaignId);

        if (empty($campaign->mail_id)) {
            $result->message = acym_translation('ACYM_CAMPAIGN_NOT_FOUND');
        } else {
            $config = acym_config();
            ob_start();
            $urlSite = trim(base64_encode(preg_replace('#https?://(www\.)?#i', '', ACYM_LIVE)), '=/');
            $url = ACYM_SPAMURL.'spamTestSystem&component=acymailing&level='.strtolower($config->get('level', 'starter')).'&urlsite='.$urlSite;
            $spamtestSystem = acym_fileGetContent($url, 30);
            $warnings = ob_get_clean();

            if (empty($spamtestSystem) || !empty($warnings)) {
                $result->message = acym_translation('ACYM_ERROR_LOAD_FROM_ACYBA').(!empty($warnings) && acym_isDebug() ? $warnings : '');
            } else {
                $decodedInformation = json_decode($spamtestSystem, true);
                if (!empty($decodedInformation['messages']) || !empty($decodedInformation['error'])) {
                    $msgError = empty($decodedInformation['messages']) ? '' : $decodedInformation['messages'].'<br />';
                    $msgError .= empty($decodedInformation['error']) ? '' : $decodedInformation['error'];
                    $result->message = $msgError;
                } else {
                    if (empty($decodedInformation['email'])) {
                        $result->message = acym_translation('ACYM_SPAMTEST_MISSING_EMAIL');
                    } else {
                        $mailerHelper = acym_get('helper.mailer');
                        $mailerHelper->checkConfirmField = false;
                        $mailerHelper->checkEnabled = false;
                        $mailerHelper->loadedToSend = true;
                        $mailerHelper->report = false;

                        $receiver = new stdClass();
                        $receiver->id = 0;
                        $receiver->email = $decodedInformation['email'];
                        $receiver->name = $decodedInformation['name'];
                        $receiver->confirmed = 1;
                        $receiver->enabled = 1;

                        if ($mailerHelper->sendOne($campaign->mail_id, $receiver)) {
                            $result->type = 'success';
                            $result->message = 'https://mailtester.acyba.com/'.(substr($decodedInformation['email'], 0, strpos($decodedInformation['email'], '@')));
                            $result->lang = acym_getLanguageTag();
                        } else {
                            $result->message = $mailerHelper->reportMessage;
                        }
                    }
                }
            }
        }

        echo json_encode($result);
        exit;
    }
}
