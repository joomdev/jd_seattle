<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><input type="hidden" class="acym__wysid__hidden__save__content" id="editor_content" name="editor_content" value="" />
<input type="hidden" class="acym__wysid__hidden__save__stylesheet" id="editor_stylesheet" name="editor_stylesheet" value="<?php echo acym_escape($this->getWYSIDStylesheet()); ?>" />
<input type="hidden" class="acym__wysid__hidden__save__settings" id="editor_settings" name="editor_settings" value="<?php echo acym_escape($this->getWYSIDSettings()); ?>" />
<input type="hidden" id="acym__wysid__session--lifetime" name="acym_session_lifetime" value="<?php echo acym_escape(ini_get("session.gc_maxlifetime")); ?>" />
<input type="hidden" class="acym__wysid__hidden__mailId" id="editor_mailid" name="editor_autoSave" value="<?php echo intval($this->mailId); ?>" />
<input type="hidden" class="acym__wysid__hidden__save__auto" id="editor_autoSave" value="<?php echo acym_escape($this->autoSave); ?>">

<div id="acym__wysid__edit" class="cell grid-x">
	<div class="cell grid-x padding-1 padding-bottom-0">
		<div class="cell medium-auto hide-for-small-only"></div>
		<button id="acym__wysid__edit__button" type="button" class="cell button xlarge-3 medium-4 margin-bottom-0"><i class="fa fa-edit" style="vertical-align: middle"></i><?php echo acym_translation(acym_getVar('string', 'ctrl') == 'campaigns' ? 'ACYM_EDIT_MAIL' : ($this->walkThrough ? 'ACYM_EDIT' : 'ACYM_EDIT_TEMPLATE')); ?></button>
		<div class="cell medium-auto hide-for-small-only"></div>
	</div>
	<div class="cell grid-x" id="acym__wysid__edit__preview">
		<div class="cell medium-auto hide-for-small-only"></div>
		<div id="acym__wysid__email__preview" class="acym__email__preview grid-x cell <?php echo $this->walkThrough ? '' : 'xxlarge-6'; ?> large-9 margin-top-1"></div>
		<div class="cell medium-auto hide-for-small-only"></div>
	</div>
</div>

