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

class acymheaderHelper
{
    function display($breadcrumb)
    {
        $news = @simplexml_load_file(ACYM_ACYWEBSITE.'acymnews.xml');
        $config = acym_config();
        $header = '';
        if (!empty($news->news)) {

            $currentLanguage = acym_getLanguageTag();

            $latestNews = null;
            $doNotRemind = json_decode($config->get('remindme', '[]'));
            foreach ($news->news as $oneNews) {
                if (!empty($latestNews) && strtotime($latestNews->date) > strtotime($oneNews->date)) break;

                if (empty($oneNews->published) || (strtolower($oneNews->language) != strtolower($currentLanguage) && (strtolower($oneNews->language) != 'default' || !empty($latestNews)))) continue;

                if (!empty($oneNews->extension) && strtolower($oneNews->extension) != 'acymailing') continue;

                if (!empty($oneNews->cms) && strtolower($oneNews->cms) != 'Joomla') continue;

                if (!empty($oneNews->level) && strtolower($oneNews->level) != strtolower($config->get('level'))) continue;

                if (!empty($oneNews->version)) {
                    list($version, $operator) = explode('_', $oneNews->version);
                    if (!version_compare($config->get('version'), $version, $operator)) continue;
                }

                if (in_array($oneNews->name, $doNotRemind)) continue;

                $latestNews = $oneNews;
            }

            if (!empty($latestNews)) {
                $header .= '<div id="acym__header__banner__news" data-news="'.acym_escape($latestNews->name).'">';

                if (!empty($latestNews)) {
                    $header .= $latestNews->content;
                }

                $header .= '</div>';
            }
        }

        $links = [];
        foreach ($breadcrumb as $oneLevel => $link) {
            if (!empty($link)) {
                $oneLevel = '<a href="'.$link.'">'.$oneLevel.'</a>';
            }
            $links[] = '<li>'.$oneLevel.'</li>';
        }

        if (count($links) > 1) {
            $links[count($links) - 1] = str_replace('<li>', '<li class="last_link cell auto"><span class="show-for-sr">Current: </span>', $links[count($links) - 1]);
        }


        $header .= '<div id="acym_header" class="grid-x hide-for-small-only margin-bottom-1">';

        $header .= '<i class="cell medium-shrink acym-logo"></i>';

        $header .= '<div id="acym_global_navigation" class="cell medium-auto"><nav aria-label="You are here:" role="navigation"><ul class="breadcrumbs grid-x">';
        $header .= implode('', $links);
        $header .= '</ul></nav></div>';

        $header .= '<div id="checkVersionArea" class="cell grid-x align-right large-auto check-version-area acym_vcenter margin-right-1">';
        $header .= $this->checkVersionArea();
        $header .= '</div>';

        $config = acym_config();

        $lastLicenseCheck = $config->get('lastlicensecheck', 0);
        $time = time();
        $checking = '0';
        if ($time > $lastLicenseCheck + 604800) $checking = '1';
        if (empty($lastLicenseCheck)) $lastLicenseCheck = $time;

        $header .= '<div class="cell grid-x align-right large-shrink">';
        $header .= acym_tooltip(
            '<a id="checkVersionButton" type="button" class="button button-secondary medium-shrink" data-check="'.$checking.'">'.acym_translation('ACYM_CHECK_MY_VERSION').'</a>',
            acym_translation('ACYM_LAST_CHECK').' <span id="acym__check__version__last__check">'.acym_date($lastLicenseCheck, 'Y/m/d H:i').'</span>'
        );

        $url = ACYM_UPDATEMEURL.'doc&task=doc&product=acymailing&for='.(empty($_REQUEST['ctrl']) ? 'dashboard' : $_REQUEST['ctrl']).'-'.$_REQUEST['layout'];
        $header .= '<a type="button" class="button medium-shrink" target="_blank" href="'.$url.'">'.acym_translation('ACYM_DOCUMENTATION').'</a>';
        $header .= '</div></div>';

        return $header;
    }

    public function checkVersionArea()
    {
        $config = acym_config();

        $currentLevel = $config->get('level', '');
        $currentVersion = $config->get('version', '');
        $latestVersion = $config->get('latestversion', '');

        $version = '<div id="acym_level_version_area" class="text-right">';
        $version .= '<div id="acym_level">'.ACYM_NAME.' '.$currentLevel.' ';

        if (version_compare($currentVersion, $latestVersion, '>=')) {
            $version .= acym_tooltip('<span class="acym__color__green">'.$currentVersion.'</span>', acym_translation('ACYM_UP_TO_DATE'));
        } elseif (!empty($latestVersion)) {
            if ('wordpress' === ACYM_CMS) {
                $downloadLink = admin_url().'update-core.php';
            } else {
                $downloadLink = ACYM_REDIRECT.'update-acymailing-'.$currentLevel.'&version='.$config->get('version').'" target="_blank';
            }
            $version .= acym_tooltip(
                '<span class="acy_updateversion acym__color__red">'.$currentVersion.'</span>',
                acym_translation_sprintf('ACYM_CLICK_UPDATE', $latestVersion),
                '',
                acym_translation('ACYM_OLD_VERSION'),
                $downloadLink
            );
        }

        $version .= '</div></div>';

        if (!acym_level(1)) return $version;

        $expirationDate = $config->get('expirationdate', 0);
        if (empty($expirationDate) || $expirationDate == -1) return $version;

        $version .= '<div id="acym_expiration" class="text-right cell">';
        if ($expirationDate == -2) {
            $version .= '<div class="acylicence_expired">
                            <a class="acy_attachlicence acymbuttons acym__color__red" href="'.ACYM_REDIRECT.'acymailing-assign" target="_blank">'.acym_translation('ACYM_ATTACH_LICENCE').'</a>
                        </div>';
        } elseif ($expirationDate < time()) {
            $version .= acym_tooltip(
                '<span class="acy_subscriptionexpired acym__color__red">'.acym_translation('ACYM_SUBSCRIPTION_EXPIRED').'</span>',
                acym_translation('ACYM_SUBSCRIPTION_EXPIRED_LINK'),
                '',
                '',
                ACYM_REDIRECT.'renew-acymailing-'.$currentLevel
            );
        } else {
            $version .= '<div class="acylicence_valid">
                            <span class="acy_subscriptionok acym__color__green">'.acym_translation_sprintf('ACYM_VALID_UNTIL', acym_getDate($expirationDate, acym_translation('ACYM_DATE_FORMAT_LC4'))).'</span>
                        </div>';
        }
        $version .= '</div>';

        return $version;
    }
}

