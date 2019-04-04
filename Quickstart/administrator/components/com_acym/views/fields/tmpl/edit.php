<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>
	<input type="hidden" name="id" value="<?php echo empty($data['field']->id) ? '' : $data['field']->id ?>">
	<input type="hidden" name="namekey" value="<?php echo empty($data['field']->namekey) ? '' : $data['field']->namekey ?>">
	<div id="acym__fields__edit" class="acym__content grid-x cell">
		<div class="cell grid-x grid-margin-x">
			<div class="cell auto hide-for-small-only"></div>
			<button data-task="apply" class="cell button button-secondary medium-shrink small-12 acy_button_submit"><?php echo acym_translation('ACYM_SAVE') ?></button>
			<button data-task="save" class="cell button medium-shrink small-12 acy_button_submit"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
		</div>
		<div class="cell grid-x grid-margin-x grid-margin-y">
			<div class="<?php echo in_array($data['field']->id, array(1, 2)) ? '' : 'medium-6 '; ?>cell grid-x acym__fields__edit__field acym__content">
				<h1 class="acym__title__listing margin-right-1 cell margin-bottom-1"><?php echo acym_translation('ACYM_CUSTOM_FIELDS') ?></h1>
				<label class="large-5 cell"><?php echo acym_translation('ACYM_NAME') ?>
					<input required type="text" name="field[name]" value="<?php echo empty($data['field']->name) ? '' : $data['field']->name; ?>">
				</label>
				<div class="cell large-1"></div>
				<label class="cell large-5"><?php echo acym_translation('ACYM_FIELD_TYPE') ?>
                    <?php
                    echo acym_select($data['fieldType'], 'field[type]', $data['field']->type, 'class="acym__fields__edit__select"'.((!empty($data['field']->id) && in_array($data['field']->id, array(1, 2))) ? 'disabled' : ''), 'value', 'name');
                    ?>
				</label>
				<div class="cell grid-x large-6 margin-top-1">
                    <?php echo acym_switch('field[active]', $data['field']->active, acym_translation('ACYM_ACTIVE'), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
				</div>

                <?php if (empty($data['field']->id) || $data['field']->id != 2) { ?>
					<div class="cell grid-x large-6  margin-top-1 acym__fields__change" id="acym__fields__required">
                        <?php echo acym_switch('field[required]', $data['field']->required, acym_translation('ACYM_REQUIRED'), array(), 'shrink', 'auto', 'tiny margin-0', 'required_error_message'); ?>
					</div>
                <?php } ?>

				<!--It's in general like the user didn't fill the field we display that message-->
                <?php if (empty($data['field']->id) || $data['field']->id != 2) { ?>
					<div class="cell margin-top-1 large-11" id="required_error_message">
						<label class="acym__fields__change" id="acym__fields__error-message"><?php echo acym_translation('ACYM_CUSTOM_ERROR') ?>
							<input type="text" name="field[option][error_message]" value="<?php echo empty($data['field']->option->error_message) ? '' : acym_escape($data['field']->option->error_message); ?>" placeholder="<?php echo acym_escape(acym_translation_sprintf('ACYM_DEFAULT_REQUIRED_MESSAGE', 'xxx')); ?>">
						</label>
					</div>
                <?php } ?>

				<div class="cell grid-x margin-top-1 acym__fields__change" id="acym__fields__editable-user-creation">
                    <?php echo acym_switch('field[option][editable_user_creation]', empty($data['field']->option) ? 1 : $data['field']->option->editable_user_creation, acym_translation('ACYM_EDITABLE_USER_CREATION'), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
				</div>
				<div class="cell grid-x margin-top-1 acym__fields__change" id="acym__fields__editable-user-modification">
                    <?php echo acym_switch('field[option][editable_user_modification]', empty($data['field']->option) ? 1 : $data['field']->option->editable_user_modification, acym_translation('ACYM_EDITABLE_USER_MODIFICATION'), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
				</div>

				<div class="cell margin-top-1 acym__fields__change" id="acym__fields__authorized-content"><?php echo acym_translation('ACYM_AUTHORIZED_CONTENT') ?>
                    <?php
                    $authorizedContent = array('all' => acym_translation('ACYM_ALL'), 'number' => acym_translation('ACYM_NUMBER_ONLY'), 'letters' => acym_translation('ACYM_LETTERS_ONLY'), 'numbers_letters' => acym_translation('ACYM_NUMBERS_LETTERS_ONLY'), 'regex' => ' <input type="text" name="field[option][authorized_content][regex]" placeholder="'.acym_translation('ACYM_REGULAR_EXPRESSION', true).'">');
                    echo acym_radio($authorizedContent, 'field[option][authorized_content][]', empty($data['field']->option) ? 'all' : $data['field']->option->authorized_content->{'0'});
                    ?>
				</div>
				<!--if the user didn't respect the authorized content then we display the message below-->
				<label class="cell margin-top-2 large-11 acym__fields__change" id="acym__fields__error-message-invalid"><?php echo acym_translation('ACYM_ERROR_MESSAGE_INVALID_CONTENT') ?>
					<input type="text" name="field[option][error_message_invalid]" value="<?php echo empty($data['field']->option->error_message_invalid) ? '' : $data['field']->option->error_message_invalid; ?>">
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__default-value"><?php echo acym_translation('ACYM_DEFAULT_VALUE') ?>
					<input type="text" name="field[default_value]" value="<?php echo empty($data['field']->default_value) ? '' : $data['field']->default_value; ?>">
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__format"><?php
                    echo acym_tooltip('<h6>Format</h6>', acym_translation_sprintf('ACYM_X_TO_ENTER_X', '%d', acym_translation('ACYM_DAY')).'<br>'.acym_translation_sprintf('ACYM_X_TO_ENTER_X', '%m', acym_translation('ACYM_MONTH')).'<br>'.acym_translation_sprintf('ACYM_X_TO_ENTER_X', '%y', acym_translation('ACYM_YEAR')).'<br>'.acym_translation('ACYM_EXEMPLE_FORMAT'));
                    ?>
					<input type="text" name="field[option][format]" value="<?php echo empty($data['field']->option->format) ? '%d%m%y' : $data['field']->option->format; ?>">
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__size"><?php echo acym_translation('ACYM_INPUT_WIDTH') ?>
					<input type="text" name="field[option][size]" value="<?php echo empty($data['field']->option->size) ? '' : $data['field']->option->size; ?>">
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__rows"><?php echo acym_translation('ACYM_ROWS') ?>
					<input type="text" name="field[option][rows]" value="<?php echo empty($data['field']->option->rows) ? '' : $data['field']->option->rows; ?>">
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__columns"><?php echo acym_translation('ACYM_COLUMNS') ?>
					<input type="text" name="field[option][columns]" value="<?php echo empty($data['field']->option->columns) ? '' : $data['field']->option->columns; ?>">
				</label>

				<label class="cell margin-top-1 large-11 acym__fields__change" id="acym__fields__custom-text"><?php echo acym_translation('ACYM_CUSTOM_TEXT') ?>
					<textarea name="field[option][custom_text]" cols="30" rows="10"><?php echo empty($data['field']->option->custom_text) ? '' : $data['field']->option->custom_text; ?></textarea>
				</label>
				<label class="cell margin-top-1 acym__fields__change large-5" id="acym__fields__html-tag-cat"><?php echo acym_translation('ACYM_HTML_TAG_CATEGORIES') ?>
                    <?php
                    echo acym_radio(array('div' => 'Div', 'fieldset' => 'Fieldset'), 'acym__fields__edit__html-tag-cat', 'div');
                    ?>
				</label>
				<label class="cell margin-top-1 large-5 acym__fields__change" id="acym__fields__css-class"><?php echo acym_translation('ACYM_CSS_CLASS') ?>
					<input type="text" name="field[option][css_class]" value="<?php echo empty($data['field']->option->css_class) ? '' : $data['field']->option->css_class; ?>">
				</label>
				<div class="cell grid-x acym__fields__change large-11 acym__content margin-bottom-2" id="acym__fields__value">
					<div class="grid-x acym__listing">
						<div class="grid-x cell acym__listing__header">
							<div class="medium-4 cell acym__listing__header__title margin-right-1 text-center">
                                <?php echo acym_translation('ACYM_VALUE'); ?>
							</div>
							<div class="medium-4 cell acym__listing__header__title margin-right-1 text-center">
                                <?php echo acym_translation('ACYM_TITLE'); ?>
							</div>
							<div class="medium-3 cell acym__listing__header__title text-center">
                                <?php echo acym_translation('ACYM_DISABLE'); ?>
							</div>
						</div>
						<div class="acym__fields__values__listing__sortable">
                            <?php if (empty($data['field']->value)) { ?>
								<div class="grid-x cell acym__fields__value__sortable acym__content margin-bottom-1 grid-margin-x">
									<div class="medium-1 cell acym_vcenter align-center acym__field__sortable__listing__handle">
										<div class="grabbable acym__sortable__field__edit__handle grid-x">
											<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
											<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
										</div>
									</div>
									<input type="text" name="field[value][value][]" class="cell medium-4" value="">
									<input type="text" name="field[value][title][]" class="cell medium-4" value="">
									<div class="cell medium-2">
                                        <?php echo acym_select(array('n' => acym_translation('ACYM_NO'), 'y' => acym_translation('ACYM_YES')), 'field[value][disabled][]', 'n', 'class="acym__fields__edit__select"', 'value', 'name') ?>
									</div>
								</div>
                            <?php } else {
                                $i = 0;
                                foreach ($data['field']->value as $value) { ?>
									<div class="grid-x cell acym__fields__value__sortable acym__content margin-bottom-1 grid-margin-x">
										<div class="medium-1 cell acym_vcenter align-center acym__field__sortable__listing__handle">
											<div class="grabbable acym__sortable__field__edit__handle grid-x">
												<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
												<i class="fa fa-ellipsis-h cell acym__color__dark-gray"></i>
											</div>
										</div>
										<input type="text" name="field[value][value][]" class="cell medium-4" value="<?php echo $value->value ?>">
										<input type="text" name="field[value][title][]" class="cell medium-4" value="<?php echo $value->title ?>">
										<div class="cell medium-2">
                                            <?php echo acym_select(array('n' => acym_translation('ACYM_NO'), 'y' => acym_translation('ACYM_YES')), 'field[value][disabled][]', $value->disabled, 'class="acym__fields__edit__select"', 'value', 'name') ?>
										</div>
										<i class="cell material-icons small-1 acym__color__red cursor-pointer acym__field__delete__value">close</i>
									</div>
                                    <?php $i++;
                                } ?>
                            <?php } ?>
						</div>
						<button type="button" class="button button-secondary margin-top-1" id="acym__fields__value__add-value"><?php echo acym_translation('ACYM_ADD_VALUE') ?></button>
					</div>
				</div>
				<div class="cell large-11 grid-x acym__fields__change acym__content" id="acym__fields__from-db">
					<h1><?php acym_translation('ACYM_VALUE_FROM_DB') ?></h1>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_DATABASE') ?>
                        <?php echo acym_select($data['database'], 'fieldDB[database]', empty($data['field']->fieldDB->database) ? '' : $data['field']->fieldDB->database, 'class="acym__fields__edit__select"', 'value', 'name') ?>
					</label>
					<div class="medium-1"></div>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_TABLES') ?>
                        <?php echo acym_select(empty($data['field']->fieldDB->tables) ? array() : $data['field']->fieldDB->tables, 'fieldDB[table]', empty($data['field']->fieldDB->table) ? '' : $data['field']->fieldDB->table, 'class="acym__fields__edit__select"', 'value', 'name') ?>
					</label>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_VALUE') ?>
                        <?php echo acym_select(empty($data['field']->fieldDB->columns) ? array() : $data['field']->fieldDB->columns, 'fieldDB[value]', empty($data['field']->fieldDB->value) ? '' : $data['field']->fieldDB->value, 'class="acym__fields__edit__select acym__fields__database__columns"', 'value', 'name') ?>
					</label>
					<div class="medium-1"></div>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_TITLE') ?>
                        <?php echo acym_select(empty($data['field']->fieldDB->columns) ? array() : $data['field']->fieldDB->columns, 'fieldDB[title]', empty($data['field']->fieldDB->title) ? '' : $data['field']->fieldDB->title, 'class="acym__fields__edit__select acym__fields__database__columns"', 'value', 'name') ?>
					</label>
					<label class="cell margin-top-1 medium-4 margin-right-1"><?php echo acym_translation('ACYM_WHERE') ?>
                        <?php echo acym_select(empty($data['field']->fieldDB->columns) ? array() : $data['field']->fieldDB->columns, 'fieldDB[where]', empty($data['field']->fieldDB->where) ? '' : $data['field']->fieldDB->where, 'class="acym__fields__edit__select acym__fields__database__columns"', 'value', 'name') ?>
					</label>
					<label class="cell margin-top-1 medium-3 margin-right-1"><?php echo acym_translation('ACYM_WHERE_OPERATION') ?>
                        <?php
                        $operator = acym_get('type.operator');
                        $operator->class = 'acym__fields__edit__select';
                        echo $operator->display('fieldDB[where_sign]', empty($data['field']->fieldDB->where_sign) ? '' : $data['field']->fieldDB->where_sign);
                        ?>
					</label>
					<label class="cell margin-top-1 medium-4"><?php echo acym_translation('ACYM_WHERE_VALUE') ?>
						<input type="text" name="fieldDB[where_value]" value="<?php echo empty($data['field']->fieldDB->where_value) ? '' : $data['field']->fieldDB->where_value ?>">
					</label>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_ORDER_BY') ?>
                        <?php echo acym_select(empty($data['field']->fieldDB->columns) ? array() : $data['field']->fieldDB->columns, 'fieldDB[order_by]', empty($data['field']->fieldDB->order_by) ? '' : $data['field']->fieldDB->order_by, 'class="acym__fields__edit__select acym__fields__database__columns"', 'value', 'name') ?>
					</label>
					<div class="medium-1"></div>
					<label class="cell margin-top-1 medium-5"><?php echo acym_translation('ACYM_SORT_ORDERING') ?>
                        <?php echo acym_select(array('asc' => 'ASC', 'desc' => 'DESC'), 'fieldDB[sort_order]', empty($data['field']->fieldDB->sort_order) ? '' : $data['field']->fieldDB->sort_order, 'class="acym__fields__edit__select"', 'value', 'name') ?>
					</label>
				</div>
			</div>
			<div class="medium-6 cell grid-x acym__fields__edit__back-front<?php echo in_array($data['field']->id, array(1, 2)) ? ' is-hidden' : ''; ?>">
				<div class="cell acym__content grid-x">
					<h1 class="cell acym__title__listing margin-right-1"><?php echo acym_translation('ACYM_BACKEND') ?></h1>
					<div class="cell grid-x large-6  margin-top-1">
                        <?php echo acym_switch('field[backend_profile]', $data['field']->backend_profile, acym_translation('ACYM_BACKEND').' '.strtolower(acym_translation('ACYM_PROFILE')), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
					</div>
					<div class="cell grid-x large-6  margin-top-1">
                        <?php echo acym_switch('field[backend_listing]', $data['field']->backend_listing, acym_translation('ACYM_BACKEND').' '.strtolower(acym_translation('ACYM_LISTING')), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
					</div>
				</div>
				<div class="cell acym__content grid-x margin-top-2 is-hidden">
					<h1 class="cell acym__title__listing margin-right-1"><?php echo acym_translation('ACYM_FRONTEND') ?></h1>
					<div class="cell grid-x large-6">
                        <?php echo acym_switch('field[frontend_form]', $data['field']->frontend_form, acym_translation('ACYM_FRONTEND').' '.strtolower(acym_translation('ACYM_FORM')), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
					</div>
					<div class="cell grid-x large-6">
                        <?php echo acym_switch('field[frontend_profile]', $data['field']->frontend_profile, acym_translation('ACYM_FRONTEND').' '.strtolower(acym_translation('ACYM_PROFILE')), array(), 'shrink', 'auto', 'tiny margin-0'); ?>
					</div>
				</div>
			</div>
		</div>
        <?php acym_formOptions(true) ?>
	</div>
</form>
