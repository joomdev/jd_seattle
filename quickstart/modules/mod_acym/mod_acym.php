<?php
defined('_JEXEC') or die('Restricted access');
?><?php

if (!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
    echo 'This module cannot work without AcyMailing';

    return;
};

acym_initModule($params);

$identifiedUser = null;
$currentUserEmail = acym_currentUserEmail();
if ($params->get('userinfo', '1') == '1' && !empty($currentUserEmail)) {
    $userClass = acym_get('class.user');
    $identifiedUser = $userClass->getOneByEmail($currentUserEmail);
}

$visibleLists = $params->get('displists', []);
$hiddenLists = $params->get('hiddenlists', []);
$fields = $params->get('fields', []);
$allfields = is_array($fields) ? $fields : explode(',', $fields);
if (!in_array('2', $allfields)) {
    $allfields[] = 2;
}
acym_arrayToInteger($visibleLists);
acym_arrayToInteger($hiddenLists);
acym_arrayToInteger($allfields);

$listClass = acym_get('class.list');
$fieldClass = acym_get('class.field');

$allLists = $listClass->getAllWIthoutManagement();
$visibleLists = array_intersect($visibleLists, array_keys($allLists));
$hiddenLists = array_intersect($hiddenLists, array_keys($allLists));
$allfields = $fieldClass->getFieldsByID($allfields);
$fields = [];
foreach ($allfields as $field) {
    if($field->active === '0') continue;
    $fields[$field->id] = $field;
}

if (empty($visibleLists) && empty($hiddenLists)) {
    $hiddenLists = array_keys($allLists);
}

if (!empty($visibleLists) && !empty($hiddenLists)) {
    $visibleLists = array_diff($visibleLists, $hiddenLists);
}

if (empty($identifiedUser->id)) {
    $checkedLists = $params->get('listschecked', []);
    if (!is_array($checkedLists)) {
        if (strtolower($checkedLists) == 'all') {
            $checkedLists = $visibleLists;
        } elseif (strpos($checkedLists, ',') || is_numeric($checkedLists)) {
            $checkedLists = explode(',', $checkedLists);
        } else {
            $checkedLists = [];
        }
    }
} else {
    $checkedLists = [];
    $userLists = $userClass->getUserSubscriptionById($identifiedUser->id);

    $countSub = 0;
    $countUnsub = 0;
    $formLists = array_merge($visibleLists, $hiddenLists);
    foreach ($formLists as $idOneList) {
        if (empty($userLists[$idOneList]) || $userLists[$idOneList]->status == 0) {
            $countSub++;
        } else {
            $countUnsub++;
            $checkedLists[] = $idOneList;
        }
    }
}
acym_arrayToInteger($checkedLists);


$config = acym_config();

$subscribeText = $params->get('subtext', 'ACYM_SUBSCRIBE');
if (!empty($identifiedUser->id)) $subscribeText = $params->get('subtextlogged', 'ACYM_SUBSCRIBE');
$unsubscribeText = $params->get('unsubtext', 'ACYM_UNSUBSCRIBE');

$listPosition = $params->get('listposition', 'before');
$displayOutside = $params->get('textmode') == '0';

$redirectURL = $params->get('redirect', '');
$unsubRedirectURL = $params->get('unsubredirect', '');
$ajax = empty($redirectURL) && empty($unsubRedirectURL) ? '1' : '0';

$formClass = $params->get('formclass', '');
$alignment = $params->get('alignment', 'none');
$style = $alignment == 'none' ? '' : 'style="text-align: '.$alignment.'"';

$termsURL = acym_getArticleURL(
    $params->get('termscontent', 0),
    $params->get('articlepopup', 1),
    'ACYM_TERMS_CONDITIONS',
    acym_translation('ACYM_TERMS_CONDITIONS')
);
$privacyURL = acym_getArticleURL(
    $params->get('privacypolicy', 0),
    $params->get('articlepopup', 1),
    'ACYM_PRIVACY_POLICY',
    acym_translation('ACYM_PRIVACY_POLICY')
);

