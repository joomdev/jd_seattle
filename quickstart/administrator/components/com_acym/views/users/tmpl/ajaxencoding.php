<?php
defined('_JEXEC') or die('Restricted access');
?><?php
$encodingHelper = acym_get('helper.encoding');
$filename = strtolower(acym_getVar('cmd', 'filename'));
$encoding = acym_getVar('cmd', 'encoding');

$extension = '.'.acym_fileGetExt($filename);
$uploadPath = ACYM_MEDIA.'import'.DS.str_replace(['.', ' '], '_', substr($filename, 0, strpos($filename, $extension))).$extension;

if (!file_exists($uploadPath)) {
    acym_display(acym_translation_sprintf('ACYM_FAIL_OPEN', '<b><i>'.acym_escape($uploadPath).'</i></b>'), 'error');

    return;
}

$this->content = file_get_contents($uploadPath);

if (empty($encoding)) {
    $encoding = $encodingHelper->detectEncoding($this->content);
}

$content = $encodingHelper->change($this->content, $encoding, 'UTF-8');


$content = str_replace(["\r\n", "\r"], "\n", $content);
$this->lines = explode("\n", $content);

$this->separator = ',';
$listSeparators = ["\t", ';', ','];
foreach ($listSeparators as $sep) {
    if (strpos($this->lines[0], $sep) !== false) {
        $this->separator = $sep;
        break;
    }
}

$nbPreviewLines = 0;
$i = 0;
$data = [];

while (isset($this->lines[$i])) {
    if (empty($this->lines[$i])) {
        unset($this->lines[$i]);
        continue;
    } else {
        $nbPreviewLines++;
    }

    if (strpos($this->lines[$i], '"') !== false) {
        $data[$i] = [];
        $j = $i + 1;
        $position = -1;

        while ($j < ($i + 30)) {
            $quoteOpened = substr($this->lines[$i], $position + 1, 1) == '"';

            if ($quoteOpened) {
                $nextQuotePosition = strpos($this->lines[$i], '"', $position + 2);

                if ($nextQuotePosition === false) {
                    if (!isset($this->lines[$j])) {
                        break;
                    }

                    $this->lines[$i] .= "\n".rtrim($this->lines[$j], $this->separator);
                    unset($this->lines[$j]);
                    $j++;
                    continue;
                } else {
                    $quoteOpened = false;

                    if (strlen($this->lines[$i]) - 1 == $nextQuotePosition) {
                        $data[$i][] = substr($this->lines[$i], $position + 1);
                        break;
                    }

                    $data[$i][] = substr($this->lines[$i], $position + 1, $nextQuotePosition + 1 - ($position + 1));
                    $position = $nextQuotePosition + 1;
                }
            } else {
                $nextSeparatorPosition = strpos($this->lines[$i], $this->separator, $position + 1);
                if ($nextSeparatorPosition === false) {
                    $data[$i][] = substr($this->lines[$i], $position + 1);
                    break;
                } else {
                    $data[$i][] = substr($this->lines[$i], $position + 1, $nextSeparatorPosition - ($position + 1));
                    $position = $nextSeparatorPosition;
                }
            }
        }

        $this->lines = array_merge($this->lines);
    } else {
        $data[$i] = explode($this->separator, rtrim(trim($this->lines[$i]), $this->separator));
    }

    if ($nbPreviewLines == 10) {
        break;
    }

    if ($nbPreviewLines != 1) {
        $i++;
        continue;
    }

    if (strpos($this->lines[$i], '@')) {
        $noHeader = 1;
    } else {
        $noHeader = 0;
    }

    $columnNames = explode($this->separator, $this->lines[$i]);
    $nbColumns = count($columnNames);
    if (!empty($i)) {
        unset($this->lines[$i]);
    }
    ksort($this->lines);
}

$this->lines = $data;
$nbLines = count($this->lines);

?>
<div class="table-scroll">
	<table cellspacing="10" cellpadding="10" id="importdata" class="unstriped">
        <?php
        if ($noHeader || !isset($this->lines[1])) {
            $firstValueLine = $columnNames;
        } else {
            $firstValueLine = $this->lines[1];
            foreach ($firstValueLine as &$oneValue) {
                $oneValue = trim($oneValue, '\'" ');
            }
        }

        $fieldAssignment = [];

        $fieldAssignment[] = acym_selectOption('0', 'ACYM_UNASSIGNED', 'value', 'text');
        $fieldAssignment[] = acym_selectOption('1', 'ACYM_IGNORE');
        $separator = acym_selectOption('3', '----------------------');
        $separator->disable = true;
        $fieldAssignment[] = $separator;


        $userClass = acym_get('class.user');
        $fields = $userClass->getAllColumnsUserAndCustomField();
        if (acym_isAdmin()) {
            $fields['listids'] = 'listids';
            $fields['listname'] = 'listname';
        }

        $cleanFields = [];
        foreach ($fields as $value => $label) {
            if (in_array($value, ['id', 'automation'])) continue;
            if (is_numeric($value)) $value = 'cf_'.$value;
            $fieldAssignment[] = acym_selectOption($value, $label);
            $cleanFields[$value] = strtolower($label);
        }


        $cleanFields['1'] = acym_translation('ACYM_IGNORE');

        echo '<tr>';

        $alreadyFound = [];

        foreach ($columnNames as $key => $oneColumn) {
            $columnNames[$key] = strtolower(trim($columnNames[$key], '\'" '));

            $customValue = '';

            $selectedField = acym_getVar('cmd', 'fieldAssignment'.$key, '');

            if (empty($selectedField) && $selectedField !== 0) {
                if (isset($cleanFields[$columnNames[$key]])) {
                    $selectedField = $columnNames[$key];
                } elseif (in_array($columnNames[$key], $cleanFields)) {
                    $selectedField = array_search(strtolower($columnNames[$key]), $cleanFields);
                } else {
                    $selectedField = '0';
                }

                if (!$selectedField && !empty($firstValueLine)) {
                    if (isset($firstValueLine[$key]) && strpos($firstValueLine[$key], '@')) {
                        $selectedField = 'email';
                    } elseif ($nbColumns == 2) {
                        $selectedField = 'name';
                    }
                }
                if (in_array($selectedField, $alreadyFound)) {
                    $selectedField = '0';
                }
            } elseif ($selectedField == 2) {
                $customValue = acym_getVar('cmd', 'newcustom'.$key);
            }

            $alreadyFound[] = $selectedField;

            echo '<td valign="top">'.acym_select(
                    $fieldAssignment,
                    'fieldAssignment'.$key,
                    $selectedField,
                    'class="fieldAssignment"',
                    'value',
                    'text'
                ).'<br />';
        }
        echo '</tr>';

        if (!$noHeader) {
            foreach ($columnNames as $key => $oneColumn) {
                $columnNames[$key] = htmlspecialchars($columnNames[$key], ENT_COMPAT | ENT_IGNORE, 'UTF-8');
            }
            echo '<tr class="acym__users__import__generic__column_name"><td><b>'.implode('</b></td><td><b>', $columnNames).'</b></td></tr>';
        }

        for ($i = 1 - $noHeader ; $i < 11 - $noHeader && $i < $nbLines ; $i++) {
            $values = $this->lines[$i];

            echo '<tr>';
            foreach ($values as &$oneValue) {
                $oneValue = htmlspecialchars(trim($oneValue, '\'" '), ENT_COMPAT | ENT_IGNORE, 'UTF-8');
                echo '<td>'.htmlspecialchars_decode($oneValue).'</td>';
            }
            echo '</tr>';
        }
        ?>
	</table>
</div>

