<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?>	<?php echo $this->tabs->startPane('mail_tab'); ?>
	<?php echo $this->tabs->startPanel(acymailing_translation('INFOS'), 'mail_infos'); ?>
	<br style="font-size:1px"/>

	<div class="onelineblockoptions">
		<table class="acymailing_smalltable" width="100%">
			<tr>
				<td class="paramlist_key">
					<label for="subject">
						<?php echo acymailing_translation('JOOMEXT_SUBJECT'); ?>
					</label>
				</td>
				<td class="paramlist_value">
					<input onClick="zoneToTag='subject';" type="text" name="data[mail][subject]" id="subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->mail->subject); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key">
					<?php echo acymailing_translation('SEND_HTML'); ?>
				</td>
				<td class="paramlist_value">
					<?php echo acymailing_boolean("data[mail][html]", 'onchange="updateAcyEditor(this.value)"', $this->mail->html); ?>
				</td>
			</tr>
			<?php
			$jflanguages = acymailing_get('type.jflanguages');
			if($jflanguages->multilingue){ ?>
				<tr>
					<td class="paramlist_key">
						<label for="jlang">
							<?php echo acymailing_translation('ACY_LANGUAGE'); ?>
						</label>
					</td>
					<td class="paramlist_value">
						<?php
						$jflanguages->sef = true;
						echo $jflanguages->displayJLanguages('data[mail][language]', empty($this->mail->language) ? '' : $this->mail->language);
						?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
	<?php if($this->mail->type == 'article'){ ?>
		<div class="onelineblockoptions">
			<span class="acyblocktitle"><?php echo acymailing_translation('ACY_INSERT_TAG_ARTICLE'); ?></span>
			<table class="acymailing_smalltable">
				<tr>
					<td>
						<?php echo acymailing_translation('DISPLAY'); ?>
					</td>
					<td colspan="2">
						<?php echo acymailing_radio($this->contenttype, 'contenttype', 'size="1" onclick="updateTag();"', 'value', 'text', 'intro'); ?>
					</td>
					<td>
						<?php $jflanguages = acymailing_get('type.jflanguages');
						$jflanguages->onclick = 'onchange="updateTag();"';
						echo $jflanguages->display('lang', ''); ?>
					</td>
				</tr>
				<tr id="format" class="acyplugformat">
					<td valign="top">
						<?php echo acymailing_translation('FORMAT'); ?>
					</td>
					<td valign="top">
						<?php echo $this->acypluginsHelper->getFormatOption('tagcontent'); ?>
					</td>
					<td valign="top"><?php echo acymailing_translation('DISPLAY_PICTURES'); ?></td>
					<td valign="top"><?php echo acymailing_radio($this->picts, 'pict', 'size="1" onclick="updateTag();"', 'value', 'text', '1'); ?>
						<span id="pictsize" style="display:none;"><br/><?php echo acymailing_translation('CAPTCHA_WIDTH') ?>
							<input name="pictwidth" type="text" onchange="updateTag();" value="150" style="width:30px;"/>
								x <?php echo acymailing_translation('CAPTCHA_HEIGHT') ?>
							<input name="pictheight" type="text" onchange="updateTag();" value="150" style="width:30px;"/>
						</span>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('CLICKABLE_TITLE'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($this->titlelink, 'titlelink', 'size="1" onclick="updateTag();"', 'value', 'text', 'link'); ?>
					</td>
					<td>
						<?php echo acymailing_translation('AUTHOR_NAME'); ?>
					</td>
					<td>
						<?php echo acymailing_radio($this->authorname, 'author', 'size="1" onclick="updateTag();"', 'value', 'text', '0'); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo acymailing_translation('SHARE'); ?>
					</td>
					<?php
					$socialMedias = array('facebook' => 'Facebook', 'linkedin' => 'LinkedIn', 'twitter' => 'Twitter', 'google' => 'Google+');

					$cpt = 1;
					foreach($socialMedias as $key => $oneSocial){
						if($cpt == 4){
							$cpt = 1;
							echo '</tr><tr><td/>';
						}
						echo '<td><input value="'.$key.'" name="socialshare" id="'.$key.'" type="checkbox" onclick="updateTag();" /> ';
						echo '<label for="'.$key.'">'.$oneSocial.'</label></td>';
						$cpt++;
					}
					while($cpt != 4){
						$cpt++;
						echo '<td/>';
					}
					?>
				</tr>
			</table>
			<a class="acymailing_button" style="width: 95%; text-align: center" onclick="insertTagCurrent(); return false;"><?php echo acymailing_translation('INSERT_TAG'); ?></a>
		</div>
	<?php } ?>
	<?php echo $this->tabs->endPanel(); ?>
	<?php echo $this->tabs->startPanel(acymailing_translation('ATTACHMENTS'), 'mail_attachments'); ?>
	<br style="font-size:1px"/>

	<div class="acyblockoptions" style="float:none;">
		<?php if(!empty($this->mail->attach)){
			echo '<div class="acyblockoptions" style="float:none;">
				<span class="acyblocktitle">'.acymailing_translation('ATTACHED_FILES').'</span>';
			foreach($this->mail->attach as $idAttach => $oneAttach){
				$idDiv = 'attach_'.$idAttach;
				echo '<div id="'.$idDiv.'">'.$oneAttach->filename.' ('.(round($oneAttach->size / 1000, 1)).' Ko)';
				echo $this->toggleClass->delete($idDiv, $this->mail->mailid.'_'.$idAttach, 'mail');
				echo '</div>';
			}

			echo '</div>';
		} ?>
		<div id="loadfile">
			<?php
			$uploadfileType = acymailing_get('type.uploadfile');
			for($i = 0; $i < 10; $i++){
				echo '<div'.($i == 0 ? '' : ' style="display:none;"').' id="attachmentsdiv'.$i.'">'.$uploadfileType->display(false, 'attachments', $i).'<a style="display:none" href="javascript:void(0);" id="attachments'.$i.'suppr" onclick="deleteAttachment('.$i.');"><span class="hasTooltip acyicon-delete" title="Delete" ></span></a></div>';
			}
			?>
		</div>
		<a href="javascript:void(0);" onclick='addFileLoader()'><?php echo acymailing_translation('ADD_ATTACHMENT'); ?></a>
		<?php echo acymailing_translation_sprintf('MAX_UPLOAD', $this->values->maxupload); ?>
	</div>
	<?php echo $this->tabs->endPanel();
	echo $this->tabs->startPanel(acymailing_translation('SENDER_INFORMATIONS'), 'mail_sender');
	$config = acymailing_config(); ?>
	<br style="font-size:1px"/>

	<div class="onelineblockoptions">
		<table width="100%" class="acymailing_smalltable">
			<tr>
				<td class="paramlist_key">
					<?php echo acymailing_translation('FROM_NAME'); ?>
				</td>
				<td class="paramlist_value">
					<input placeholder="<?php echo acymailing_translation('USE_DEFAULT_VALUE'); ?>" class="inputbox" type="text" id="fromname" name="data[mail][fromname]" style="width:200px" value="<?php echo $this->escape($this->mail->fromname); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key">
					<?php echo acymailing_translation('FROM_ADDRESS'); ?>
				</td>
				<td class="paramlist_value">
					<input onchange="validateEmail(this.value, '<?php echo addslashes(acymailing_translation('FROM_ADDRESS')); ?>')" placeholder="<?php echo acymailing_translation('USE_DEFAULT_VALUE'); ?>" class="inputbox" type="text" id="fromemail" name="data[mail][fromemail]" style="width:200px" value="<?php echo $this->escape($this->mail->fromemail); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key">
					<?php echo acymailing_translation('REPLYTO_NAME'); ?>
				</td>
				<td class="paramlist_value">
					<input placeholder="<?php echo acymailing_translation('USE_DEFAULT_VALUE'); ?>" class="inputbox" type="text" id="replyname" name="data[mail][replyname]" style="width:200px" value="<?php echo $this->escape($this->mail->replyname); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="paramlist_key">
					<?php echo acymailing_translation('REPLYTO_ADDRESS'); ?>
				</td>
				<td class="paramlist_value">
					<input onchange="validateEmail(this.value, '<?php echo addslashes(acymailing_translation('REPLYTO_ADDRESS')); ?>')" placeholder="<?php echo acymailing_translation('USE_DEFAULT_VALUE'); ?>" class="inputbox" type="text" id="replyemail" name="data[mail][replyemail]" style="width:200px" value="<?php echo $this->escape($this->mail->replyemail); ?>"/>
				</td>
			</tr>
		</table>
	</div>
	<?php echo acymailing_getFunctionsEmailCheck();

	echo $this->tabs->endPanel();
	$this->config = acymailing_config();
	if(acymailing_level(3) && acymailing_isAllowed($this->config->get('acl_newsletters_inbox_actions', 'all')) && acymailing_isPluginEnabled('acymailing', 'plginboxactions')) include(ACYMAILING_BACK.'views'.DS.'newsletter'.DS.'tmpl'.DS.'inboxactions.php');
	echo $this->tabs->endPane(); ?>
