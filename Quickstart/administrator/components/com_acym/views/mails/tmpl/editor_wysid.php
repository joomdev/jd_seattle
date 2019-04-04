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
echo "<input type='hidden' class='acym__wysid__hidden__save__content' id='editor_content' name='editor_content' value=''/>";
echo "<input type='hidden' class='acym__wysid__hidden__save__stylesheet' id='editor_stylesheet' name='editor_stylesheet' value='".$this->getWYSIDStylesheet()."'/>";
echo "<input type='hidden' class='acym__wysid__hidden__save__settings' id='editor_settings' name='editor_settings' value='".$this->getWYSIDSettings()."'/>";
?>
<div id="acym__wysid__edit" class="cell grid-x">
	<div class="cell grid-x padding-1 padding-bottom-0">
		<div class="cell medium-auto hide-for-small-only"></div>
		<button id="acym__wysid__edit__button" type="button" class="cell button xlarge-3 medium-4 margin-bottom-0"><i class="fa fa-edit" style="vertical-align: middle"></i><?php echo acym_getVar('string', 'ctrl') == 'campaigns' ? acym_translation("ACYM_EDIT_MAIL") : acym_translation("ACYM_EDIT_TEMPLATE"); ?></button>
		<div class="cell medium-auto hide-for-small-only"></div>
	</div>
	<div class="cell grid-x">
		<div class="cell medium-auto hide-for-small-only"></div>
		<div id="acym__wysid__email__preview" class="acym__email__preview grid-x cell xxlarge-6 large-9 margin-top-1"></div>
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
				<button id="acym__wysid__cancel__button" type="button" class="cell small-6 medium-shrink button-secondary button margin-bottom-0"><?php echo acym_translation("ACYM_CANCEL"); ?></button>
				<button id="acym__wysid__save__button" type="button" class="cell small-6 medium-shrink button margin-bottom-0"><?php echo acym_translation("ACYM_APPLY"); ?></button>
			</div>

            <?php if ($this->content !== "") {
                echo $this->content;
            } else { ?>
				<div id="acym__wysid__template" class="cell acym__foundation__for__email">
					<table class="body">
						<tbody>
						<tr>
							<td align="center" class="center acym__wysid__template__content" valign="top" style="background-color: rgb(120, 120, 120); padding: 40px 0 40px 0;">
								<center>
									<table align="center">
										<tbody>
										<tr>
											<td class="acym__wysid__row ui-droppable ui-sortable">

												<table class="row acym__wysid__row__element" bgcolor="#ffffff">
													<tbody style="background-color: rgb(255, 255, 255);" bgcolor="#ffffff">
													<tr>
														<th class="small-12 medium-12 large-12 columns">
															<table class="acym__wysid__column" style="min-height: 75px; display: block;">
																<tbody class="ui-sortable" style="min-height: 75px; display: block;"></tbody>
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
					<p id="acym__wysid__right__toolbar__design__tab" class="large-6 small-6 cell acym__wysid__right__toolbar__selected">
						<span><?php echo acym_translation("ACYM_DESIGN"); ?></span>
						<i class="fa fa-pencil"></i>
					</p>
					<p id="acym__wysid__right__toolbar__settings__tab" class="large-6 small-6 cell">
						<span><?php echo acym_translation("ACYM_SETTINGS"); ?></span>
						<i class="fa fa-cog"></i>
					</p>
				</div>

				<div id="acym__wysid__right__toolbar__design" class="cell grid-y acym__wysid__right__toolbar--menu">
					<p class="cell grid-margin-x grid-margin-y acym__wysid__right__toolbar__p__open"><?php echo acym_translation("ACYM_BLOCKS"); ?><i class="material-icons">expand_more</i></p>
					<div class="grid-x grid-margin-x grid-margin-y cell xxlarge-up-3 large-up-2 medium-up-1 small-up-1 acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__blocks">
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

					<p class="cell grid-margin-x grid-margin-y acym__wysid__right__toolbar__p__open"><?php echo acym_translation("ACYM_CONTENTS"); ?><i class="material-icons">expand_more</i></p>
					<div class="grid-x grid-margin-x grid-margin-y cell xxlarge-up-3 large-up-2 medium-up-1 small-up-1 acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__contents">
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--title ui-draggable ui-draggable-handle">
							<i class="cell material-icons">title</i>
							<div class="cell"><?php echo acym_translation("ACYM_TITLE"); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--text ui-draggable ui-draggable-handle">
							<i class="cell material-icons">format_align_justify</i>
							<div class="cell"><?php echo acym_translation("ACYM_TEXT"); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--button ui-draggable ui-draggable-handle">
							<i class="cell material-icons">crop_16_9</i>
							<div class="cell"><?php echo acym_translation("ACYM_BUTTON"); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--space ui-draggable ui-draggable-handle">
							<i class="cell material-icons">unfold_more</i>
							<div class="cell"><?php echo acym_translation("ACYM_SPACE"); ?></div>
						</div>

						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--cms ui-draggable ui-draggable-handle">
							<i class="cell fa fa-<?php echo ACYM_CMS == 'WordPress' ? 'wordpress' : 'joomla'; ?>"></i>
							<div class="cell"><?php echo ACYM_CMS; ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--picture ui-draggable ui-draggable-handle">
							<i class="cell material-icons">insert_photo</i>
							<div class="cell"><?php echo acym_translation("ACYM_IMAGE"); ?></div>
						</div>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--video ui-draggable ui-draggable-handle">
							<i class="cell fa fa-play-circle"></i>
							<div class="cell"><?php echo acym_translation("ACYM_VIDEO"); ?></div>
						</div>
						<!--<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--gif">-->
						<!--    <i class="cell material-icons">gif</i>-->
						<!--    <div class="cell">Giphy</div>-->
						<!--</div>-->
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--follow ui-draggable ui-draggable-handle">
							<i class="cell fa fa-facebook"></i>
							<div class="cell"><?php echo acym_translation("ACYM_FOLLOW"); ?></div>
						</div>
                        <?php
                        echo acym_tooltip('<div class="grid-x cell acym__wysid__column__element--coming-soon"><i class="cell material-icons">share</i><div class="cell">'.acym_translation("ACYM_SHARE").'</div></div>', '<span class="acy_coming_soon"><i class="material-icons acy_coming_soon_icon">new_releases</i>'.acym_translation('ACYM_COMING_SOON').'</span>', 'grid-x cell');

                        $plugins = acym_trigger('insertOptions');

                        foreach ($plugins as $onePlugin) {
                            echo '
                            <div '.(empty($onePlugin->title) ? '' : 'title="'.$onePlugin->title.'"').' data-plugin="'.$onePlugin->plugin.'" class="grid-x cell acym__wysid__column__element--new ui-draggable ui-draggable-handle">
                                <img class="cell acym-plugin-icon" src="'.$onePlugin->icon.'" alt="cb icon"/>
                                <div class="cell">'.$onePlugin->name.'</div>
                            </div>';
                        }
                        ?>
						<div class="grid-x cell acym__wysid__column__element--new acym__wysid__column__element--new--separator ui-draggable ui-draggable-handle">
							<i class="cell material-icons">more_horiz</i>
							<div class="cell"><?php echo acym_translation("ACYM_SEPARATOR"); ?></div>
						</div>
					</div>

					<!--Todo custom zones created by users-->
					<!--<p class="cell grid-margin-x grid-margin-y acym__wysid__right__toolbar__last--text">My elements<i class="material-icons">expand_more</i></p>-->
					<!--<div class="cell grid-x grid-margin-x grid-margin-y grid-padding-y large-up-2 medium-up-1 acym__wysid__right__toolbar__design--show" style="display: none;">-->
					<!--</div>-->
				</div>

				<div id="acym__wysid__right__toolbar__settings" style="display: none;" class="cell grid-padding-x acym__wysid__right__toolbar--menu">
					<p class="acym__wysid__right__toolbar__p__open"><?php echo acym_translation("ACYM_TEMPLATE_DESIGN"); ?><i class="material-icons">expand_more</i></p>
					<div class="grid-y acym__wysid__right__toolbar__design--show">
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label for="acym__wysid__background-colorpicker" class="cell small-10"><?php echo acym_translation("ACYM_BACKGROUND_COLOR"); ?>
							</label>
							<div class="small-2 text-center cell" style="margin:auto">
								<input type="text" id="acym__wysid__background-colorpicker" class="cell medium-shrink small-4"/>
							</div>
						</div>
					</div>
					<p class="acym__wysid__right__toolbar__p__open"><?php echo acym_translation("ACYM_DESIGN"); ?><i class="material-icons">expand_more</i></p>
					<div class="grid-y acym__wysid__right__toolbar__design--show acym__wysid__right__toolbar__design">
						<div class="grid-x margin-bottom-1 small-12 cell">
							<hr class="small-2 large-4 cell" style="border-width: 2px; margin: 18px 0; border-color: #222">
							<div class="cell large-4">
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
							<hr class="small-2 large-4 cell" style="border-width: 2px; margin: 18px 0; border-color: #222">
						</div>
						<div class="grid-x margin-bottom-1 small-12 cell">
							<label class="middle large-4 cell" for="acym__wysid__right__toolbar__settings__font-family"><?php echo acym_translation("ACYM_FAMILY"); ?></label>
							<div class="cell large-8">
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
							<label class="middle large-4 cell" for="acym__wysid__right__toolbar__settings__font-size"><?php echo acym_translation("ACYM_SIZE"); ?></label>
							<div class="cell large-8">
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
							<label class="middle large-4 cell"><?php echo acym_translation("ACYM_STYLE"); ?></label>
							<i id="acym__wysid__right__toolbar__settings__bold" class="material-icons text-center small-3 large-auto cell" style="line-height: 39px">format_bold</i>
							<i id="acym__wysid__right__toolbar__settings__italic" class="material-icons text-center small-3 large-auto cell" style="line-height: 39px">format_italic</i>
							<div class="small-2 text-center cell" style="margin:auto"><input type="text" id="acym__wysid__right__toolbar__settings__color" style="display: none;">
							</div>
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
                            echo acym_modal(acym_translation('ACYM_CUSTOM_ADD_STYLESHEET'), $dataStyleSheet, 'acym__wysid__right__toolbar__settings__stylesheet__modal', '', 'class="button cell medium-6 margin-top-2" id="acym__wysid__right__toolbar__settings__stylesheet__open"') ?>
						</div>
					</div>
				</div>

			</div>
		</div>

		<!--Context modal-->
		<div id="acym__wysid__context__button" class="grid-x padding-1" style="display: none">
			<div class="grid-x cell">
				<i id="acym__wysid__context__button__italic" class="material-icons small-1 cell">format_italic</i>
				<i id="acym__wysid__context__button__bold" class="material-icons small-1 cell">format_bold</i>
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
				<div class="auto cell"></div>
				<select id="acym__wysid__context__button__font-size" class="small-3 cell">
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
				<input type="text" id="acym__wysid__context__button__background-color" class="small-3 cell">
			</div>
			<div class="grid-x cell">
				<div class="small-2 cell">
					<label class="text-left middle"><?php echo acym_translation("ACYM_BORDER"); ?></label>
				</div>
				<div class="auto cell">
					<select id="acym__wysid__context__button__border-width">
						<option>0</option>
						<option>1</option>
						<option>2</option>
						<option>3</option>
						<option>4</option>
						<option>5</option>
					</select>
				</div>
				<div class="auto cell">
					<label class="text-center middle"><?php echo acym_translation("ACYM_RADIUS"); ?></label>
				</div>
				<div class="small-3 cell">
					<select id="acym__wysid__context__button__border-radius">
						<option>0</option>
						<option>5</option>
						<option>10</option>
						<option>15</option>
						<option>20</option>
						<option>25</option>
					</select>
				</div>
				<input type="text" id="acym__wysid__context__button__border-color" class="small-3 cell">
			</div>
			<div class="grid-x cell">
				<div class="small-2 cell">
					<label class="text-left middle"><?php echo acym_translation("ACYM_TEXT"); ?></label>
				</div>
				<input id="acym__wysid__context__button__text" class="auto cell" type="text" placeholder="<?php echo acym_translation("ACYM_MY_BUTTON"); ?>">
				<input type="text" id="acym__wysid__context__button__color" class="small-2 cell">
			</div>
			<div class="grid-x cell">
				<div class="input-group small-12 cell">
					<span class="input-group-label"><img draggable="false" class="emoji" alt="🔗" src="<?php echo ACYM_MEDIA_URL ?>/images/link.svg"></span>
					<input id="acym__wysid__context__button__link" class="input-group-field" type="text" placeholder="http://my-website.com">
				</div>
			</div>
		</div>
		<div id="acym__wysid__context__space" class="grid-x padding-1">
			<div class="grid-x cell">
				<div class="small-8 padding-right-1 cell" id="acym__wysid__context__space__slider">
					<div class="slider" data-slider="" data-initial-start="50" data-start="10" data-e="2mf38c-e">
						<span class="slider-handle" data-slider-handle="" role="slider" tabindex="0" aria-controls="sliderOutput1" aria-valuemax="100" aria-valuemin="10" aria-valuenow="50" aria-orientation="horizontal" style="left: 44%;"></span>
						<span class="slider-fill" data-slider-fill="" style="width: 44%;"></span>
					</div>
				</div>
				<div class="auto cell" id="acym__wysid__context__space__input">
					<input type="number" id="sliderOutput1" max="100" min="10" step="1">
				</div>
			</div>
		</div>
		<div id="acym__wysid__context__follow" class="grid-x" style="display: none">
			<div class="grid-x small-12 cell">
				<div class="small-2 cell">
					<select name="acym__wysid__context__follow__select" id="acym__wysid__context__follow__select">
					</select>
				</div>
				<div class="auto cell">
				</div>
				<div class="small-2 cell">
					<label class="text-right middle"><?php echo acym_translation("ACYM_WIDTH"); ?>&nbsp;</label>
				</div>
				<div class="small-2 cell">
					<input id="acym__wysid__context__social__width" class="auto cell" value="40" type="number" min="30" max="80">
				</div>
			</div>
			<div id="acym__wysid__context__follow__list" class="grid-x small-12 cell">
			</div>
		</div>
		<div id="acym__wysid__context__share" class="grid-x" style="display: none">
			<div class="grid-x small-12 cell">
				<div class="small-2 cell">
					<select name="acym__wysid__context__share__select" id="acym__wysid__context__share__select">
					</select>
				</div>
				<div class="auto cell">
				</div>
				<div class="small-2 cell">
					<label class="text-right middle"><?php echo acym_translation("ACYM_WIDTH"); ?>&nbsp;</label>
				</div>
				<div class="small-2 cell">
					<input id="acym__wysid__context__social__share__width" class="auto cell" value="20" type="number" min="15" max="40">
				</div>
			</div>
			<div id="acym__wysid__context__share__list" class="grid-x small-12 cell">
			</div>
		</div>
		<div id="acym__wysid__context__separator" class="grid-x" style="display: none">
			<div class="grid-x cell">
				<div class="cell margin-left-1 margin-top-1 small-11 grid-x grid-margin-x">
					<h6 class="cell"><?php echo acym_translation('ACYM_BORDER_TYPE') ?></h6>
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
				<label class="cell margin-left-1 margin-top-1 small-11 grid-x grid-margin-x">
					<h6 class="cell shrink"><?php echo acym_translation('ACYM_COLOR') ?></h6>
					<input type="text" id="acym__wysid__context__separator__color">
				</label>
				<div class="cell margin-left-1 margin-top-1 small-11 grid-x grid-margin-x">
					<h6 class="cell shrink acym_vcenter"><?php echo acym_translation('ACYM_HEIGHT') ?></h6>
					<div class="small-6 padding-right-1 cell" id="acym__wysid__context__separator__slide">
						<div class="slider" data-slider data-initial-start="3" data-start="1" data-e="2mf38c-e">
							<span class="slider-handle" data-slider-handle role="slider" tabindex="0" aria-controls="sliderOutput2" aria-valuemax="20" aria-valuemin="1" aria-valuenow="3" aria-orientation="horizontal" style="left: 44%;"></span>
							<span class="slider-fill" data-slider-fill style="width: 44%;"></span>
						</div>
					</div>
					<div class="auto cell" id="acym__wysid__context__separator__input__height">
						<input type="number" id="sliderOutput2" max="20" min="1" step="1" value="3">
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

        <?php if (ACYM_CMS != 'WordPress') { ?>
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
		<i class="material-icons acym__wysid__modal__dynamic-text--close" id="acym__wysid__modal__dynamic-text__close__icon">close</i>
		<iframe id="acym__wysid__modal__dynamic-text__ui__iframe" src="<?php echo acym_completeLink('dynamics&task=popup', true); ?>" frameborder="0"></iframe>
	</div>
</div>
