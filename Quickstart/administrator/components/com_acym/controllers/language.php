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

class LanguageController extends acymController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function saveLanguage($fromShare = false)
    {
        acym_checkToken();

        $code = acym_getVar('cmd', 'code');
        acym_setVar('code', $code);

        $content = acym_getVar('string', 'content', '', '', ACYM_ALLOWHTML);
        $content = str_replace('</textarea>', '', $content);

        if (empty($code) || empty($content)) {
            return $this->displayLanguage();
        }

        $customcontent = acym_getVar('string', 'customcontent', '', '', ACYM_ALLOWHTML);
        $customcontent = str_replace('</textarea>', '', $customcontent);

        $path = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym.ini';
        $result = acym_writeFile($path, $content);
        if ($result) {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success');
            acym_addScript(true, "window.top.document.getElementById('image$code').innerHTML = 'edit'");

            $updateHelper = acym_get('helper.update');
            $updateHelper->installBackLanguages($code);
        } else {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_FAIL_SAVE', $path), 'error');
        }

        $custompath = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym_custom.ini';
        $customresult = acym_writeFile($custompath, $customcontent);
        if (!$customresult) {
            acym_enqueueNotification(acym_translation_sprintf('ACYM_FAIL_SAVE', $custompath), 'error');
        }

        if ($code == acym_getLanguageTag()) {
            acym_loadLanguage();
        }

        $updateHelper = acym_get('helper.update');
        $updateHelper->installBackLanguages();

        if ($fromShare) {
            return $result;
        } else {
            return $this->displayLanguage();
        }
    }

    public function latest()
    {
        $this->displayLanguage();
    }

    function share()
    {
        acym_checkToken();

        if ($this->saveLanguage(true)) {
            acym_setVar('layout', 'share');

            $file = new stdClass();
            $file->name = acym_getVar('cmd', 'code');

            return parent::display(array('file' => $file));
        } else {
            return $this->displayLanguage();
        }
    }

    function send()
    {
        acym_checkToken();

        $bodyEmail = acym_getVar('string', 'mailbody');
        $code = acym_getVar('cmd', 'code');
        acym_setVar('code', $code);

        if (empty($code)) {
            return;
        }

        $config = acym_config();
        $mailer = acym_get('helper.mailer');
        $mailer->Subject = '[ACYMAILING LANGUAGE FILE] '.$code;
        $mailer->Body = 'The website '.ACYM_LIVE.' using AcyMailing '.$config->get('level').' '.$config->get('version').' sent a language file : '.$code;
        $mailer->Body .= "\n\n\n".$bodyEmail;

        $file = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym.ini';
        if (!file_exists($file)) {
            return;
        }

        $translation = acym_fileGetContent($file);

        $customFile = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym_custom.ini';

        if (file_exists($customFile)) {
            $customTranslation = acym_fileGetContent($customFile);

            if (!empty($customTranslation)) {
                $newKeys = array();
                $customKeys = array();
                preg_match_all('#([0-9A-Z_]+)="((?:[^"]|"_QQ_")+)"#is', $customTranslation, $customKeys);

                if (!empty($customKeys)) {
                    $mainKeys = array();
                    preg_match_all('#([0-9A-Z_]+)="((?:[^"]|"_QQ_")+)"#is', $translation, $mainKeys);

                    foreach ($customKeys[1] as $index => $oneKey) {
                        $position = array_search($oneKey, $mainKeys[1]);
                        if ($position !== false) {
                            $translation = str_replace($mainKeys[0][$position], $customKeys[0][$index], $translation);
                        } else {
                            $newKeys[] = $customKeys[0][$index];
                        }
                    }

                    if (!empty($newKeys)) {
                        $mailer->Body .= "\n\n\nCustom content:\n".implode("\n", $newKeys);
                    }
                }
            }
        }

        $mailer->addStringAttachment($translation, $code.'.com_acym.ini');

        $mailer->AddAddress(acym_currentUserEmail(), acym_currentUserName());
        $mailer->AddAddress('translate@acyba.com', 'Acyba Translation Team');
        $mailer->report = false;

        $result = $mailer->Send();

        if ($result) {
            acym_enqueueNotification(acym_translation('ACYM_THANK_YOU_SHARING').'<br>'.acym_translation('ACYM_MESSAGE_SENT'), 'success');
        } else {
            acym_enqueueNotification($mailer->reportMessage, 'error');
        }

        $this->displayLanguage();
    }

    public function displayLanguage()
    {
        acym_setVar("layout", "default");

        $code = acym_getVar('string', 'code');
        if (empty($code)) {
            acym_display('Code not specified', 'error');

            return;
        }

        $file = new stdClass();
        $file->name = $code;
        $path = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym.ini';
        $file->path = $path;
        $file->content = '';
        $file->customcontent = '';


        $showLatest = true;
        $loadLatest = false;

        if (file_exists($path)) {
            $file->content = acym_fileGetContent($path);
            if (empty($file->content)) {
                acym_display('File not found : '.$path, 'error');
            }
        } else {
            $loadLatest = true;
            acym_enqueueMessage(acym_translation('ACYM_LOAD_ENGLISH_1').'<br />'.acym_translation('ACYM_LOAD_ENGLISH_2').'<br />'.acym_translation('ACYM_LOAD_ENGLISH_3'), 'info');
            $file->content = acym_fileGetContent(acym_getLanguagePath(ACYM_ROOT, ACYM_DEFAULT_LANGUAGE).DS.ACYM_DEFAULT_LANGUAGE.'.com_acym.ini');
        }

        $custompath = acym_getLanguagePath(ACYM_ROOT, $code).DS.$code.'.com_acym_custom.ini';
        if (file_exists($custompath)) {
            $file->customcontent = acym_fileGetContent($custompath);
        }

        if ($loadLatest || acym_getVar('cmd', 'task') == 'latest') {
            if (file_exists(acym_getLanguagePath(ACYM_ROOT, $code))) {
                acym_addScript(false, ACYM_UPDATEMEURL.'update&component=acym&task=languageload&code='.acym_getVar('cmd', 'code'));
            } else {
                acym_enqueueMessage('The specified language "'.htmlspecialchars($code, ENT_COMPAT, 'UTF-8').'" is not installed on your site', 'warning');
            }
            $showLatest = false;
        } elseif (acym_getVar('cmd', 'task') == 'save') {
            $showLatest = false;
        }

        $data = array(
            'showLatest' => $showLatest,
            'file' => $file,
        );

        return parent::display($data);
    }
}
