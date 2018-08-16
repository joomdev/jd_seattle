<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="acyarchivelisting">
	<?php if($this->values->show_page_heading){ ?>
	<h1 class="contentheading<?php echo $this->values->suffix; ?>"><?php echo $this->values->page_heading; ?></h1>
	<?php } ?>
	<form action="<?php echo acymailing_completeLink('archive&listid='.$this->list->listid); ?>" method="post" name="adminForm" id="adminForm" >
		<table style="width:100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $this->values->suffix; ?>">
		<?php if($this->values->show_description){ ?>
			<tr>
				<td class="contentdescription<?php echo $this->values->suffix; ?>" >
					<?php echo $this->list->description; ?>
				</td>
			</tr>
		<?php } ?>
			<tr>
				<td>
				<?php
					if(!empty($this->manageableLists)){
				?>
					<p class="acynewbutton"><a class="btn" href="<?php echo acymailing_completeLink('frontnewsletter&task=add&listid='.$this->list->listid); ?>" title="<?php echo acymailing_translation('CREATE_NEWSLETTER',true); ?>" ><img src="<?php echo ACYMAILING_IMAGES; ?>icons/icon-16-add.png" alt="<?php echo acymailing_translation('CREATE_NEWSLETTER',true); ?>" /> <?php echo acymailing_translation('CREATE_NEWSLETTER'); ?></a></p>
				<?php } ?>
					<?php echo $this->loadTemplate('newsletters'); ?>
					<?php if(!empty($this->values->itemid)){ ?>
						<input type="hidden" name="Itemid" value=<?php echo $this->values->itemid; ?> />
					<?php } ?>
					<input type="hidden" name="nbreceiveemail" value="0" />
				</td>
			</tr>
		</table>
	
		<?php if($this->values->show_receiveemail){ ?>
			<div id="receiveemailbox" class="receiveemailbox receiveemailbox_hidden">
				<fieldset class="acymailing_receiveemail">
				<legend><?php echo acymailing_translation('SEND_SELECT_NEWS'); ?></legend>
					<table>
						<tr>
							<td>
								<label for="forwardname"><?php echo acymailing_translation('JOOMEXT_NAME'); ?></label>
							</td>
							<td>
								<input id="forwardname" type="text" class="inputbox required" name="name" value="" style="width:100px"/>
							</td>
						</tr>
						<tr>
							<td>
								<label for="forwardemail"><?php echo acymailing_translation('JOOMEXT_EMAIL'); ?></label>
							</td>
							<td>
								<input id="forwardemail" type="text" class="inputbox required" name="email" value="" style="width:100px"/>
							</td>
						</tr>
						<tr>
							<?php
								$captchaClass = acymailing_get('class.acycaptcha');
								$captchaClass->display();
							?>
						</tr>
					</table>
					<button class="btn btn-primary" type="submit"/><?php echo acymailing_translation('SEND'); ?></button>
					<?php acymailing_formOptions($this->pageInfo->filter->order, 'sendarchive'); ?>
				</fieldset>
			</div>
	
		<?php }
			if(!empty($this->manageableLists)){
		?>
			<p class="acynewbutton"><a class="btn" href="<?php echo acymailing_completeLink('frontnewsletter&task=add&listid='.$this->list->listid); ?>" title="<?php echo acymailing_translation('CREATE_NEWSLETTER',true); ?>" ><img src="<?php echo ACYMAILING_IMAGES; ?>icons/icon-16-add.png" alt="<?php echo acymailing_translation('CREATE_NEWSLETTER',true); ?>" /> <?php echo acymailing_translation('CREATE_NEWSLETTER'); ?></a></p>
		<?php } ?>
	</form>
</div>
