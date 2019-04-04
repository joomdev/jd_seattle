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

class FrontUrlController extends acymController
{
    public function click()
    {
        $urlid = acym_getVar('int', 'urlid');
        $mailid = acym_getVar('int', 'mailid');
        $userid = acym_getVar('int', 'userid');

        $mailStatClass = acym_get('class.mailstat');
        $userStatClass = acym_get('class.userstat');
        $urlClass = acym_get('class.url');
        $urlObject = $urlClass->getOneUrlById($urlid);

        if (empty($urlObject->id)) {
            return acym_raiseError(E_ERROR, 404, acym_translation('Page not found'));
        }

        $urlClickClass = acym_get('class.urlclick');
        if (!acym_isRobot()) {
            $urlClick = array(
                'mail_id' => $mailid,
                'url_id' => $urlObject->id,
                'click' => 1,
                'user_id' => $userid,
                'date_click' => acym_date('now', 'Y-m-d H:i:s'),
            );
            $urlClickClass->save($urlClick);
            $userStat = $userStatClass->getOneByMailAndUserId($mailid, $userid);
            if (empty($userStat->open)) {
                $userStatToInsert = array();
                $userStatToInsert['user_id'] = $userid;
                $userStatToInsert['mail_id'] = $mailid;
                $userStatToInsert['open'] = 1;
                $userStatToInsert['open_date'] = acym_date('now', 'Y-m-d H:i:s');

                $mailStatToInsert = array();
                $mailStatToInsert['mail_id'] = $mailid;
                $mailStatToInsert['open_unique'] = 1;
                $mailStatToInsert['open_total'] = 1;
                $userStatClass->save($userStatToInsert);
                $mailStatClass->save($mailStatToInsert);
            }
        }


        acym_redirect($urlObject->url);
    }
}
