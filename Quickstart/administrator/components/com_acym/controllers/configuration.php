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

class ConfigurationController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_CONFIGURATION')] = acym_completeLink('configuration');
    }

    public function listing()
    {
        acym_setVar("layout", "listing");

        $config = acym_config();
        $tabHelper = acym_get('helper.tab');

        $langs = acym_getLanguages();
        $languages = array();

        foreach ($langs as $lang => $obj) {
            if (strlen($lang) != 5 || $lang == "xx-XX") {
                continue;
            }

            $oneLanguage = new stdClass();
            $oneLanguage->language = $lang;
            $oneLanguage->name = $obj->name;

            $linkEdit = acym_completeLink('language&task=displayLanguage&code='.$lang, true);
            $icon = $obj->exists ? 'edit' : 'add';
            $idModalLanguage = 'acym_modal_language_'.$lang;
            $oneLanguage->edit = acym_modal(
                '<i class="material-icons cursor-pointer acym__color__blue" data-open="'.$idModalLanguage.'" data-ajax="false" data-iframe="'.$linkEdit.'" data-iframe-class="acym__iframe_language" id="image'.$lang.'">'.$icon.'</i>',
                '', //<iframe src="'.$linkEdit.'"></iframe>
                $idModalLanguage,
                'data-reveal-larger',
                '',
                false
            );

            $languages[] = $oneLanguage;
        }

        $data = array(
            'config' => $config,
            'tab' => $tabHelper,
            'languages' => $languages,
        );

        return parent::display($data);
    }

    function checkDB()
    {
        $messages = array();

        $queries = file_get_contents(ACYM_BACK.'tables.sql');
        $tables = explode("CREATE TABLE IF NOT EXISTS ", $queries);
        $structure = array();
        $createTable = array();
        $indexes = array();

        foreach ($tables as $oneTable) {
            if (strpos($oneTable, '`#__') !== 0) {
                continue;
            }

            $tableName = substr($oneTable, 1, strpos($oneTable, '`', 1) - 1);

            $fields = explode("\n", $oneTable);
            foreach ($fields as $oneField) {
                if (strpos($oneField, '#__') === 1) {
                    continue;
                }
                $oneField = rtrim(trim($oneField), ',');

                if (substr($oneField, 0, 1) == '`') {
                    $columnName = substr($oneField, 1, strpos($oneField, '`', 1) - 1);
                    $structure[$tableName][$columnName] = trim($oneField, ",");
                    continue;
                }

                if (strpos($oneField, 'PRIMARY KEY') === 0) {
                    $indexes[$tableName]['PRIMARY'] = $oneField;
                } else if (strpos($oneField, 'INDEX') === 0) {
                    $firstBackquotePos = strpos($oneField, '`');
                    $indexName = substr($oneField, $firstBackquotePos + 1, strpos($oneField, '`', $firstBackquotePos + 1) - $firstBackquotePos - 1);

                    $indexes[$tableName][$indexName] = $oneField;
                }
            }
            $createTable[$tableName] = "CREATE TABLE IF NOT EXISTS ".$oneTable;
        }


        $columnNames = array();
        $tableNames = array_keys($structure);

        foreach ($tableNames as $oneTableName) {
            try {
                $columns = acym_loadObjectList("SHOW COLUMNS FROM ".$oneTableName);
            } catch (Exception $e) {
                $columns = null;
            }

            if (!empty($columns)) {
                foreach ($columns as $oneField) {
                    $columnNames[$oneTableName][$oneField->Field] = $oneField->Field;
                }
                continue;
            }


            $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
            $messages[] = "<span style=\"color:blue\">Could not load columns from the table ".$oneTableName." : ".$errorMessage."</span>";

            if (strpos($errorMessage, 'marked as crashed')) {
                $repairQuery = 'REPAIR TABLE '.$oneTableName;

                try {
                    $isError = acym_query($repairQuery);
                } catch (Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                    $messages[] = "<span style=\"color:red\">[ERROR]Could not repair the table ".$oneTableName." : ".$errorMessage."</span>";
                } else {
                    $messages[] = "<span style=\"color:green\">[OK]Problem solved : Table ".$oneTableName." repaired</span>";
                }
                continue;
            }

            try {
                $isError = acym_query($createTable[$oneTableName]);
            } catch (Exception $e) {
                $isError = null;
            }

            if ($isError === null) {
                $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                $messages[] = "<span style=\"color:red\">[ERROR]Could not create the table ".$oneTableName." : ".$errorMessage."</span>";
            } else {
                $messages[] = "<span style=\"color:green\">[OK]Problem solved : Table ".$oneTableName." created</span>";
            }
        }

        foreach ($tableNames as $oneTableName) {
            if (empty($columnNames[$oneTableName])) {
                continue;
            }

            $idealColumnNames = array_keys($structure[$oneTableName]);
            $missingColumns = array_diff($idealColumnNames, $columnNames[$oneTableName]);

            if (!empty($missingColumns)) {
                foreach ($missingColumns as $oneColumn) {
                    $messages[] = "<span style=\"color:blue\">Column ".$oneColumn." missing in ".$oneTableName."</span>";
                    try {
                        $isError = acym_query("ALTER TABLE ".$oneTableName." ADD ".$structure[$oneTableName][$oneColumn]);
                    } catch (Exception $e) {
                        $isError = null;
                    }
                    if ($isError === null) {
                        $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                        $messages[] = "<span style=\"color:red\">[ERROR]Could not add the column ".$oneColumn." on the table ".$oneTableName." : ".$errorMessage."</span>";
                    } else {
                        $messages[] = "<span style=\"color:green\">[OK]Problem solved : Added ".$oneColumn." in ".$oneTableName."</span>";
                    }
                }
            }




            $results = acym_loadObjectList('SHOW INDEX FROM '.$oneTableName, 'Key_name');
            if (empty($results)) {
                $results = array();
            }

            foreach ($indexes[$oneTableName] as $name => $query) {
                $name = acym_prepareQuery($name);
                if (in_array($name, array_keys($results))) {
                    continue;
                }


                $keyName = $name == 'PRIMARY' ? 'primary key' : 'index '.$name;

                $messages[] = "<span style=\"color:blue\">".$keyName." missing in ".$oneTableName."</span>";
                try {
                    $isError = acym_query('ALTER TABLE '.$oneTableName.' ADD '.$query);
                } catch (Exception $e) {
                    $isError = null;
                }

                if ($isError === null) {
                    $errorMessage = (isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200));
                    $messages[] = "<span style=\"color:red\">[ERROR]Could not add the ".$keyName." on the table ".$oneTableName." : ".$errorMessage."</span>";
                } else {
                    $messages[] = "<span style=\"color:green\">[OK]Problem solved : Added ".$keyName." to ".$oneTableName."</span>";
                }
            }
        }

        if (empty($messages)) {
            echo '<i class="fa fa-check-circle-o acym__color__green"></i>';
        } else {
            echo implode('<br />', $messages);
        }

        exit;
    }

    function store()
    {
        acym_checkToken();

        $formData = acym_getVar('array', 'config', array());
        if (empty($formData)) {
            return false;
        }

        if ($formData['from_as_replyto'] == 1) {
            $formData['replyto_name'] = $formData['from_name'];
            $formData['replyto_email'] = $formData['from_email'];
        }


        $config = acym_config();
        $status = $config->save($formData);

        if ($status) {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 0);
        }

        $config->load();
    }

    public function test()
    {
        $this->store();

        $config = acym_config();
        $mailerHelper = acym_get('helper.mailer');
        $addedName = $config->get('add_names', true) ? $mailerHelper->cleanText(acym_currentUserName()) : '';

        $mailerHelper->AddAddress(acym_currentUserEmail(), $addedName);
        $mailerHelper->Subject = 'Test e-mail from '.ACYM_LIVE;
        $mailerHelper->Body = acym_translation('ACYM_TEST_EMAIL');
        $mailerHelper->SMTPDebug = 1;
        if (acym_isDebug()) {
            $mailerHelper->SMTPDebug = 2;
        }

        $mailerHelper->isHTML(false);
        $result = $mailerHelper->send();

        if (!$result) {
            $sendingMethod = $config->get('mailer_method');

            if ($sendingMethod == 'smtp') {
                if ($config->get('smtp_secured') == 'ssl' && !function_exists('openssl_sign')) {
                    acym_enqueueNotification(acym_translation('ACYM_OPENSSL'), 'notice');
                }

                if (!$config->get('smtp_auth') && strlen($config->get('smtp_password')) > 1) {
                    acym_enqueueNotification(acym_translation('ACYM_ADVICE_SMTP_AUTH'), 'notice');
                }

                if ($config->get('smtp_port') && !in_array($config->get('smtp_port'), [25, 2525, 465, 587])) {
                    acym_enqueueNotification(acym_translation_sprintf('ACYM_ADVICE_PORT', $config->get('smtp_port')), 'notice');
                }
            }

            if ((strpos(ACYM_LIVE, 'localhost') || strpos(ACYM_LIVE, '127.0.0.1')) && in_array($sendingMethod, array('sendmail', 'qmail', 'mail'))) {
                acym_enqueueNotification(acym_translation('ACYM_ADVICE_LOCALHOST'), 'notice');
            }

            $bounce = $config->get('bounce_email');
            if (!empty($bounce) && !in_array($sendingMethod, ['smtp', 'elasticemail'])) {
                acym_enqueueNotification(acym_translation_sprintf('ACYM_ADVICE_BOUNCE', '<b>'.$bounce.'</b>'), 'notice');
            }
        }

        $this->listing();
    }

    public function ports()
    {
        if (!function_exists('fsockopen')) {
            echo '<span style="color:red">'.acym_translation('ACYM_FSOCKOPEN').'</span>';
            exit;
        }

        $tests = array(25 => 'smtp.sendgrid.com', 2525 => 'smtp.sendgrid.com', 587 => 'smtp.sendgrid.com', 465 => 'ssl://smtp.sendgrid.com');
        $total = 0;
        foreach ($tests as $port => $server) {
            $fp = @fsockopen($server, $port, $errno, $errstr, 5);
            if ($fp) {
                echo '<br /><span style="color:#3dea91" >'.acym_translation_sprintf('ACYM_SMTP_AVAILABLE_PORT', $port).'</span>';
                fclose($fp);
                $total++;
            } else {
                echo '<br /><span style="color:#ff5259" >'.acym_translation_sprintf('ACYM_SMTP_NOT_AVAILABLE_PORT', $port, $errno.' - '.utf8_encode($errstr)).'</span>';
            }
        }

        exit;
    }

    public function detecttimeout()
    {
        acym_query("REPLACE INTO `#__acym_configuration` (`name`,`value`) VALUES ('max_execution_time','5'), ('last_maxexec_check','".time()."')");
        @ini_set('max_execution_time', 600);
        @ignore_user_abort(true);
        $i = 0;
        while ($i < 480) {
            sleep(8);
            $i += 10;
            acym_query("UPDATE `#__acym_configuration` SET `value` = '".intval($i)."' WHERE `name` = 'max_execution_time'");
            acym_query("UPDATE `#__acym_configuration` SET `value` = '".time()."' WHERE `name` = 'last_maxexec_check'");
            sleep(2);
        }
        exit;
    }

    public function deletereport()
    {
        $config = acym_config();
        $path = trim(html_entity_decode($config->get('cron_savepath')));
        if (!preg_match('#^[a-z0-9/_\-{}]*\.log$#i', $path)) {
            acym_enqueueNotification(acym_translation('ACYM_WRONG_LOG_NAME'), 'error', 6000);

            return;
        }

        $path = str_replace(['{year}', '{month}'], [date('Y'), date('m')], $config->get('cron_savepath'));
        $reportPath = acym_cleanPath(ACYM_ROOT.$path);

        if (is_file($reportPath)) {
            $result = acym_deleteFile($reportPath);
            if ($result) {
                acym_enqueueNotification(acym_translation('ACYM_SUCC_DELETE_LOG'), 'success', 4000);
            } else {
                acym_enqueueNotification(acym_translation('ACYM_ERROR_DELETE_LOG'), 'error', 4000);
            }
        } else {
            acym_enqueueNotification(acym_translation('ACYM_EXIST_LOG'), 'info', 4000);
        }

        return $this->listing();
    }

    public function seereport()
    {
        $config = acym_config();

        $path = trim(html_entity_decode($config->get('cron_savepath')));
        if (!preg_match('#^[a-z0-9/_\-{}]*\.log$#i', $path)) {
            acym_display(acym_translation('ACYM_WRONG_LOG_NAME'), 'error');
        }

        $path = str_replace(array('{year}', '{month}'), array(date('Y'), date('m')), $path);
        $reportPath = acym_cleanPath(ACYM_ROOT.$path);

        if (file_exists($reportPath) && !is_dir($reportPath)) {
            try {
                $lines = 5000;
                $f = fopen($reportPath, "rb");
                fseek($f, -1, SEEK_END);
                if (fread($f, 1) != "\n") {
                    $lines -= 1;
                }

                $report = '';
                while (ftell($f) > 0 && $lines >= 0) {
                    $seek = min(ftell($f), 4096); // Figure out how far back we should jump
                    fseek($f, -$seek, SEEK_CUR);
                    $report = ($chunk = fread($f, $seek)).$report; // Get the line
                    fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
                    $lines -= substr_count($chunk, "\n"); // Move to previous line
                }

                while ($lines++ < 0) {
                    $report = substr($report, strpos($report, "\n") + 1);
                }
                fclose($f);
            } catch (Exception $e) {
                $report = '';
            }
        }

        if (empty($report)) {
            $report = acym_translation('ACYM_EMPTY_LOG');
        }

        echo nl2br($report);
        exit;
    }

    public function redomigration()
    {
        $config = acym_config();
        $newConfig = new stdClass();
        $newConfig->migration = 0;
        $config->save($newConfig);

        acym_redirect(acym_completeLink('dashboard', false, true));
    }
}
