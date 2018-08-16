<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><table<?php if(!acymailing_isAdmin()){
	echo ' class="acymailing_table" style="margin: 10px 0px;"';
} ?> width="100%">
	<tr>
		<td class="acykey" id="subjectkey" valign="top">
			<label for="subject">
				<?php echo acymailing_translation('JOOMEXT_SUBJECT'); ?>
			</label>
		</td>
		<td id="subjectinput">
			<div>
				<input type="text" name="data[mail][subject]" id="subject" style="width:80%;" class="inputbox" value="<?php echo $this->escape(@$this->mail->subject); ?>" onClick="zoneToTag='subject';"/>
			</div>
		</td>
		<td class="acykey" id="publishedkey" valign="top">
			<label for="published">
				<?php echo acymailing_translation('ACY_PUBLISHED'); ?>
			</label>
		</td>
		<td id="publishedinput" valign="top">
			<?php echo ($this->mail->published == 2) ? acymailing_translation('SCHED_NEWS') : acymailing_boolean("data[mail][published]", '', $this->mail->published, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="acykey" id="aliaskey">
			<label for="alias">
				<?php echo acymailing_translation('JOOMEXT_ALIAS'); ?>
			</label>
		</td>
		<td id="aliasinput">
			<input class="inputbox" type="text" name="data[mail][alias]" id="alias" style="width:80%;" value="<?php echo @$this->mail->alias; ?>" <?php echo($this->type == 'joomlanotification' ? 'readonly' : ''); ?>/>
		</td>
		<?php if ($this->type != 'joomlanotification'){ ?>
		<td class="acykey" id="visiblekey">
			<label for="visible">
				<?php echo acymailing_translation('JOOMEXT_VISIBLE'); ?>
			</label>
		</td>
		<td id="visibleinput">
			<?php echo acymailing_boolean("data[mail][visible]", '', $this->mail->visible, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<tr>
		<td class="acykey" id="createdkey" valign="top">
			<label for="createdinput">
				<?php echo acymailing_translation('CREATED_DATE'); ?>
			</label>
		</td>
		<td id="createdinput" valign="top">
			<?php echo acymailing_getDate(@$this->mail->created); ?>
		</td>
		<?php } ?>
		<td class="acykey" id="sendhtmlkey">
			<label for="data_mail_htmlfieldset">
				<?php echo acymailing_translation('SEND_HTML'); ?>
			</label>
		</td>
		<td id="sendhtmlinput">
			<?php echo acymailing_boolean("data[mail][html]", 'onclick="updateAcyEditor(this.value); initTagZone(this.value);"', $this->mail->html, acymailing_translation('JOOMEXT_YES'), acymailing_translation('JOOMEXT_NO')); ?>
		</td>
	</tr>
	<?php if($this->type != 'joomlanotification'){ ?>
		<tr class="hidewp">
			<td class="acykey" id="picturekey" valign="top">
				<label for="pictureinput">
					<?php echo acymailing_translation('ACY_THUMBNAIL'); ?>
				</label>
			</td>
			<td id="pictureinput" valign="top">
				<?php
				$uploadfileType = acymailing_get('type.uploadfile');
				echo $uploadfileType->display(true, 'thumb', $this->mail->thumb, 'data[mail][thumb]');
				?>
			</td>
			<td class="acykey" id="summarykey" valign="top">
				<label for="summaryfield">
					<?php echo acymailing_translation('ACY_SUMMARY'); ?>
				</label>
			</td>
			<td id="summaryinput" valign="top">
				<textarea placeholder="<?php echo acymailing_translation('ACY_SUMMARY_PLACEHOLDER') ?>" style="width:80%;height:60px;" id="summaryfield" name="data[mail][summary]"><?php echo $this->escape(@$this->mail->summary); ?></textarea>
			</td>
		</tr>
		<?php
		?>
		<?php if(!empty($this->mail->senddate)){ ?>
			<tr>
				<td class="acykey" id="senddatekey">
					<label for="senddateinput">
						<?php echo acymailing_translation('SEND_DATE'); ?>
					</label>
				</td>
				<td id="senddateinput">
					<?php echo acymailing_getDate(@$this->mail->senddate); ?>
				</td>
				<td class="acykey" id="sentbykey">
					<label for="sentbyinput">
						<?php if(!empty($this->mail->sentby)) echo acymailing_translation('SENT_BY'); ?>
					</label>
				</td>
				<td id="sentbyinput">
					<?php echo @$this->sentbyname; ?>
				</td>
			</tr>
		<?php }
	}
	$jflanguages = acymailing_get('type.jflanguages');
	if($jflanguages->multilingue){
		?>
		<tr>
			<td class="acykey" id="languagekey">
				<label for="jlang">
					<?php echo acymailing_translation('ACY_LANGUAGE'); ?>
				</label>
			</td>
			<td id="languageinput" colspan="3">
				<?php
				$jflanguages->sef = true;
				echo $jflanguages->displayJLanguages('data[mail][language]', empty($this->mail->language) ? '' : $this->mail->language);
				?>
			</td>
		</tr>
	<?php } ?>
</table>
