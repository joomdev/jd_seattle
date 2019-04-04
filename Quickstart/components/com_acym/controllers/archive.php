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

class ArchiveController extends acymController
{
    function __construct()
    {
        parent::__construct();
        $this->setDefaultTask('view');
    }

    function view()
    {
        acym_addMetadata('robots', 'noindex,nofollow');

        $mailId = acym_getVar('int', 'id', 0);

        $isPopup = acym_getVar('int', 'is_popup', 0);

        $mailerHelper = acym_get('helper.mailer');
        $mailerHelper->loadedToSend = false;
        $oneMail = $mailerHelper->load($mailId);

        if (empty($oneMail->id)) {
            return acym_raiseError(E_ERROR, 404, acym_translation('ACYM_CAMPAIGN_NOT_FOUND'));
        }

        $campaignClass = acym_get('class.campaign');
        $campaign = $campaignClass->get($mailId, 'mail_id');

        if (empty($campaign) || $campaign->active != 1) {
            acym_enqueueMessage('This email doesn\'t or isn\'t published', 'error');
            acym_redirect(acym_rootURI());

            return false;
        }

        $fshare = '';
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
            $receiver = acym_loadObject('SELECT * FROM #__acym_user WHERE `id` = '.intval($userId).' AND `key` = '.acym_escapeDB($userKey).' LIMIT 1');
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

        acym_trigger('replaceUserInformation', array(&$oneMail, &$receiver, false));

        preg_match('@href="{unsubscribe:(.*)}"@', $oneMail->body, $match);
        if (!empty($match)) {
            $oneMail->body = str_replace($match[0], 'href="'.$match[1].'"', $oneMail->body);
        }

        acym_addStyle(false, ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css'));
        acym_addStyle(false, ACYM_CSS.'email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'email.min.css'));

        $data = array(
            'mail' => $oneMail,
            'receiver' => $receiver,
        );

        parent::display($data);

        if ($isPopup) exit;
    }

    function listing()
    {
        acym_setVar("layout", "listing");

        $menu = acym_getMenu();
        $campaignClass = acym_get('class.campaign');
        $userClass = acym_get('class.user');
        $pagination = acym_get('helper.pagination');

        $page = acym_getVar('int', 'page', 1);

        $paramsJoomla = array();

        if (!is_object($menu)) {
            acym_redirect(acym_rootURI());
            return false;
        }

        $menuParams = new acymParameter($menu->params);
        $nbNewsletters = $menuParams->get('archiveNbNewsletters', '0');

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

        $currentUser = $userClass->identify(true);

        $params = array();

        $userId = false;

        if (!empty($currentUser)) {
            $params['userId'] = $currentUser->id;
            $userId = $currentUser->id;
        }

        if (!empty($nbNewsletters)) {
            $params['limit'] = $nbNewsletters;
        }

        $params['page'] = $page;

        $params['numberPerPage'] = acym_getCMSConfig("list_limit", 10);


        $returnLastNewsletters = $campaignClass->getLastNewsletters($params);

        $matchingNewsletters = $returnLastNewsletters['matchingNewsletters'];

        $countNewsletters = $returnLastNewsletters['count'];

        $pagination->setStatus($countNewsletters, $page, $params['numberPerPage']);

        $data = [
            'newsletters' => $matchingNewsletters,
            'paramsJoomla' => $paramsJoomla,
            'pagination' => $pagination,
            'userId' => $userId,
        ];


        acym_addScript(false, ACYM_JS.'front/frontarchive.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'front'.DS.'frontarchive.min.js'));

        parent::display($data);
    }
}
