<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class ArchiveController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultTask('view');
        $this->authorizedFrontTasks = ['view', 'listing', 'showArchive'];
    }

    public function view()
    {
        acym_addMetadata('robots', 'noindex,nofollow');

        $mailId = acym_getVar('int', 'id', 0);

        $isPopup = acym_getVar('int', 'is_popup', 0);

        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->loadedToSend = false;
        $oneMail = $mailerHelper->load($mailId);

        if (empty($oneMail->id)) {
            acym_raiseError(E_ERROR, 404, acym_translation('ACYM_EMAIL_NOT_FOUND'));

            return;
        }

        if (preg_match('#<img[^>]*id="pictshare"[^>]*>#i', $oneMail->body, $pregres) && preg_match('#src="([^"]*)"#i', $pregres[0], $pict)) {
            acym_addMetadata('og:image', $pict[1]);
        } elseif (preg_match('#<img[^>]*class="[^"]*pictshare[^"]*"[^>]*>#i', $oneMail->body, $pregres) && preg_match('#src="([^"]*)"#i', $pregres[0], $pict)) {
            acym_addMetadata('og:image', $pict[1]);
        }

        acym_addMetadata('og:url', acym_frontendLink('archive&task=view&mailid='.$oneMail->id));
        acym_addMetadata('og:title', $oneMail->subject);

        if (!empty($oneMail->metadesc)) {
            acym_addMetadata('og:description', $oneMail->metadesc);
            acym_addMetadata('description', $oneMail->metadesc);
        }
        if (!empty($oneMail->metakey)) {
            acym_addMetadata('keywords', $oneMail->metakey);
        }

        $userkeys = acym_getVar('string', 'userid', 0);
        if (!empty($userkeys)) {
            $userId = intval(substr($userkeys, 0, strpos($userkeys, '-')));
            $userKey = substr($userkeys, strpos($userkeys, '-') + 1);
            $receiver = acym_loadObject('SELECT * FROM #__acym_user WHERE `id` = '.intval($userId).' AND `key` = '.acym_escapeDB($userKey));
        }

        $currentEmail = acym_currentUserEmail();
        if (empty($receiver) && !empty($currentEmail)) {
            $userClass = acym_get('class.user');
            $receiver = $userClass->getOneByEmail($currentEmail);
        }

        if (empty($receiver)) {
            $receiver = new stdClass();
            $receiver->name = acym_translation('ACYM_VISITOR');
        }

        acym_trigger('replaceUserInformation', [&$oneMail, &$receiver, false]);

        preg_match('@href="{unsubscribe:(.*)}"@', $oneMail->body, $match);
        if (!empty($match)) {
            $oneMail->body = str_replace($match[0], 'href="'.$match[1].'"', $oneMail->body);
        }

        if (strpos($oneMail->body, 'acym__wysid__template') !== false) {
            acym_addStyle(false, ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css'));
        }
        acym_addStyle(true, acym_getEmailCssFixes());
        if (!empty($oneMail->stylesheet)) {
            acym_addStyle(true, $oneMail->stylesheet);
        }
        $editorHelper = acym_get('helper.editor');
        $settings = json_decode($oneMail->settings, true);
        if (!empty($settings)) {
            $settings = $editorHelper->getSettingsStyle($settings);

            if (!empty($settings)) {
                acym_addStyle(true, $settings);
            }
        }

        $oneMail->body = preg_replace('#background\-image: url\(&quot;([^)]*)&quot;\)#Uis', 'background-image: url($1)', $oneMail->body);

        $data = [
            'mail' => $oneMail,
            'receiver' => $receiver,
        ];

        acym_includeHeaders();
        parent::display($data);

        if ($isPopup || 'wordpress' === ACYM_CMS) exit;
    }

    public function listing()
    {
        acym_setVar('layout', 'listing');

        $menu = acym_getMenu();
        if (!is_object($menu)) {
            acym_redirect(acym_rootURI());

            return;
        }

        $menuParams = new acymParameter($menu->params);

        $paramsJoomla = [];
        $paramsJoomla['suffix'] = $menuParams->get('pageclass_sfx', '');
        $paramsJoomla['page_heading'] = $menuParams->get('page_heading');
        $paramsJoomla['show_page_heading'] = $menuParams->get('show_page_heading', 0);

        if ($menuParams->get('menu-meta_description')) {
            acym_addMetadata('description', $menuParams->get('menu-meta_description'));
        }

        if ($menuParams->get('menu-meta_keywords')) {
            acym_addMetadata('keywords', $menuParams->get('menu-meta_keywords'));
        }

        if ($menuParams->get('robots')) {
            acym_addMetadata('robots', $menuParams->get('robots'));
        }

        $nbNewsletters = $menuParams->get('archiveNbNewsletters', '0');
        $listsSent = $menuParams->get('lists', '');
        $popup = $menuParams->get('popup', '1');

        $viewParams = [
            'nbNewsletters' => $nbNewsletters,
            'listsSent' => $listsSent,
            'popup' => $popup,
            'paramsCMS' => $paramsJoomla,
        ];

        $this->showArchive($viewParams);
    }

    public function showArchive($viewParams)
    {
        acym_setVar('layout', 'listing');

        $params = [];

        $userId = false;
        $userClass = acym_get('class.user');
        $currentUser = $userClass->identify(true);
        if (!empty($currentUser)) {
            $params['userId'] = $currentUser->id;
            $userId = $currentUser->id;
        }

        if (!empty($viewParams['nbNewsletters'])) {
            $params['limit'] = $viewParams['nbNewsletters'];
        }

        if (!empty($viewParams['listsSent'])) {
            $params['lists'] = $viewParams['listsSent'];
        }

        $params['page'] = acym_getVar('int', 'page', 1);
        $params['numberPerPage'] = acym_getCMSConfig('list_limit', 10);

        $campaignClass = acym_get('class.campaign');
        $returnLastNewsletters = $campaignClass->getLastNewsletters($params);
        $pagination = acym_get('helper.pagination');
        $pagination->setStatus($returnLastNewsletters['count'], $params['page'], $params['numberPerPage']);

        $data = [
            'newsletters' => $returnLastNewsletters['matchingNewsletters'],
            'paramsJoomla' => $viewParams['paramsCMS'],
            'pagination' => $pagination,
            'userId' => $userId,
            'popup' => '1' === $viewParams['popup'],
        ];

        acym_addScript(false, ACYM_JS.'front/frontarchive.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'front'.DS.'frontarchive.min.js'));

        parent::display($data);
    }
}

