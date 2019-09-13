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

class acymexportHelper
{
    public function setDownloadHeaders($filename = 'export', $extension = 'csv')
    {
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        header("Content-Disposition: attachment; filename=".$filename.".".$extension);
        header("Content-Transfer-Encoding: binary");
    }

    public function exportCSV($query, $fieldsToExport, $customFieldsToExport, $separator = ',', $charset = 'UTF-8')
    {
        @ob_clean();

        $filename = "export_".date('Y-m-d');
        $this->setDownloadHeaders($filename);
        $nbExport = $this->getExportLimit();

        acym_displayErrors();
        $encodingClass = acym_get('helper.encoding');
        $config = acym_config();

        $eol = "\r\n";
        $before = '"';
        $separator = '"'.$separator.'"';
        $after = '"';

        echo $before.implode($separator, array_merge($fieldsToExport, $customFieldsToExport)).$after.$eol;

        $start = 0;
        do {
            $users = acym_loadObjectList($query.' LIMIT '.intval($start).', '.intval($nbExport), 'id');
            $start += $nbExport;

            if ($users === false) {
                echo $eol.$eol.'Error : '.acym_getDBError();
            }

            if (empty($users)) {
                break;
            }

            foreach ($users as $userID => $oneUser) {
                unset($oneUser->id);

                $data = get_object_vars($oneUser);

                if (!empty($customFieldsToExport)) {
                    $userCustomFields = acym_loadObjectList(
                        'SELECT field_id, value 
                        FROM #__acym_user_has_field 
                        WHERE user_id = '.intval($userID).' AND field_id IN ('.implode(',', array_keys($customFieldsToExport)).')
                        ORDER BY field_id',
                        'field_id'
                    );

                    foreach ($customFieldsToExport as $fieldID => $fieldName) {
                        $data[] = empty($userCustomFields[$fieldID]) ? '' : $userCustomFields[$fieldID]->value;
                    }
                }

                $excelSecure = $config->get('export_excelsecurity', 0);
                foreach ($data as &$oneData) {
                    if ($excelSecure == 1) {
                        $firstcharacter = substr($oneData, 0, 1);
                        if (in_array($firstcharacter, ['=', '+', '-', '@'])) {
                            $oneData = '	'.$oneData;
                        }
                    }

                    $oneData = acym_escape($oneData);
                }

                $dataexport = implode($separator, $data);

                echo $before.$encodingClass->change($dataexport, 'UTF-8', $charset).$after.$eol;
            }

            unset($users);
        } while (true);
    }

    private function getExportLimit()
    {
        $serverLimit = acym_bytes(ini_get('memory_limit'));
        if ($serverLimit > 150000000) {
            return 50000;
        } elseif ($serverLimit > 80000000) {
            return 15000;
        } else {
            return 5000;
        }
    }
}

