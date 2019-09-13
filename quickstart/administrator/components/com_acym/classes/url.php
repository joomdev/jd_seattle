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
        $query = 'SELECT * from #__acym_url WHERE id = '.intval($id);

        return acym_loadObject($query);
    }

    public function get($url)
    {
        $column = is_numeric($url) ? 'id' : 'url';
        $query = 'SELECT * FROM #__acym_url WHERE '.$column.' = '.acym_escapeDB($url).' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getAdd($url)
    {
        $currentUrl = $this->get($url);
        if (empty($currentUrl->id)) {
            $currentUrl = new stdClass();
            $currentUrl->name = $url;
            $currentUrl->url = $url;
            $currentUrl->id = $this->save($currentUrl);

            if (empty($currentUrl->id)) {
                return;
            }
        }

        return $currentUrl;
    }

    public function getUrl($url, $mailid, $userid)
    {

        if (empty($url) || empty($mailid) || empty($userid)) {
            return;
        }

        static $allurls;

        $url = str_replace('&amp;', '&', $url);

        if (empty($allurls[$url])) {
            $currentUrl = $this->getAdd($url);

            $allurls[$url] = acym_frontendLink('fronturl&action=acymailing_frontrouter&task=click&urlid='.$currentUrl->id.'&userid='.$userid.'&mailid='.$mailid);
        }

        return $allurls[$url];
    }
}
