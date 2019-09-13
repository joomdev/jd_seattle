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

class CampaignsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_CAMPAIGNS')] = acym_completeLink('campaigns');
        $this->loadScripts = [
            'edit' => ['colorpicker', 'datepicker', 'thumbnail', 'foundation-email', 'parse-css'],
            'save' => ['colorpicker', 'datepicker', 'thumbnail', 'foundation-email', 'parse-css'],
        ];
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
            [
                'ordering' => $ordering,
                'search' => $searchFilter,
                'campaignsPerPage' => $campaignsPerPage,
                'offset' => ($page - 1) * $campaignsPerPage,
                'tag' => $tagFilter,
                'ordering_sort_order' => $orderingSortOrder,
                'status' => $status,
            ]
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingCampaigns['total'], $page, $campaignsPerPage);

        $data = [
            'allCampaigns' => $matchingCampaigns['campaigns'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'allStatusFilter' => $this->getCountStatusFilter($matchingCampaigns['campaigns']),
            'pagination' => $pagination,
            "search" => $searchFilter,
            'ordering' => $ordering,
            'status' => $status,
            'tag' => $tagFilter,
            'orderingSortOrder' => $orderingSortOrder,
        ];

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
            $this->breadcrumb[acym_escape($campaign->name)] = '';
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
            [
                'ordering' => $ordering,
                'ordering_sort_order' => $orderingSortOrder,
                'search' => $searchFilter,
                'mailsPerPage' => $mailsPerPage,
                'offset' => ($page - 1) * $mailsPerPage,
                'tag' => $tagFilter,
                'type' => $type,
                'onlyStandard' => true,
            ]
        );

        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($matchingMails['total'], $page, $mailsPerPage);

        foreach ($matchingMails['mails'] as $oneTemplate) {
            if (empty($oneTemplate->thumbnail)) {
                $oneTemplate->thumbnail = ACYM_IMAGES.'default_template_thumbnail.png';
            }
        }

        $data = [
            'allMails' => $matchingMails['mails'],
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'pagination' => $pagination,
            'search' => $searchFilter,
            'tag' => $tagFilter,
            'ordering' => $ordering,
            'type' => $type,
            'campaignID' => $campaignId,
        ];


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
        $config = acym_config();

        $editLink = 'campaigns&task=edit&step=editEmail';

        $checkAutosave = empty($mailId);

        if (empty($campaignId)) {
            $campaign = new stdClass();
            $campaign->id = 0;
            $campaign->name = '';
            $campaign->tags = [];
            $campaign->subject = '';
            $campaign->preheader = '';
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

        if (!empty($campaign->sent) && !empty($campaign->active)) {
            $this->summary();

            return;
        }

        if ($mailId == -1) {
            $campaign->name = '';
            $campaign->tags = [];
            $campaign->subject = '';
            $campaign->preheader = '';
            $campaign->body = '';
            $campaign->settings = null;
            $campaign->attachments = [];
            $campaign->stylesheet = '';
            $campaign->headers = '';
        } elseif (!empty($mailId)) {
            $mail = $mailClass->getOneById($mailId);
            $campaign->tags = $mail->tags;
            $campaign->subject = $mail->subject;
            $campaign->preheader = $mail->preheader;
            $campaign->body = $mail->body;
            $campaign->settings = $mail->settings;
            $campaign->stylesheet = $mail->stylesheet;
            $campaign->headers = $mail->headers;
            $campaign->attachments = empty($mail->attachments) ? [] : json_decode($mail->attachments);

            if (empty($campaignId)) {
                $editLink .= '&from='.$mailId;
            }
            if ($checkAutosave) {
                $campaign->autosave = $mail->autosave;
            }
        }
        $this->breadcrumb[acym_escape(empty($campaign->name) ? acym_translation('ACYM_NEW_CAMPAIGN') : $campaign->name)] = acym_completeLink($editLink);

        $pluginHelper = acym_get('helper.plugin');
        $pluginHelper->cleanHtml($campaign->body);
        $editor->content = $campaign->body;
        $editor->autoSave = !empty($campaign->autosave) ? $campaign->autosave : '';
        if (!empty($campaign->settings)) {
            $editor->settings = $campaign->settings;
        }

        if (acym_bytes(ini_get('upload_max_filesize')) > acym_bytes(ini_get('post_max_size'))) {
            $maxupload = ini_get('post_max_size');
        } else {
            $maxupload = ini_get('upload_max_filesize');
        }

        if (!empty($campaign->stylesheet)) {
            $editor->stylesheet = $campaign->stylesheet;
        }

        if (empty($typeEditor) && strpos($editor->content, 'acym__wysid__template') !== false) {
            $typeEditor = 'acyEditor';
        }

        $editor->editor = $typeEditor;
        if ($editor->editor != 'acyEditor' || empty($editor->editor)) {
            if (!isset($campaign->stylesheet)) $campaign->stylesheet = '';
            $needDisplayStylesheet = '<input type="hidden" name="editor_stylesheet" value="'.acym_escape($campaign->stylesheet).'">';
        } else {
            $needDisplayStylesheet = '';
        }

        $editor->mailId = empty($mailId) ? 0 : $mailId;

        $data = [
            'campaignID' => $campaign->id,
            'mailInformation' => $campaign,
            'allTags' => acym_get('class.tag')->getAllTagsByType('mail'),
            'editor' => $editor,
            'maxupload' => $maxupload,
            'needDisplayStylesheet' => $needDisplayStylesheet,
            'social_icons' => $config->get('social_icons', '{}'),
        ];

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
            $this->breadcrumb[acym_escape($currentCampaign->name)] = acym_completeLink('campaigns&task=edit&step=recipients&id='.$campaignId);
        } else {
            $currentCampaign = new stdClass();
            $this->breadcrumb[acym_translation('ACYM_NEW_CAMPAIGN')] = acym_completeLink('campaigns&task=edit&step=recipients');
        }

        if (!empty($currentCampaign->sent) && !empty($currentCampaign->active)) {
            $this->summary();

            return;
        }

        $campaign = [
            'campaignInformation' => $campaignId,
            'currentCampaign' => $currentCampaign,
        ];

        if (!empty($currentCampaign->mail_id)) {
            $campaignLists = $mailClass->getAllListsByMailId($currentCampaign->mail_id);
            $campaign['campaignListsId'] = array_keys($campaignLists);
            $campaign['campaignListsSelected'] = json_encode(acym_escapeArrayValues($campaign['campaignListsId']));
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
        $this->breadcrumb[acym_escape($currentCampaign->name)] = acym_completeLink('campaigns&task=edit&step=sendSettings&id='.$campaignId);

        if (!empty($currentCampaign->sent) && !empty($currentCampaign->active)) {
            $this->summary();

            return;
        } elseif (!empty($currentCampaign->sent) && empty($currentCampaign->active)) {
            $currentCampaign->sending_date = '';
        }


        $lists = acym_getVar('array', 'lists');
        if (!empty($lists)) {
            $this->addRecipients();
        }

        $campaign = [];

        $campaign['currentCampaign'] = $currentCampaign;
        $campaign['from'] = $from;
        $campaign['suggestedDate'] = acym_date('1534771620', 'j M Y H:i');
        $campaign['senderInformations'] = new stdClass();
        $campaign['config_values'] = new stdClass();

        empty($currentCampaign->from_name) ? $campaign['senderInformations']->from_name = '' : $campaign['senderInformations']->from_name = $currentCampaign->from_name;
        empty($currentCampaign->from_email) ? $campaign['senderInformations']->from_email = '' : $campaign['senderInformations']->from_email = $currentCampaign->from_email;
        empty($currentCampaign->reply_to_name) ? $campaign['senderInformations']->reply_to_name = '' : $campaign['senderInformations']->reply_to_name = $currentCampaign->reply_to_name;
        empty($currentCampaign->reply_to_email) ? $campaign['senderInformations']->reply_to_email = '' : $campaign['senderInformations']->reply_to_email = $currentCampaign->reply_to_email;

        $campaign['config_values']->from_name = $config->get('from_name', '');
        $campaign['config_values']->from_email = $config->get('from_email', '');
        $campaign['config_values']->reply_to_name = $config->get('replyto_name', '');
        $campaign['config_values']->reply_to_email = $config->get('replyto_email', '');

        return parent::display($campaign);
    }

    public function saveEditEmail($ajax = false)
    {
        acym_checkToken();

        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $formData = acym_getVar('array', 'mail', []);
        $allowedFields = acym_getColumns('mail');
        $campaignId = acym_getVar("int", "id", 0);

        if (empty($campaignId)) {
            $mail = new stdClass();
            $mail->creation_date = acym_date('now', 'Y-m-d H:i:s', false);
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
            $mail->name = $mail->subject;
        }

        $mail->body = acym_getVar('string', 'editor_content', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->settings = acym_getVar('string', 'editor_settings', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->stylesheet = acym_getVar('string', 'editor_stylesheet', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->headers = acym_getVar('string', 'editor_headers', '', 'REQUEST', ACYM_ALLOWRAW);
        $mail->drag_editor = strpos($mail->body, 'acym__wysid__template') === false ? 0 : 1;

        $mail->tags = acym_getVar("array", "template_tags", []);

        $newAttachments = [];
        $attachments = acym_getVar('array', 'attachments', []);
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
                $attachment->filename = str_replace(['.', ' '], '_', substr($attachment->filename, 0, strpos($attachment->filename, $extension))).$extension;

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

            if (!$ajax) {
                $this->listing();

                return;
            } else {
                return false;
            }
        }

        $campaign->mail_id = $mailID;
        $campaign->id = $campaignClass->save($campaign);

        if ($ajax) {
            return $campaign->id;
        }

        acym_setVar("id", $campaign->id);

        $this->edit();
    }

    public function saveRecipients()
    {
        $allLists = json_decode(acym_getVar("string", "lists_selected"));
        $campaignId = acym_getVar("int", "id");

        $campaignClass = acym_get('class.campaign');
        $currentCampaign = $campaignClass->getOneByIdWithMail($campaignId);


        if ($currentCampaign->sent && !$currentCampaign->active) {
            $mailStatClass = acym_get('class.mailstat');
            $listClass = acym_get('class.list');
            $mailStat = $mailStatClass->getOneRowByMailId($currentCampaign->mail_id);
            $mailStat->total_subscribers = $listClass->getTotalSubCount($allLists);
            $mailStatClass->save($mailStat);
        } elseif (!empty($currentCampaign->mail_id)) {
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

            $this->listing();

            return;
        }
        $currentCampaign = $campaignClass->getOneById($campaignId);
        empty($currentCampaign->mail_id) ? : $currentMail = $mailClass->getOneById($currentCampaign->mail_id);

        if (empty($currentMail) || empty($senderInformation)) {
            $this->listing();

            return;
        }

        $currentMail->from_name = $senderInformation['from_name'];
        $currentMail->from_email = $senderInformation['from_email'];
        $currentMail->reply_to_name = $senderInformation['reply_to_name'];
        $currentMail->reply_to_email = $senderInformation['reply_to_email'];
        $currentMail->bcc = $senderInformation['bcc'];


        $mailClass->save($currentMail);

        if (!empty($isScheduled) && empty($currentCampaign->sent)) {
            if ($isScheduled == 'true') {
                $currentCampaign->scheduled = 1;
                if (!empty($sendingDate) && empty($currentCampaign->sent)) $currentCampaign->sending_date = acym_date(acym_getTime($sendingDate), 'Y-m-d H:i:s', false);
            } else {
                $currentCampaign->scheduled = 0;
                $currentCampaign->sending_date = null;
            }
        } elseif (empty($isScheduled)) {
            $this->listing();

            return;
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

            $this->listing();

            return;
        }

        $this->edit();
    }

    public function duplicate()
    {
        $campaignsSelected = acym_getVar('int', 'elements_checked');

        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');
        $campaignId = 0;

        foreach ($campaignsSelected as $campaignSelected) {

            $campaign = $campaignClass->getOneById($campaignSelected);

            unset($campaign->id);
            unset($campaign->sending_date);
            $campaign->draft = 1;
            $campaign->sent = 0;

            $mail = $mailClass->getOneById($campaign->mail_id);
            $oldMailId = $mail->id;
            unset($mail->id);
            $mail->creation_date = acym_date('now', 'Y-m-d H:i:s', false);
            $mail->name .= '_copy';
            $idNewMail = $mailClass->save($mail);

            $campaign->mail_id = $idNewMail;
            $campaignId = $campaignClass->save($campaign);

            $allLists = $campaignClass->getListsForCampaign($oldMailId);

            $campaignClass->manageListsToCampaign($allLists, $idNewMail);
        }

        acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_DUPLICATED_SUCCESS'), 'success', '10000');

        if (count($campaignsSelected) == 1 && acym_getVar('string', 'step', '') == 'summary') {
            acym_setVar('id', $campaignId);
            $this->editEmail();
        } else {
            $this->listing();
        }

        return;
    }

    public function saveSummary()
    {
        $this->edit();
    }

    public function summary()
    {
        acym_setVar('step', 'summary');
        acym_setVar('layout', 'summary');
        $campaignId = acym_getVar('int', 'id');
        $campaignClass = acym_get('class.campaign');

        $campaign = empty($campaignId) ? null : $campaignClass->getOneByIdWithMail($campaignId);

        if (is_null($campaign)) {
            acym_enqueueNotification(acym_translation('ACYM_CANT_GET_CAMPAIGN_INFORMATION'), 'error', 0);
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
            $listsIds = [];
            foreach ($campaignLists as $oneList) {
                $listsIds[] = $oneList->list_id;
            }
            $listClass = acym_get('class.list');
            $nbSubscribers = $listClass->getSubscribersCount($listsIds);
        }

        $mailData->from_name = empty($mailData->from_name) ? $config->get('from_name') : $mailData->from_name;
        $mailData->from_email = empty($mailData->from_email) ? $config->get('from_email') : $mailData->from_email;


        $useFromInReply = $config->get('from_as_replyto');
        $replytoName = $config->get('replyto_name');
        $replytoEmail = $config->get('replyto_email');

        if (!empty($mailData->reply_to_name)) {
            $replytoName = $mailData->reply_to_name;
        } elseif ($useFromInReply != 0 || empty($replytoName)) {
            $replytoName = $config->get('from_name');
        }

        if (!empty($mailData->reply_to_email)) {
            $replytoEmail = $mailData->reply_to_email;
        } elseif ($useFromInReply != 0 || empty($replytoEmail)) {
            $replytoEmail = $config->get('from_email');
        }

        $mailData->reply_to_name = $replytoName;
        $mailData->reply_to_email = $replytoEmail;

        $campaignType = empty($campaign->scheduled) ? 'now' : 'scheduled';

        acym_trigger('replaceContent', [&$mailData, false]);
        $receiver = $userClass->getOneByEmail(acym_currentUserEmail());
        if (empty($receiver)) {
            $receiver = new stdClass();
            $receiver->email = acym_currentUserEmail();
            $newID = $userClass->save($receiver);
            $receiver = $userClass->getOneById($newID);
        }
        acym_trigger('replaceUserInformation', [&$mailData, &$receiver, false]);

        $data = [
            'config' => $config,
            'campaignInformation' => $campaign,
            'mailInformation' => $mailData,
            'listsReceiver' => $campaignLists,
            'nbSubscribers' => $nbSubscribers,
            'campaignType' => $campaignType,
        ];

        $this->breadcrumb[acym_escape($campaign->name)] = acym_completeLink('campaigns&task=edit&step=summary&id='.$campaign->id);
        parent::display($data);
    }

    function unpause_campaign()
    {
        $id = acym_getVar('int', 'id', 0);
        if (empty($id)) {
            acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_NOT_FOUND'), 'error', 10000);
            $this->listing();

            return;
        }

        acym_redirect(acym_completeLink('queue').'&task=playPauseSending&acym__queue__play_pause__active__new_value=1&acym__queue__play_pause__campaign_id='.$id);

        return;
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

            $campaignId = $campaignClass->save($campaign);
            if (empty($campaignId)) {
                acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
            } else {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
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

            $campaignId = $campaignClass->save($campaign);
            if (empty($campaignId)) {
                acym_enqueueNotification(acym_translation('ACYM_CAMPAIGN_CANT_BE_SAVED'), 'error', 0);
            } else {
                acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
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
            acym_enqueueNotification(acym_translation_sprintf('ACYM_CONFIRMED_CAMPAIGN', acym_date($campaignSendingDate, 'j F Y H:i')), 'success', 8000);
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
            $lists = $campaignClass->getAllListsByCampaignId($campaign->id)[0]->name;
            if (!empty($lists)) {
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

            $campaignId = $campaignClass->save($campaign);
            if (empty($campaignId)) {
                echo 'error';
                exit;
            }

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

                $this->_redirectAfterQueued();

                return;
            }

            $status = $campaignClass->send($campaignID);

            if ($status) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_CAMPAIGN_ADDED_TO_QUEUE', $campaign->name), 'info', 5000);
            } else {
                if (empty($campaignClass->errors)) {
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_ERROR_QUEUE_CAMPAIGN', $campaign->name), 'error', 10000);
                } else {
                    acym_enqueueNotification($campaignClass->errors, 'error', 6000);
                }
            }
        }

        $this->_redirectAfterQueued();
    }

    private function _redirectAfterQueued()
    {
        $config = acym_config();
        if (!acym_level(1) || $config->get('cron_last', 0) < (time() - 43200)) {
            acym_redirect(acym_completeLink('queue&task=campaigns', false, true));
        } else {
            $this->listing();
        }
    }

    public function countNumberOfRecipients()
    {
        $listsSelected = acym_getVar("array", "listsSelected", []);
        if (empty($listsSelected)) {
            echo 0;
            exit;
        }

        $listClass = acym_get('class.list');
        echo $listClass->getTotalSubCount($listsSelected);
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


        $report = [];

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

        $testEmails = acym_getVar('array', 'test_emails', [acym_currentUserEmail()]);
        foreach ($testEmails as $oneEmail) {
            $defaultEmails[$oneEmail] = $oneEmail;
        }

        $data = [
            'id' => $campaign->id,
            'test_emails' => $defaultEmails,
            'upgrade' => !acym_level(2) ? true : false,
            'version' => 'enterprise',
        ];

        $this->breadcrumb[acym_escape($campaign->name)] = acym_completeLink('campaigns&task=edit&step=tests&id='.$campaign->id);
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

        $spamWords = [
            '4U',
            'you are a winner',
            'For instant access',
            'Accept credit cards',
            'Claims you registered with',
            'For just $',
            'Act now!',
            'Don’t hesitate!',
            'Click below',
            'Free',
            'income',
            'Click here',
            'Click to remove',
            'All natural',
            'Amazing',
            'Compare rates',
            'Apply Online',
            'your business',
            'As seen on',
            'all orders',
            'Auto email removal',
            'bankruptcy',
            'debt',
            'Be amazed',
            'Copy accurately',
            'Be your own boss',
            'Being a member',
            'Big bucks',
            'Credit card',
            'Bill',
            'Cures baldness',
            'Billing address',
            'Billion dollars',
            'Dear friend',
            'Brand new pager',
            'Bulk email',
            'Different reply to',
            'Buy direct',
            'Dig up dirt',
            'Full refund',
            'Buying judgments',
            'Direct email',
            'Get It Now',
            'Cable converter',
            'Direct marketing',
            'Get paid',
            'Get started now',
            'Call now',
            'Do it today',
            'Gift certificate',
            'Calling creditors',
            'Don’t delete',
            'Great offer',
            'Can’t live without',
            'Drastically reduced',
            'Guarantee',
            'Cancel at any time',
            'Earn per week',
            'Have you been turned down?',
            'Easy terms',
            'Hidden assets',
            'Eliminate bad credit',
            'Home employment',
            'Cash',
            'Email harvest',
            'Human growth hormone',
            'Casino',
            'Email marketing',
            'Expect to earn',
            'In accordance with laws',
            'Fantastic deal',
            'Increase sales',
            'Viagra',
            'Increase traffic',
            'Insurance',
            'Find out anything',
            'Investment decision',
            'it\'s legal',
            'It\'s effective',
            'Join millions of',
            'No questions asked',
            'Reverses aging',
            'No selling',
            'Risk',
            'Limited time only',
            'No strings attached',
            'Round the world',
            'Not intended',
            'Lose weight',
            'Off shore',
            'Safeguard notice',
            'Lower interest rates',
            'Offer expires',
            'Satisfaction guaranteed',
            'Lower monthly payment',
            'coupon',
            'Save $',
            'Lowest price',
            'Luxury car',
            'Save up to',
            'Once in a lifetime',
            'Score with babes',
            'Marketing solutions',
            'Mass email',
            'guaranteed',
            'See for yourself',
            'Meet singles',
            'One time mailing',
            'Sent in compliance',
            'Member stuff',
            'opportunity',
            'Online pharmacy',
            'Serious only',
            'MLM',
            'Only $',
            'Shopping spree',
            'Social security number',
            'trial offer',
            'Special promotion',
            'More Internet traffic',
            'Stock alert',
            'Outstanding values',
            'Pennies a day',
            'Stock pick',
            'New customers only',
            'money',
            'Stop snoring',
            'New domain extensions',
            'Please read',
            'Strong buy',
            'Potential earnings',
            'Stuff on sale',
            'No age restrictions',
            'Subject to credit',
            'No catch',
            'Supplies are limited',
            'No claim forms',
            'Produced and sent out',
            'Take action now',
            'No cost',
            'Profits',
            'hidden charges',
            'No credit check',
            'Promise you',
            'No disappointment',
            'Pure profit',
            'Real thing',
            'No fees',
            'Refinance home',
            'The best rates',
            'No gimmick',
            'The following form',
            'No inventory',
            'No investment',
            'giving it away',
            'No medical exams',
            'Removes wrinkles',
            'This isn’t junk',
            'No middleman',
            'This isn’t spam',
            'No obligation',
            'initial investment',
            'University diplomas',
            'No purchase necessary',
            'Reserves the right',
            'Unlimited',
            'We honor all',
            'Will not believe your eyes',
            'Urgent',
            'Winner',
            'US dollars',
            'What are you waiting for?',
            'Winning',
            'While supplies last',
            'Work at home',
            'drugs',
            'While you sleep',
            'You have been selected',
            'We hate spam',
            'Why pay more?',
        ];

        $errors = [];
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

        acym_trigger('replaceContent', [&$mail, false]);
        $userClass = acym_get('class.user');
        $receiver = $userClass->getOneByEmail(acym_currentUserEmail());
        if (empty($receiver)) {
            $receiver = new stdClass();
            $receiver->email = acym_currentUserEmail();
            $newID = $userClass->save($receiver);
            $receiver = $userClass->getOneById($newID);
        }
        acym_trigger('replaceUserInformation', [&$mail, &$receiver, false]);

        preg_match_all('# (href|src)="([^"]+)"#Uis', acym_absoluteURL($mail->body), $URLs);

        $errors = [];
        $processed = [];
        foreach ($URLs[2] as $oneURL) {
            if (in_array($oneURL, $processed)) continue;
            if (0 === strpos($oneURL, 'mailto:')) continue;
            if (strlen($oneURL) > 1 && 0 === strpos($oneURL, '#')) continue;

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

    public function saveAjax()
    {
        $return = $this->saveEditEmail(true);
        echo json_encode(['error' => !$return ? acym_translation('ACYM_ERROR_SAVING') : '', 'data' => $return]);
        exit;
    }
}

