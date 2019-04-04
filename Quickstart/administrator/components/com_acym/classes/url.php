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

class acymurlClass extends acymClass
{
    var $table = 'url';
    var $pkey = 'id';

    public function save($url)
    {
        if (empty($url)) {
            return false;
        }

        foreach ($url as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            $url->$oneAttribute = strip_tags($value);
        }

        return parent::save($url);
    }

    public function getOneUrlById($id)
    {
        $query = 'SELECT * from #__acym_url WHERE id = '.$id;

        return acym_loadObject($query);
    }

    public function getUrl($url, $mailid, $userid)
    {

        if (empty($url) || empty($mailid) || empty($userid)) {
            return;
        }

        $url = str_replace('&amp;', '&', $url);

        $currentUrl = new stdClass();
        $currentUrl->name = $url;
        $currentUrl->url = $url;
        $currentUrl->id = $this->save($currentUrl);

        if (empty($currentUrl->id)) {
            return;
        }

        return acym_frontendLink('fronturl&action=acymailing_frontrouter&task=click&urlid='.$currentUrl->id.'&userid='.$userid.'&mailid='.$mailid);
    }
}