<div class="grid-x grid-margin-x">
	<div id="acym__wysid" class="grid-x margin-0 grid-margin-x acym__content cell" style="display: none;">
		<!--Template & top toolbar-->
		<div id="acym__wysid__wrap" class="grid-y large-8 small-9 cell grid-padding-x grid-padding-y">
			<!--Top toolbar-->
			<div id="acym__wysid__top-toolbar" class="grid-x cell">
				<div class="cell auto small-up-3 hide-for-small-only text-left">
					<i id="acym__wysid__view__desktop" class="cell fa fa-desktop text-center"></i>
					<i id="acym__wysid__view__smartphone" class="cell fa fa-mobile-phone text-center"></i>
				</div>
				<div class="cell auto hide-for-small-only"></div>
				<div class="cell small-3 text-center acym__autosave__status">
					<div class="acym__wysid__autosave__status__fail">
						<i class="fa fa-exclamation-circle"></i>
						<div><?php echo acym_translation('ACYM_AUTOSAVE_FAIL'); ?></div>
					</div>
					<div class="acym__wysid__autosave__status__success">
						<i class="fa fa-check-circle"></i>
						<div><?php echo acym_translation('ACYM_AUTOSAVE_SUCCESS'); ?></div>
					</div>
				</div>
				<button id="acym__wysid__cancel__button" type="button" class="cell small-6 medium-shrink button-secondary button margin-bottom-0"><?php echo acym_translation('ACYM_CANCEL'); ?></button>
				<button id="acym__wysid__save__button" type="button" class="cell small-6 medium-shrink button margin-bottom-0"><?php echo acym_translation('ACYM_APPLY'); ?></button>
			</div>

            <?php if (strpos($this->content, 'acym__wysid__template') !== false) {
                echo $this->content;
            } else { ?>
				<div id="acym__wysid__template" class="cell acym__foundation__for__email">
					<table class="body">
						<tbody>
							<tr>
								<td align="center" class="center acym__wysid__template__content" valign="top" style="background-color: rgb(120, 120, 120); padding: 40px 0 120px 0;">
									<center>
										<table align="center">
											<tbody>
												<tr>
													<td class="acym__wysid__row ui-droppable ui-sortable" bgcolor="#ffffff" style="background-color: rgb(255, 255, 255);">

														<table class="row acym__wysid__row__element">
															<tbody>
																<tr>
																	<th class="small-12 medium-12 large-12 columns">
																		<table class="acym__wysid__column" style="min-height: 75px; display: block;">
																			<tbody class="ui-sortable" style="min-height: 75px; display: block;">
                                                                                <?php
                                                                                if (!empty($this->content)) {
                                                                                    echo '<tr class="acym__wysid__column__element ui-draggable" style="position: relative; top: inherit; left: inherit; right: inherit; bottom: inherit; height: auto;">
																			<td class="large-12 acym__wysid__column__element__td" style="outline: rgb(0, 163, 254) dashed 0px; outline-offset: -1px;">
																				<div class="acym__wysid__tinymce--text mce-content-body" id="mce_0" contenteditable="true" style="position: relative;" spellcheck="false">
																					'.acym_absoluteURL($this->content).'
																				</div>
																			</td>
																		</tr>';
                                                                                }
                                                                                ?>
																			</tbody>
																		</table>
																	</th>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</center>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
            <?php } ?>
		</div>

		<!--Right toolbar-->
		<div id="acym__wysid__right-toolbar" class="grid-y large-4 small-3 cell">
			<div class="acym__wysid__right-toolbar__content grid-y grid-padding-x small-12 cell" style="max-height: 829px;">

				<div class="cell grid-x text-center">
					<p data-attr-show="acym__wysid__right__toolbar__design" id="acym__wysid__right__toolbar__design__tab" class="large-4 small-4 cell acym__wysid__right__toolbar__selected acym__wysid__right__toolbar__tabs">
						<i class="fa fa-th"></i>
					</p>
					<p data-attr-show="acym__wysid__right__toolbar__current-block" id="acym__wysid__right__toolbar__block__tab" class="large-4 small-4 cell acym__wysid__right__toolbar__tabs">
						<i class="fa fa-edit"></i>
					</p>
					<p data-attr-show="acym__wysid__right__toolbar__settings" id="acym__wysid__right__toolbar__settings__tab" class="large-4 small-4 cell acym__wysid__right__toolbar__tabs">
						<i class="fa fa-cog"></i>
					</p>
				</div>

				<div id="acym__wysid__right__toolbar__design" class="cell grid-y acym__wysid__right__toolbar--menu">
					<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_BLOCKS'); ?><i class="acymicon-expand_more"></i></p>
					<div class="acym__wysid__context__modal__container grid-x grid-margin-x grid-margin-y cell xxlarge-up-3 large-up-2 medium-up-1 small-up-1 acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__blocks">
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--1 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                    <rect class="acym__wysid__row__element__type" width="80.8" height="100"></rect>
                                </svg>
						</div>
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--2 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                <rect class="acym__wysid__row__element__type" width="36.8" height="100"></rect>
								<rect x="44" class="acym__wysid__row__element__type" width="36.8" height="100"></rect>
                                </svg>
						</div>
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--3 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                <rect y="0.5" class="acym__wysid__row__element__type" width="24.2" height="100"></rect>
								<rect x="28.5" y="0.5" class="acym__wysid__row__element__type" width="24.2" height="100"></rect>
								<rect x="57" y="0.5" class="acym__wysid__row__element__type" width="24.2" height="100"></rect>
                                </svg>
						</div>
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--4 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                <rect x="65.6" class="acym__wysid__row__element__type" width="15.8" height="100"></rect>
								<rect x="44.1" class="acym__wysid__row__element__type" width="15.8" height="100"></rect>
								<rect x="22.1" class="acym__wysid__row__element__type" width="15.8" height="100"></rect>
								<rect class="acym__wysid__row__element__type" width="15.8" height="100"></rect>
                                </svg>
						</div>
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--5 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                <rect x="55.6" class="acym__wysid__row__element__type" width="25.2" height="100"></rect>
								<rect class="acym__wysid__row__element__type" width="50.2" height="100"></rect>
                                </svg>
						</div>
						<div class="cell acym__wysid__row__element--new acym__wysid__row__element--new--6 ui-draggable ui-draggable-handle">
							<svg version="1.1" x="0px" y="0px" viewBox="0 0 80.8 81" style="enable-background:new 0 0 80.8 81;" xml:space="preserve">
                                    <rect class="acym__wysid__row__element__type" width="25.2" height="100"></rect>
								<rect x="31" class="acym__wysid__row__element__type" width="50.2" height="100"></rect>
                                </svg>
						</div>
					</div>

					<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_CONTENTS'); ?><i class="acymicon-expand_more"></i></p>
					<div class="grid-x grid-margin-x grid-margin-y cell xxlarge-up-3 large-up-2 medium-up-1 small-up-1 acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__contents acym__wysid__context__modal__container">
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--title ui-draggable ui-draggable-handle">
							<i class="cell acymicon-title"></i>
							<div class="cell"><?php echo acym_translation('ACYM_TITLE'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--text ui-draggable ui-draggable-handle">
							<i class="cell acymicon-format_align_justify"></i>
							<div class="cell"><?php echo acym_translation('ACYM_TEXT'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--button ui-draggable ui-draggable-handle">
							<i class="cell acymicon-crop_16_9"></i>
							<div class="cell"><?php echo acym_translation('ACYM_BUTTON'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--space ui-draggable ui-draggable-handle">
							<i class="cell acymicon-unfold_more"></i>
							<div class="cell"><?php echo acym_translation('ACYM_SPACE'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--picture ui-draggable ui-draggable-handle">
							<i class="cell acymicon-insert_photo"></i>
							<div class="cell"><?php echo acym_translation('ACYM_IMAGE'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--video ui-draggable ui-draggable-handle">
							<i class="cell fa fa-play-circle"></i>
							<div class="cell"><?php echo acym_translation('ACYM_VIDEO'); ?></div>
						</div>
                        <?php
                        $plugins = acym_trigger('insertOptions');

                        foreach ($plugins as $onePlugin) {
                            $title = empty($onePlugin->title) ? '' : 'title="'.$onePlugin->title.'"';

                            echo '<div '.$title.' data-plugin="'.$onePlugin->plugin.'" class="grid-x cell acym__wysid__column__element--new ui-draggable ui-draggable-handle">';

                            if (empty($onePlugin->icontype) || $onePlugin->icontype == 'img') {
                                echo '<img class="cell acym-plugin-icon" src="'.$onePlugin->icon.'" alt="cb icon"/>';
                            } elseif ($onePlugin->icontype == 'raw') {
                                echo $onePlugin->icon;
                            }

                            echo '<div class="cell">'.$onePlugin->name.'</div>';
                            echo '</div>';
                        }
                        ?>
						<!--<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--gif">-->
						<!--    <i class="cell acymicon-gif"></i>-->
						<!--    <div class="cell">Giphy</div>-->
						<!--</div>-->
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--follow ui-draggable ui-draggable-handle">
							<i class="cell fa fa-facebook"></i>
							<div class="cell"><?php echo acym_translation('ACYM_FOLLOW'); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--separator ui-draggable ui-draggable-handle">
							<i class="cell acymicon-more_horiz"></i>
							<div class="cell"><?php echo acym_translation('ACYM_SEPARATOR'); ?></div>
						</div>
                        <?php
                        echo acym_tooltip(
                            '<div class="grid-x cell acym__wysid__column__element--coming-soon"><i class="cell acymicon-share"></i><div class="cell">'.acym_translation('ACYM_SHARE').'</div></div>',
                            '<span class="acy_coming_soon"><i class="acymicon-new_releases acy_coming_soon_icon"></i>'.acym_translation('ACYM_COMING_SOON').'</span>',
                            'grid-x cell'
                        );
                        ?>
					</div>

					<!--Todo custom zones created by users-->
					<!--<p class="cell acym__wysid__right__toolbar__last--text">My elements<i class="acymicon-expand_more"></i></p>-->
					<!--<div class="cell grid-x grid-margin-x grid-margin-y grid-padding-y large-up-2 medium-up-1 acym__wysid__right__toolbar__design--show" style="display: none;">-->
					<!--</div>-->
				</div>

				<div id="acym__wysid__right__toolbar__settings" style="display: none;" class="cell grid-padding-x acym__wysid__right__toolbar--menu">
					<p class="acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_TEMPLATE_DESIGN'); ?><i class="acymicon-expand_more"></i></p>
					<div class="grid-y acym__wysid__right__toolbar__design--show acym__wysid__context__modal__container">
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label for="acym__wysid__background-colorpicker" class="cell large-6 small-9"><?php echo acym_translation('ACYM_BACKGROUND_COLOR'); ?></label>
							<i class="acymicon-insert_photo small-1 acym_vcenter text-center cell acym__color__light-blue cursor-pointer" id="acym__wysid__background-image__template"></i>
							<i class="acymicon-close acym_vcenter acym__color__red" id="acym__wysid__background-image__template-delete"></i>
							<div class="small-2 text-center cell" style="margin:auto 0;">
								<input type="text" id="acym__wysid__background-colorpicker" class="cell medium-shrink small-4" />
							</div>
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="cell grid-x">
								<span class="cell large-6 small-8"><?php echo acym_translation('ACYM_MARGIN_TOP_CONTENT'); ?></span>
								<input type="number" min="0" value="20" id="acym__wysid__padding__top__content" class="cell small-4">
							</label>
						</div>
					</div>
					<p class="acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_DESIGN'); ?><i class="acymicon-expand_more"></i></p>
					<div class="grid-y acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__design acym__wysid__context__modal__container">
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="middle large-6 cell" for="acym__wysid__right__toolbar__settings__font--select"><?php echo acym_translation('ACYM_HTML_TAG'); ?></label>
							<div class="cell large-6">
								<select id="acym__wysid__right__toolbar__settings__font--select" class="small-8 large-4 cell">
									<option>p</option>
									<option>h1</option>
									<option>h2</option>
									<option>h3</option>
									<option>h4</option>
									<option>h5</option>
									<option>h6</option>
								</select>
							</div>
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="middle large-6 cell" for="acym__wysid__right__toolbar__settings__font-family"><?php echo acym_translation('ACYM_FAMILY'); ?></label>
							<div class="cell large-6">
								<select id="acym__wysid__right__toolbar__settings__font-family" class="auto cell">
									<option style="font-family: 'Andale Mono'">Andale Mono</option>
									<option style="font-family: 'Arial'">Arial</option>
									<option style="font-family: 'Book Antiqua'">Book Antiqua</option>
									<option style="font-family: 'Comic Sans MS'">Comic Sans MS</option>
									<option style="font-family: 'Courier New'">Courier New</option>
									<option style="font-family: 'Georgia'">Georgia</option>
									<option style="font-family: 'Helvetica'">Helvetica</option>
									<option style="font-family: 'Impact'">Impact</option>
									<option style="font-family: 'Times New Roman'">Times New Roman</option>
									<option style="font-family: 'Trebuchet MS'">Trebuchet MS</option>
									<option style="font-family: 'Verdana'">Verdana</option>
								</select>
							</div>
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="middle large-6 cell" for="acym__wysid__right__toolbar__settings__font-size"><?php echo acym_translation('ACYM_SIZE'); ?></label>
							<div class="cell large-6">
								<select id="acym__wysid__right__toolbar__settings__font-size" class="auto cell">
									<option>10px</option>
									<option>12px</option>
									<option>14px</option>
									<option>16px</option>
									<option>18px</option>
									<option>20px</option>
									<option>24px</option>
									<option>28px</option>
									<option>30px</option>
									<option>34px</option>
									<option>36px</option>
								</select>
							</div>
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="middle large-6 cell"><?php echo acym_translation('ACYM_STYLE'); ?></label>
							<i id="acym__wysid__right__toolbar__settings__bold" class="acymicon-format_bold text-center small-3 large-auto cell" style="line-height: 39px"></i>
							<i id="acym__wysid__right__toolbar__settings__italic" class="acymicon-format_italic text-center small-3 large-auto cell" style="line-height: 39px"></i>
							<div class="small-2 text-center cell" style="margin:auto"><input type="text" id="acym__wysid__right__toolbar__settings__color" style="display: none;"></div>
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<div class="cell hide-for-small-only medium-3"></div>
                            <?php
                            $dataStyleSheet = '<div class="grid-x acym__wysid__right__toolbar__settings__stylesheet">
                                                    <h6 class="acym__wysid__right__toolbar__settings__stylesheet__title cell text-center margin-top-1">'.acym_translation('ACYM_HERE_PASTE_YOUR_STYLESHEET').'</h6>
                                                    <textarea id="acym__wysid__right__toolbar__settings__stylesheet__textarea" class="margin-top-1" rows="15"></textarea>
                                                    <button type="button" id="acym__wysid__right__toolbar__settings__stylesheet__cancel" class="button cell medium-4">'.acym_translation('ACYM_CANCEL').'</button>
                                                    <div class="medium-4 cell"></div>
                                                    <button type="button" id="acym__wysid__right__toolbar__settings__stylesheet__apply" class="button cell medium-4">'.acym_translation('ACYM_LOAD_STYLESHEET').'</button>
                                               </div>';
                            echo acym_modal(acym_translation('ACYM_CUSTOM_ADD_STYLESHEET'), $dataStyleSheet, 'acym__wysid__right__toolbar__settings__stylesheet__modal', '', 'class="button cell medium-6 margin-top-2" id="acym__wysid__right__toolbar__settings__stylesheet__open"'); ?>
						</div>
					</div>
					<p class="acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_CUSTOM_SOCIAL_ICONS'); ?><i class="acymicon-expand_more"></i></p>
					<div class="grid-y acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__design acym__wysid__right__toolbar__design__social__icons acym__wysid__context__modal__container">
                        <?php
                        $config = acym_config();
                        $socialIcons = json_decode($config->get('social_icons', '{}'), true);
                        foreach ($socialIcons as $social => $iconUrl) {
                            echo '<div class="cell grid-x margin-bottom-2 acym_vcenter acym__wysid__right__toolbar__design__social__icons__one">
                        				<img class="cell shrink" src="'.acym_escape($iconUrl).'" alt="icon '.acym_escape($social).'">
                        				<input type="file" name="icon_'.acym_escape($social).'" class="auto cell" accept="image/png, image/jpeg">
                        				<div class="auto cell grid-x text-center align-center acym_vcenter"><span class="shrink cell acym__wysid__social__icons__import__text">'.acym_translation('ACYM_SELECT_NEW_ICON').'</span></div>
                        				<button disabled type="button" class="button cell shrink acym__wysid__social__icons__import">'.acym_translation('ACYM_IMPORT').'</button>
                        			 </div>';
                        }
                        ?>
					</div>
				</div>

				<div id="acym__wysid__right__toolbar__current-block" style="display: none;" class="grid-padding-x cell acym__wysid__right__toolbar--menu">
					<p class="acym__wysid__right__toolbar__current-block__empty cell text-center margin-top-1">No block selected</p>
					<div id="acym__wysid__context__block" class="grid-x cell padding-1 acym__wysid__context__modal" style="display: none">
						<p class="cell acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_BACKGROUND'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x acym_vcenter">
								<label class="cell small-6"><?php echo acym_translation('ACYM_BACKGROUND_COLOR'); ?></label>
								<input type="text" id="acym__wysid__context__block__background-color">
							</div>
							<div class="cell grid-x acym_vcenter">
								<label class="cell small-6"><?php echo acym_translation('ACYM_BACKGROUND_IMAGE'); ?></label>
								<i class="acymicon-insert_photo acym__color__light-blue cursor-pointer" id="acym__wysid__context__block__background-image"></i>
								<i class="acymicon-close acym__color__red cursor-pointer" style="display: none" id="acym__wysid__context__block__background-image__remove"></i>
							</div>
							<div class="cell grid-x acym_vcenter">
                                <?php echo acym_switch('transparent_background', 0, acym_translation('ACYM_TRANSPARENT_BACKGROUND'), [], 'small-6 acym__wysid__context__block__transparent__bg');
                                ?>
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_PADDING'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x align-center">
								<input type="number" max="20" min="0" class="cell shrink acym__wysid__context__block__padding" data-block-padding="top">
							</div>
							<div class="cell grid-x align-center acym_vcenter margin-bottom-1">
								<input type="number" max="20" min="0" class="cell shrink acym__wysid__context__block__padding" data-block-padding="left">
								<div class="small-4 cell acym__wysid__context__block__padding__exemple"></div>
								<input type="number" max="20" min="0" class="cell shrink acym__wysid__context__block__padding" data-block-padding="right">
							</div>
							<div class="cell grid-x align-center">
								<input type="number" max="20" min="0" class="cell shrink acym__wysid__context__block__padding" data-block-padding="bottom">
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_BORDER'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x">
								<label class="cell small-5"><?php echo acym_translation('ACYM_RADIUS'); ?></label>
								<input type="number" max="20" min="0" class="cell shrink acym__wysid__context__block__border__actions" data-css="border-radius">
							</div>
							<div class="cell grid-x">
								<label class="cell small-5"><?php echo acym_translation('ACYM_WIDTH'); ?></label>
								<input type="number" max="10" min="0" class="cell shrink acym__wysid__context__block__border__actions" data-css="border-width"">
							</div>
							<div class="cell grid-x acym_vcenter">
								<label class="cell small-5"><?php echo acym_translation('ACYM_COLOR'); ?></label>
								<input type="number" max="20" min="0" class="cell small-2" id="acym__wysid__context__block__border__color">
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_ADVANCED_OPTIONS'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<label class="cell small-5"><?php echo acym_translation('ACYM_HTML_ID').acym_info(acym_translation('ACYM_HTML_ID_DESC')); ?></label>
							<input type="text" class="acym__light__input cell small-6" id="acym__wysid__context__block__custom_id" placeholder="<?php echo acym_escape(acym_translation('ACYM_HTML_ID')); ?>">
						</div>
					</div>
					<div id="acym__wysid__context__button" class="grid-x padding-1 acym__wysid__context__modal" style="display: none">
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_FONT'); ?><i class="acymicon-expand_more"></i></p>
						<div class="grid-x cell acym__wysid__context__modal__container">
							<div class="cell grid-x">
								<label class="cell small-5"><?php echo acym_translation('ACYM_FORMATTING'); ?></label>
								<i id="acym__wysid__context__button__italic" class="acymicon-format_italic small-1 cell acym__wysid__context__button__actions-i"></i>
								<i id="acym__wysid__context__button__bold" class="acymicon-format_bold small-1 cell acym__wysid__context__button__actions-i"></i>
							</div>
							<div class="cell grid-x">
								<label for="acym__wysid__context__button__font" class="cell small-5"><?php echo acym_translation('ACYM_FONT_FAMILY'); ?></label>
								<select id="acym__wysid__context__button__font-family" class="auto cell">
									<option style="font-family: 'Andale Mono'">Andale Mono</option>
									<option style="font-family: 'Arial'">Arial</option>
									<option style="font-family: 'Book Antiqua'">Book Antiqua</option>
									<option style="font-family: 'Comic Sans MS'">Comic Sans MS</option>
									<option style="font-family: 'Courier New'">Courier New</option>
									<option style="font-family: 'Georgia'">Georgia</option>
									<option style="font-family: 'Helvetica'">Helvetica</option>
									<option style="font-family: 'Impact'">Impact</option>
									<option style="font-family: 'Times New Roman'">Times New Roman</option>
									<option style="font-family: 'Trebuchet MS'">Trebuchet MS</option>
									<option style="font-family: 'Verdana'">Verdana</option>
								</select>
							</div>
							<div class="cell grid-x">
								<label for="acym__wysid__context__button__font" class="cell small-5"><?php echo acym_translation('ACYM_SIZE'); ?></label>
								<select id="acym__wysid__context__button__font-size" class="small-5 cell">
									<option>10</option>
									<option>12</option>
									<option>14</option>
									<option>16</option>
									<option>18</option>
									<option>20</option>
									<option>24</option>
									<option>28</option>
									<option>30</option>
									<option>34</option>
									<option>36</option>
								</select>
							</div>
							<div class="cell grid-x">
								<label for="acym__wysid__context__button__background" class="cell small-5"><?php echo acym_translation('ACYM_COLOR'); ?></label>
								<input type="text" id="acym__wysid__context__button__color" class="small-2 cell">
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_BORDER'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="grid-x cell">
								<label class="cell small-5"><?php echo acym_translation('ACYM_WIDTH'); ?></label>
								<select id="acym__wysid__context__button__border-width" class="small-5 cell">
									<option>0</option>
									<option>1</option>
									<option>2</option>
									<option>3</option>
									<option>4</option>
									<option>5</option>
								</select>
							</div>
							<div class="grid-x cell">
								<label class="small-5 cell"><?php echo acym_translation('ACYM_RADIUS'); ?></label>
								<select id="acym__wysid__context__button__border-radius" class="small-5 cell">
									<option>0</option>
									<option>5</option>
									<option>10</option>
									<option>15</option>
									<option>20</option>
									<option>25</option>
								</select>
							</div>
							<div class="cell grid-x">
								<label class="cell small-5"><?php echo acym_translation('ACYM_COLOR'); ?></label>
								<input type="text" id="acym__wysid__context__button__border-color" class="small-5 cell">
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_CONTENT'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="grid-x cell">
								<label class="cell small-5"><?php echo acym_translation('ACYM_TEXT'); ?></label>
								<input id="acym__wysid__context__button__text" class="auto cell" type="text" placeholder="<?php echo acym_translation('ACYM_MY_BUTTON'); ?>">
							</div>
							<div class="grid-x cell">
								<div class="input-group cell grid-x">
									<label class="cell small-5" for="acym__wysid__context__button__link"><?php echo acym_translation('ACYM_LINK'); ?></label>
									<input id="acym__wysid__context__button__link" class="input-group-field cell auto" type="text" placeholder="https://my-website.com">
								</div>
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_OTHER'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x">
								<label for="acym__wysid__context__button__background" class="cell small-5"><?php echo acym_translation('ACYM_BACKGROUND_COLOR'); ?></label>
								<input type="text" id="acym__wysid__context__button__background-color" class="small-5 cell">
							</div>
							<div class="grid-x cell">
								<div class="cell grid-x">
									<label class="cell small-5"><?php echo acym_translation('ACYM_ALIGNMENT'); ?></label>
									<i class="acymicon-format_align_left cell shrink acym__wysid__context__button__align" id="acym__wysid__context__button__align__left" data-align="left"></i>
									<i class="acymicon-format_align_center cell shrink acym__wysid__context__button__align" id="acym__wysid__context__button__align__center" data-align="center"></i>
									<i class="acymicon-format_align_right cell shrink acym__wysid__context__button__align" id="acym__wysid__context__button__align__right" data-align="right"></i>
								</div>
							</div>
							<div class="cell grid-x margin-top-1 acym_vcenter margin-bottom-1">
                                <?php
                                echo acym_switch('full_width', 0, acym_tooltip(acym_translation('ACYM_FULL_WIDTH'), acym_translation('ACYM_FULL_WIDTH_DESC')), [], 'small-5');
                                ?>
								<div class="cell grid-x acym__button__padding">
									<div class="cell grid-x">
										<label class="cell small-3"><?php echo acym_translation('ACYM_PADDING') ?></label>
										<div class="cell grid-x auto">
											<div class="small-8 padding-right-1 cell acym__wysid__context__button__slider" data-output="slider__output__button__width">
												<div class="slider" data-slider="" data-end="100" data-initial-start="25">
													<span class="slider-handle" data-slider-handle="" role="slider" tabindex="0" aria-controls="slider__output__button__width" aria-valuemax="50" data-valuenow="25" aria-valuemin="10" style="left: 44%"></span>
													<span class="slider-fill" data-slider-fill="" style="width: 44%;"></span>
												</div>
											</div>
											<div class="small-2 cell" id="acym__wysid__context__space__input">
												<input type="number" id="slider__output__button__width" max="50" min="10" step="1">
											</div>
										</div>
										<div class="cell grid-x acym_vcenter">
											<div class="cell small-3 grid-x acym__wysid__context__button__slider" data-output="slider__output__button__height">
												<div class="slider vertical" data-slider data-initial-start="25" data-end="100" data-vertical="true">
													<span class="slider-handle" data-slider-handle role="slider" tabindex="0" aria-controls="slider__output__button__height"></span>
													<span class="slider-fill" data-slider-fill></span>
												</div>
											</div>
											<div class="cell small-7 acym__button__padding__shape align-center acym_vcenter"><?php echo acym_translation('ACYM_BUTTON') ?></div>
											<div class="cell grid-x">
												<input type="number" class="cell shrink" id="slider__output__button__height" max="50" min="10" step="1">
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="acym__wysid__context__space" style="display: none" class="grid-x padding-1 acym__wysid__context__modal">
						<label class="cell small-5"><?php echo acym_translation('ACYM_HEIGHT') ?></label>
						<div class="grid-x cell auto">
							<div class="small-8 padding-right-1 cell" id="acym__wysid__context__space__slider">
								<div class="slider" data-slider="" data-initial-start="50" data-start="10" data-e="2mf38c-e">
									<span class="slider-handle" data-slider-handle="" role="slider" tabindex="0" aria-controls="sliderOutput1" aria-valuemax="100" aria-valuemin="10" aria-valuenow="50" aria-orientation="horizontal" style="left: 44%;"></span>
									<span class="slider-fill" data-slider-fill="" style="width: 44%;"></span>
								</div>
							</div>
							<div class="small-2 cell" id="acym__wysid__context__space__input">
								<input type="number" id="sliderOutput1" max="100" min="10" step="1">
							</div>
						</div>
					</div>
					<div id="acym__wysid__context__separator" class="grid-x padding-1 acym__wysid__context__modal" style="display: none">
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_STYLE'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x grid-margin-x">
								<div class="acym__wysid__context__separator__kind cell small-3 separator-selected">
									<hr data-kind="solid" style="border-bottom: 3px solid black">
								</div>
								<div class="acym__wysid__context__separator__kind cell small-3">
									<hr data-kind="dotted" style="border-bottom: 3px dotted black">
								</div>
								<div class="acym__wysid__context__separator__kind cell small-3">
									<hr data-kind="dashed" style="border-bottom: 3px dashed black">
								</div>
								<div class="acym__wysid__context__separator__kind cell small-3">
									<hr data-kind="double" style="border-bottom: 3px double black">
								</div>
							</div>
							<label class="cell small-11 grid-x grid-margin-x margin-top-1">
								<label class="cell small-3 acym__color__light-blue"><?php echo acym_translation('ACYM_COLOR'); ?></label>
								<input type="text" id="acym__wysid__context__separator__color">
							</label>
						</div>
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_SIZE'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x grid-margin-x">
								<label class="cell small-3 acym_vcenter acym__color__light-blue"><?php echo acym_translation('ACYM_HEIGHT'); ?></label>
								<div class="small-6 padding-right-1 cell" id="acym__wysid__context__separator__slide">
									<div class="slider" data-slider data-initial-start="3" data-start="1" data-e="2mf38c-e">
										<span class="slider-handle" data-slider-handle role="slider" tabindex="0" aria-controls="sliderOutput2" aria-valuemax="20" aria-valuemin="1" aria-valuenow="3" aria-orientation="horizontal" style="left: 44%;"></span>
										<span class="slider-fill" data-slider-fill style="width: 44%;"></span>
									</div>
								</div>
								<div class="shrink cell" id="acym__wysid__context__separator__input__height">
									<input type="number" id="sliderOutput2" max="20" min="1" step="1" value="3">
								</div>
							</div>
							<div class="cell grid-x grid-margin-x">
								<label class="cell small-3 acym_vcenter acym__color__light-blue"><?php echo acym_translation('ACYM_WIDTH'); ?></label>
								<div class="small-6 padding-right-1 cell" id="acym__wysid__context__separator__slide__width">
									<div class="slider" data-slider data-initial-start="100">
										<span class="slider-handle" data-slider-handle role="slider" tabindex="1" aria-controls="sliderOutput3" style="left: 100%;"></span>
										<span class="slider-fill" data-slider-fill style="width: 100%;"></span>
									</div>
								</div>
								<div class="shrink cell" id="acym__wysid__context__separator__input__width">
									<input type="number" id="sliderOutput3" max="100" min="0" step="1" value="100">
								</div>
							</div>
						</div>
					</div>
					<div id="acym__wysid__context__follow" class="grid-x padding-1 acym__wysid__context__modal" style="display: none">
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_LINKS'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="grid-x cell">
								<label class="cell small-3"><?php echo acym_translation('ACYM_ADD_NEW'); ?></label>
								<div class="small-2 cell">
									<select name="acym__wysid__context__follow__select" id="acym__wysid__context__follow__select">
									</select>
								</div>
							</div>
							<div id="acym__wysid__context__follow__list" class="grid-x small-12 cell">
							</div>
						</div>
						<p class="cell acym__wysid__right__toolbar__p__open acym__wysid__right__toolbar__p"><?php echo acym_translation('ACYM_OTHER'); ?><i class="acymicon-expand_more"></i></p>
						<div class="cell grid-x acym__wysid__context__modal__container">
							<div class="cell grid-x">
								<label class="small-3 cell"><?php echo acym_translation('ACYM_WIDTH'); ?></label>
								<input id="acym__wysid__context__social__width" class="small-2 cell" value="40" type="number" min="30" max="80">
							</div>
							<div class="grid-x cell margin-top-1">
								<div class="cell grid-x grid-margin-x">
									<label class="cell small-3"><?php echo acym_translation('ACYM_ALIGNMENT'); ?></label>
									<div class="cell auto grid-x">
										<i class="acymicon-format_align_left cell shrink acym__wysid__context__follow__align" id="acym__wysid__context__follow__align__left" data-align="left"></i>
										<i class="acymicon-format_align_center cell shrink acym__wysid__context__follow__align" id="acym__wysid__context__follow__align__center" data-align="center"></i>
										<i class="acymicon-format_align_right cell shrink acym__wysid__context__follow__align" id="acym__wysid__context__follow__align__right" data-align="right"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!--Modal-->
		<div id="acym__wysid__modal" class="acym__wysid__modal">
			<div class="acym__wysid__modal__bg acym__wysid__modal--close"></div>
			<div class="acym__wysid__modal__ui float-center cell">
				<div id="acym__wysid__modal__ui__fields"></div>
				<div id="acym__wysid__modal__ui__display"></div>
				<div id="acym__wysid__modal__ui__search"></div>
				<button class="close-button acym__wysid__modal--close" aria-label="Dismiss alert" type="button" data-close="">
					<span aria-hidden="true">×</span>
				</button>
			</div>
		</div>
		<div id="acym__wysid__modal_plugins" class="acym__wysid__modal">
			<div class="acym__wysid__modal__bg acym__wysid__modal--close"></div>
			<div class="acym__wysid__modal__ui float-center cell">
				<div id="acym__plugins__modal__insert">
					<div id="acym__dynamics__popup__menu__insert__tag" class="cell grid-x">
						<div class="medium-auto hide-for-small-only"></div>
						<input title="Dynamic content" type="text" class="cell medium-5 small-12 margin-right-1" id="dcontentcode" name="dcontentcode" value="">
						<div class="medium-2 small-12">
							<button class="button expanded smaller-button" id="insertDContent"><?php echo acym_translation('ACYM_INSERT'); ?></button>
						</div>
						<div class="medium-auto hide-for-small-only"></div>
					</div>
				</div>
				<div id="acym__plugins__modal__options"></div>
				<button class="close-button acym__wysid__modal--close" aria-label="Dismiss alert" type="button" data-close="">
					<span aria-hidden="true">×</span>
				</button>
			</div>
		</div>

		<div id="acym__wysid__fullscreen__modal" class="grid-y">
			<div id="acym__wysid__fullscreen__modal__content" class="grid-x cell small-12"></div>
			<button id="acym__wysid__fullscreen__modal__close" class="close-button padding-1" aria-label="Dismiss alert" type="button" data-close="">
				<span aria-hidden="true" style="font-size: 39px">×</span>
			</button>
		</div>

        <?php if ('joomla' === ACYM_CMS) { ?>
			<div id="acym__wysid__modal__joomla-image">
				<div id="acym__wysid__modal__joomla-image__bg" class="acym__wysid__modal__joomla-image--close"></div>
				<div id="acym__wysid__modal__joomla-image__ui" class="float-center cell">
					<iframe id="acym__wysid__modal__joomla-image__ui__iframe" src="index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;e_name=imageurl&amp;asset=com_content&amp;author=" frameborder="0"></iframe>
				</div>
			</div>
        <?php } ?>
	</div>
</div>
<div id="acym__wysid__modal__dynamic-text">
	<div id="acym__wysid__modal__dynamic-text__bg" class="acym__wysid__modal__dynamic-text--close"></div>
	<div id="acym__wysid__modal__dynamic-text__ui" class="float-center cell">
		<i class="acymicon-close acym__wysid__modal__dynamic-text--close" id="acym__wysid__modal__dynamic-text__close__icon"></i>
		<iframe id="acym__wysid__modal__dynamic-text__ui__iframe" src="<?php echo acym_completeLink('dynamics&task=popup&automation='.$this->automation, true); ?>" frameborder="0"></iframe>
	</div>
</div>

