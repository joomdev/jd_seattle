<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_prepareAjaxURL(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
	<div class="grid-x acym__content" id="acym__users__export">
		<!--<div class="cell grid-x align-right margin-bottom-1">-->
		<!--	<h5 class="cell auto font-bold">--><?php //echo acym_translation('ACYM_EXPORT'); ?><!--</h5>-->
		<!--</div>-->
		<div class="cell grid-x grid-margin-x">
			<div class="cell acym_area medium-6 acym__content">
				<div class="acym_area_title"><?php echo acym_translation('ACYM_FIELDS_TO_EXPORT'); ?></div>
				<p><span id="acym__users__export__check_all_field"><?php echo strtolower(acym_translation('ACYM_ALL')) ?></span> | <span id="acym__users__export__check_default_field"><?php echo strtolower(acym_translation('ACYM_DEFAULT')) ?></span></p>
				<div class="margin-bottom-1">
                    <?php
                    $defaultFields = explode(',', $data['config']->get('export_fields', 'name,email'));
                    foreach ($data['fields'] as $fieldName) {
                        if ($fieldName == 'id') continue;

                        $checked = in_array($fieldName, $defaultFields) ? 'checked="checked"' : '';
                        echo '<input '.$checked.' id="checkbox_'.$fieldName.'" class="acym__users__export__export_fields smaller-checkbox" type="checkbox" name="export_fields[]" value="'.$fieldName.'">
                        	<label for="checkbox_'.$fieldName.'">'.$fieldName.'</label><br/>';
                    }

                    foreach ($data['customfields'] as $field) {
                        if ($field->type == 'file' || in_array($field->id, [1, 2])) continue;

                        $checked = in_array($field->id, $defaultFields) ? 'checked="checked"' : '';
                        $fieldName = $field->name;

                        echo '<input '.$checked.' id="checkbox_'.$fieldName.'" class="acym__users__export__export_fields smaller-checkbox" type="checkbox" name="export_fields[]" value="'.$field->id.'">
                        	<label for="checkbox_'.$fieldName.'">'.$fieldName.'</label><br/>';
                    }
                    ?>
				</div>
				<div class="grid-x margin-bottom-1" id="userField_separator">
					<label class="cell"><?php echo acym_translation('ACYM_SEPARATOR'); ?>
                        <?php echo acym_radio([';' => acym_translation('ACYM_SEMICOLON'), ',' => acym_translation('ACYM_COMMA')], "export_separator", $data['config']->get('export_separator', ',')); ?>
					</label>
					<div class="cell medium-auto"></div>
				</div>
				<div class="grid-x">
					<label class="cell medium-6 xxlarge-3"><?php echo acym_translation('ACYM_ENCODING'); ?>
                        <?php $encodingHelper = acym_get('helper.encoding');
                        echo $encodingHelper->charsetField('export_charset', $data['config']->get('export_charset', 'UTF-8')); ?>
					</label>
					<div class="cell medium-auto"></div>
				</div>
				<div class="grid-x" id="userField_excel">
					<label class="cell"><?php echo acym_tooltip(acym_translation('ACYM_EXCEL_SECURITY'), acym_translation('ACYM_EXCEL_SECURITY_DESC')); ?>
                        <?php echo acym_boolean("export_excelsecurity", $data['config']->get('export_excelsecurity', 0)); ?>
					</label>
					<div class="cell medium-auto"></div>
				</div>
			</div>
			<div class="cell acym_area medium-6 acym__content">
				<div class="acym_area_title"><?php echo acym_translation('ACYM_USERS_TO_EXPORT') ?></div>
                <?php if (empty($data['checkedUsers'])) { ?>
					<fieldset id="acym__users__export__users-to-export" class="margin-bottom-1">
                        <?php echo acym_radio(['all' => acym_translation('ACYM_ALL_USERS'), 'list' => acym_translation('ACYM_USERS_FROM_LISTS')], 'export_users-to-export', 'all'); ?>
					</fieldset>
					<div id="acym__users__export__select_all">
						<h1><?php echo acym_translation('ACYM_ALL_USER_WILL_BE_EXPORTED') ?></h1>
					</div>
					<div id="acym__users__export__select_lists" class="margin-bottom-1" style="display: none">
                        <?php
                        echo acym_modal_pagination_lists('', "", null, null, "", false);
                        ?>
						<div class="margin-bottom-1 margin-top-1">
                            <?php
                            $exportSub = array(
                                'sub' => acym_translation('ACYM_SUBSCRIBED_USER'),
                                'unsub' => acym_translation('ACYM_UNSUBSCRIBED_USER'),
                                'all' => acym_translation('ACYM_EXPORT_BOTH'),
                            );
                            echo acym_radio($exportSub, 'export_list', 'all');
                            ?>
						</div>
					</div>
                <?php } else { ?>
					<input type="hidden" name="selected_users" value="<?php echo implode(',', $data['checkedUsers']); ?>"/>
					<div class="grid-x">
                        <?php
                        $userClass = acym_get('class.user');
                        foreach ($data['checkedUsers'] as $id) {
                            $user = $userClass->getOneById($id);
                            echo '<div class="cell grid-x acym__listing__row">';
                            echo '    <div class="cell small-6">'.$user->name.'</div>
                                      <div class="cell small-6">'.$user->email.'</div>';
                            echo '</div>';
                        }
                        ?>
					</div>
                <?php } ?>
			</div>
		</div>
		<div class="cell grid-x margin-top-2">
			<div class="medium-auto"></div>
			<div class="cell grid-x medium-shrink">
				<button type="button" data-task="doexport" class="cell button acy_button_submit" id="acym__export__button">
                    <?php echo acym_translation('ACYM_EXPORT_USERS'); ?>
				</button>
				<p class="acym__color__dark-gray cell text-center"><?php echo acym_translation('ACYM_DATA_WILL_EXPORT_CSV_FORMAT') ?></p>
			</div>
			<div class="medium-auto"></div>
		</div>
	</div>

    <?php echo acym_formOptions(); ?>
</form>