if (empty($termsURL) && empty($privacyURL)) {
    $termslink = '';
} elseif (empty($privacyURL)) {
    $termslink = acym_translation_sprintf('ACYM_I_AGREE_TERMS', $termsURL);
} elseif (empty($termsURL)) {
    $termslink = acym_translation_sprintf('ACYM_I_AGREE_PRIVACY', $privacyURL);
} else {
    $termslink = acym_translation_sprintf('ACYM_I_AGREE_BOTH', $termsURL, $privacyURL);
}


$formName = acym_getModuleFormName();
$formAction = htmlspecialchars_decode(acym_completeLink('frontusers', true, true));

$js = "window.addEventListener('DOMContentLoaded', (event) => {";
$js .= "\n"."acymModule['excludeValues".$formName."'] = [];";
$fieldsToDisplay = [];
foreach ($fields as $field) {
    $fieldsToDisplay[$field->id] = $field->name;
    $js .= "\n"."acymModule['excludeValues".$formName."']['".$field->id."'] = '".acym_translation($field->name, true)."';";
}
$js .= "  });";
echo "<script type=\"text/javascript\">
        <!--
        $js
        //-->
        </script>";
?>
	<div class="acym_module <?php echo acym_escape($formClass); ?>" id="acym_module_<?php echo $formName; ?>">
		<div class="acym_fulldiv" id="acym_fulldiv_<?php echo $formName; ?>" <?php echo $style; ?>>
			<form enctype="multipart/form-data" id="<?php echo acym_escape($formName); ?>" name="<?php echo acym_escape($formName); ?>" method="POST" action="<?php echo acym_escape($formAction); ?>" onsubmit="return submitAcymForm('subscribe','<?php echo $formName; ?>', 'acySubmitSubForm')">
				<div class="acym_module_form">
                    <?php
                    $introText = $params->get('introtext', '');
                    if (!empty($introText)) {
                        echo '<div class="acym_introtext">'.$introText.'</div>';
                    }

                    if ($params->get('mode', 'tableless') == 'tableless') {
                        $view = 'tableless.php';
                    } else {
                        $displayInline = $params->get('mode', 'tableless') != 'vertical';
                        $view = 'default.php';
                    }

                    $app = JFactory::getApplication('site');
                    $template = $app->getTemplate();
                    if (file_exists(str_replace(DS, '/', ACYM_ROOT).'templates/'.$template.'/html/mod_acym/'.$view)) {
                        include ACYM_ROOT.'templates'.DS.$template.DS.'html'.DS.'mod_acym'.DS.$view;
                    } else {
                        include __DIR__.DS.'tmpl'.DS.$view;
                    }

                    ?>
				</div>

				<input type="hidden" name="ctrl" value="frontusers" />
				<input type="hidden" name="task" value="notask" />
				<input type="hidden" name="option" value="<?php echo acym_escape(ACYM_COMPONENT); ?>" />

                <?php
                $currentEmail = acym_currentUserEmail();
                if (!empty($currentEmail)) {
                    echo '<span style="display:none">{emailcloak=off}</span>';
                }

                if (!empty($redirectURL)) echo '<input type="hidden" name="redirect" value="'.acym_escape($redirectURL).'"/>';
                if (!empty($unsubRedirectURL)) echo '<input type="hidden" name="redirectunsub" value="'.acym_escape($unsubRedirectURL).'"/>';

                ?>

				<input type="hidden" name="ajax" value="<?php echo acym_escape($ajax); ?>" />
				<input type="hidden" name="acy_source" value="<?php echo acym_escape($params->get('source', 'mod_'.$module->id)); ?>" />
				<input type="hidden" name="hiddenlists" value="<?php echo implode(',', $hiddenLists); ?>" />
				<input type="hidden" name="fields" value="<?php echo 'name,email'; ?>" />
				<input type="hidden" name="acyformname" value="<?php echo acym_escape($formName); ?>" />
				<input type="hidden" name="acysubmode" value="mod_acym" />

                <?php
                $postText = $params->get('posttext', '');
                if (!empty($postText)) {
                    echo '<div class="acym_posttext">'.$postText.'</div>';
                }
                ?>
			</form>
		</div>
	</div>
<?php

