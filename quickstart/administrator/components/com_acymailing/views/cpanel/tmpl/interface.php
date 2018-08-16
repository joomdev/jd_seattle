<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="config_interface">
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('MESSAGES'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_SUBSCRIPTION_DESC').'<br /><br /><i>'.($this->config->get('require_confirmation', 0) ? acymailing_translation('CONFIRMATION_SENT') : acymailing_translation('SUBSCRIPTION_OK')).'</i>', acymailing_translation('DISPLAY_MSG_SUBSCRIPTION'), '', acymailing_translation('DISPLAY_MSG_SUBSCRIPTION')); ?>
				</td>
				<td>
					<?php echo $this->elements->subscription_message; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_CONFIRM_DESC').'<br /><br /><i>'.acymailing_translation('SUBSCRIPTION_CONFIRMED').'</i>', acymailing_translation('DISPLAY_MSG_CONFIRM'), '', acymailing_translation('DISPLAY_MSG_CONFIRM')); ?>
				</td>
				<td>
					<?php echo $this->elements->confirmation_message; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_UNSUBSCRIPTION_DESC'), acymailing_translation('DISPLAY_MSG_UNSUBSCRIPTION'), '', acymailing_translation('DISPLAY_MSG_UNSUBSCRIPTION')); ?>
				</td>
				<td>
					<?php echo $this->elements->unsubscription_message; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_CONFIRMATION_DESC'), acymailing_translation('DISPLAY_MSG_CONFIRMATION'), '', acymailing_translation('DISPLAY_MSG_CONFIRMATION')); ?>
				</td>
				<td>
					<?php echo $this->elements->confirm_message; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_WELCOME_DESC'), acymailing_translation('DISPLAY_MSG_WELCOME'), '', acymailing_translation('DISPLAY_MSG_WELCOME')); ?>
				</td>
				<td>
					<?php echo $this->elements->welcome_message; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('DISPLAY_MSG_UNSUB_DESC'), acymailing_translation('DISPLAY_MSG_UNSUB'), '', acymailing_translation('DISPLAY_MSG_UNSUB')); ?>
				</td>
				<td>
					<?php echo $this->elements->unsub_message; ?>
				</td>
			</tr>
		</table>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle">CSS</span>
		<table class="acymailing_table" cellspacing="1">
			<?php if(!empty($this->elements->css_module)){ ?>
			<tr>
				<td class="acykey">
					<?php
					if('joomla' == 'wordpress'){
						echo acymailing_translation('ACY_CSS_WIDGET');
					}else{
						echo acymailing_tooltip(acymailing_translation('CSS_MODULE_DESC'), acymailing_translation('CSS_MODULE'), '', acymailing_translation('CSS_MODULE'));
					}
					?>
				</td>
				<td>
					<?php echo $this->elements->css_module; ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('CSS_FRONTEND_DESC'), acymailing_translation('CSS_FRONTEND'), '', acymailing_translation('CSS_FRONTEND')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_frontend; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ACY_CSS_BACKEND_DESC'), acymailing_translation('ACY_CSS_BACKEND'), '', acymailing_translation('ACY_CSS_BACKEND')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_backend; ?>
				</td>
			</tr>
			<?php if(ACYMAILING_J30 && !empty($this->elements->bootstrap_frontend)){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_translation('USE_BOOTSTRAP_FRONTEND'); ?>
					</td>
					<td>
						<?php echo $this->elements->bootstrap_frontend; ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
	<?php if(!empty($this->elements->use_sef)){ ?>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('FEATURES'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('FORWARD_DESC'), acymailing_translation('FORWARD_FEATURE'), '', acymailing_translation('FORWARD_FEATURE')); ?>
				</td>
				<td>
					<?php echo $this->elements->forward; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('USE_SEF_DESC'), acymailing_translation('USE_SEF'), '', acymailing_translation('USE_SEF')); ?>
				</td>
				<td>
					<?php echo $this->elements->use_sef; ?>
				</td>
			</tr>
			<?php
			if(acymailing_level(3)){
				?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('ACY_FEATURE_SEND_IN_ARTICLE_DESC'), acymailing_translation('ACY_FEATURE_SEND_IN_ARTICLE'), '', acymailing_translation('ACY_FEATURE_SEND_IN_ARTICLE')); ?>
					</td>
					<td class="acykey">
						<?php echo $this->elements->edit_send_in_article ?>
					</td>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
	<?php } ?>
	<?php if(!empty($this->elements->acymailing_menu)) { ?>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('MENU'); ?></span>
			<table class="acymailing_table" cellspacing="1">
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('ACYMAILING_MENU_DESC'), acymailing_translation('ACYMAILING_MENU'), '', acymailing_translation('ACYMAILING_MENU')); ?>
					</td>
					<td>
						<?php echo $this->elements->acymailing_menu; ?>
					</td>
				</tr>
			</table>
		</div>
	<?php
		}
		if(!empty($this->elements->editor)){
	?>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ACY_EDITOR'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('EDITOR_DESC'), acymailing_translation('ACY_EDITOR'), '', acymailing_translation('ACY_EDITOR')); ?>
				</td>
				<td>
					<?php echo $this->elements->editor; ?>
				</td>
			</tr>
		</table>
	</div>
	<?php
		}
		if(!empty($this->elements->indexFollow)){
	?>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ARCHIVE_SECTION'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<?php
			if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_jcomments'.DS.'jcomments.php')){
				$jcomments = ($this->config->get('comments_feature') == 'jcomments') ? 'checked="checked"' : '';
			}else{
				$jcomments = 'disabled="disabled"';
			}
			if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_rscomments')){
				$rscomments = ($this->config->get('comments_feature') == 'rscomments') ? 'checked="checked"' : '';
			}else{
				$rscomments = 'disabled="disabled"';
			}
			if(file_exists(ACYMAILING_ROOT.'components'.DS.'com_komento')){
				$komento = ($this->config->get('comments_feature') == 'komento') ? 'checked="checked"' : '';
			}else{
				$komento = 'disabled="disabled"';
			}
			if(file_exists(ACYMAILING_ROOT.'plugins'.DS.'content'.DS.'jom_comment_bot.php')){
				$jomcomment = ($this->config->get('comments_feature') == 'jomcomment') ? 'checked="checked"' : '';
			}else{
				$jomcomment = 'disabled="disabled"';
			}
			if($this->config->get('comments_feature') == 'disqus'){
				$disqus = 'checked="checked"';
			}else{
				$disqus = '';
			}
			$no_checked = $this->config->get('comments_feature') ? '' : 'checked="checked"';

			?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('COMMENTS_ENABLED_DESC'), acymailing_translation('COMMENTS_ENABLED'), '', acymailing_translation('COMMENTS_ENABLED')); ?>
				</td>
				<td>
					<div class="controls">
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature" value="" <?php echo $no_checked; ?> size="1" type="radio"/>
						<label for="config_comments_feature"><?php echo acymailing_translation('JOOMEXT_NO'); ?></label>
						<?php if('joomla' == 'joomla') { ?>
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature_rscomments" value="rscomments" <?php echo $rscomments; ?> size="1" type="radio"/>
						<label for="config_comments_feature_rscomments">RSComments</label>
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature_komento" value="komento" <?php echo $komento; ?> size="1" type="radio"/>
						<label for="config_comments_feature_komento">Komento</label>
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature_jcomments" value="jcomments" <?php echo $jcomments; ?> size="1" type="radio"/>
						<label for="config_comments_feature_jcomments">jComments</label>
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature_jomcomment" value="jomcomment" <?php echo $jomcomment; ?> size="1" type="radio"/>
						<label for="config_comments_feature_jomcomment">jomComment</label>
						<?php } ?>
						<input onclick="updateCommentsOption();" name="config[comments_feature]" id="config_comments_feature_disqus" value="disqus" <?php echo $disqus; ?> size="1" type="radio"/>
						<label for="config_comments_feature_disqus">Disqus</label>
					</div>
					<label for="config_disqus_shortname" style="display:<?php echo empty($disqus) ? "none" : "inline-block"; ?>;" id="config_disqus_shortname_label">Shortname : </label>
					<input type="text" name="config[disqus_shortname]" id="config_disqus_shortname" value="<?php echo $this->config->get('disqus_shortname'); ?>" size="1" style="width:100px;float:none;<?php if(empty($disqus)) echo "display:none;"; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('SUBJECT_DISPLAY_DESC'), acymailing_translation('SUBJECT_DISPLAY'), '', acymailing_translation('SUBJECT_DISPLAY')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[frontend_subject]", '', $this->config->get('frontend_subject', 1)); ?>
				</td>
			</tr>
			<?php if(!ACYMAILING_J16){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_tooltip(acymailing_translation('FRONTEND_PDF_DESC'), acymailing_translation('FRONTEND_PDF'), '', acymailing_translation('FRONTEND_PDF')); ?>
					</td>
					<td>
						<?php echo acymailing_boolean("config[frontend_pdf]", '', $this->config->get('frontend_pdf', 0)); ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('FRONTEND_PRINT_DESC'), acymailing_translation('FRONTEND_PRINT'), '', acymailing_translation('FRONTEND_PRINT')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[frontend_print]", '', $this->config->get('frontend_print', 0)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('SHOW_DESCRIPTION_DESC'), acymailing_translation('SHOW_DESCRIPTION'), '', acymailing_translation('SHOW_DESCRIPTION')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[show_description]", '', $this->config->get('show_description', 1)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('SHOW_FILTER_DESC'), acymailing_translation('SHOW_FILTER'), '', acymailing_translation('SHOW_FILTER')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[show_filter]", '', $this->config->get('show_filter', 1)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_ORDER'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[show_order]", '', $this->config->get('show_order', 1)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('SHOW_SENDDATE_DESC'), acymailing_translation('SHOW_SENDDATE'), '', acymailing_translation('SHOW_SENDDATE')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[show_senddate]", '', $this->config->get('show_senddate', 1)); ?>
				</td>
			</tr>
			<?php if(acymailing_level(1)){ ?>
				<tr>
					<td class="acykey">
						<?php echo acymailing_translation_sprintf('SHOW_COLUMN_X', '<b><i>'.acymailing_translation('RECEIVE_VIA_EMAIL').'</i></b>'); ?>
					</td>
					<td>
						<?php echo acymailing_boolean("config[show_receiveemail]", '', $this->config->get('show_receiveemail', 0)); ?>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="acykey" valign="top">
					<?php echo acymailing_tooltip(acymailing_translation('OPEN_POPUP_DESC'), acymailing_translation('OPEN_POPUP'), '', acymailing_translation('OPEN_POPUP')); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[open_popup]", '', $this->config->get('open_popup', 1)); ?>
					<div style="margin-top:10px;">
						<?php echo acymailing_translation('CAPTCHA_WIDTH'); ?> <input type="text" name="config[popup_width]" style="float:none;width:40px" value="<?php echo intval($this->config->get('popup_width', 750)); ?>"/> x <?php echo acymailing_translation('CAPTCHA_HEIGHT'); ?> <input type="text" name="config[popup_height]" style="float:none;width:40px"
																																																																		value="<?php echo intval($this->config->get('popup_height', 550)); ?>"/>
					</div>
				</td>
			</tr>
			<tr id="indexfollow">
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ARCHIVE_INDEX_FOLLOW_DESC'), acymailing_translation('ARCHIVE_INDEX_FOLLOW'), '', acymailing_translation('ARCHIVE_INDEX_FOLLOW')); ?>
				</td>
				<td>
					<?php echo $this->elements->indexFollow; ?>
				</td>
			</tr>
		</table>
	</div>
	<?php } ?>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('UNSUB_PAGE'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(str_replace('UNSUB_INTRO', acymailing_translation('UNSUB_INTRO'), $this->config->get('unsub_intro', 'UNSUB_INTRO')), acymailing_translation('UNSUB_INTRODUCTION'), '', acymailing_translation('UNSUB_INTRODUCTION')); ?>
				</td>
				<td>
					<textarea style="width:300px;" rows="5" name="config[unsub_intro]"><?php echo $this->config->get('unsub_intro', 'UNSUB_INTRO'); ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('UNSUB_DISP_CHOICE'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[unsub_dispoptions]", '', $this->config->get('unsub_dispoptions', 1)); ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_UNSUB_DISP_OTHER_SUBS'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[unsub_dispothersubs]", '', $this->config->get('unsub_dispothersubs', 0)); ?>
				</td>
			</tr>
			<tr>
				<td valign="top" class="acykey">
					<?php echo acymailing_translation('UNSUB_DISP_SURVEY'); ?>
				</td>
				<td>
					<?php echo acymailing_boolean("config[unsub_survey]", 'onclick="displaySurvey(this.value)"', $this->config->get('unsub_survey', 1));
					$reasons = unserialize($this->config->get('unsub_reasons'));
					?>
					<div id="unsub_reasons_area" class="acymailing_deploy" <?php if(!$this->config->get('unsub_survey', 1)) echo 'style="display:none"'; ?> >
						<div id="unsub_reasons">
							<?php
							foreach($reasons as $i => $oneReason){
								if(preg_match('#^[A-Z_]*$#', $oneReason)){
									$trans = acymailing_translation($oneReason);
								}else{
									$trans = $oneReason;
								}
								echo '<span style="font-size:8px">'.$trans.'</span><br /><input type="text" style="width:300px;margin-bottom: 3px;" value="'.$this->escape($oneReason).'" name="unsub_reasons[]" /><br />';
							} ?>
						</div>
						<a onclick="addUnsubReason();return false;" href='#' title="<?php echo $this->escape(acymailing_translation('FIELD_ADDVALUE')); ?>">
							<button class="acymailing_button_grey" onclick="return false">
								<?php echo acymailing_translation('FIELD_ADDVALUE'); ?>
							</button>
						</a>
					</div>
				</td>
			</tr>
		</table>
	</div>
	<?php if(!empty($this->elements->acyrss_format)){ ?>
	<div class="onelineblockoptions">
		<span class="acyblocktitle">RSS</span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_TYPE'); ?>
				</td>
				<td>
					<?php echo $this->elements->acyrss_format; ?>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_NAME'); ?>
				</td>
				<td>
					<input type="text" style="width:200px" name="config[acyrss_name]" value="<?php echo $this->escape($this->config->get('acyrss_name', '')); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_DESCRIPTION'); ?>
				</td>
				<td>
					<textarea style="width:300px;" rows="5" name="config[acyrss_description]"><?php echo $this->config->get('acyrss_description', ''); ?></textarea>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('MAX_ARTICLE'); ?>
				</td>
				<td>
					<input type="text" style="width:50px" name="config[acyrss_element]" value="<?php echo intval($this->config->get('acyrss_element', 20)); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_translation('ACY_ORDER'); ?>
				</td>
				<td>
					<?php echo $this->elements->acyrss_order; ?>
				</td>
			</tr>
		</table>
	</div>
	<?php }
	if(acymailing_level(3) && 'joomla' == 'joomla') include(dirname(__FILE__).DS.'interface_enterprise.php'); ?>
	<script language="javascript" type="text/javascript">
		<!--
		function updateCommentsOption(){
			if(document.getElementById("config_comments_feature_disqus").checked){
				document.getElementById('config_disqus_shortname_label').style.display = 'inline-block';
				document.getElementById('config_disqus_shortname').style.display = '';
			}else{
				document.getElementById('config_disqus_shortname_label').style.display = 'none';
				document.getElementById('config_disqus_shortname').style.display = 'none';
			}
		}
		//-->
	</script>
</div>
