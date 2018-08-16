<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div id="page-mail">
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('SENDER_INFORMATIONS'); ?></span>
		<table class="acymailing_table" cellspacing="1">
			<tr>
				<td width="185" class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('FROM_NAME_DESC'), acymailing_translation('FROM_NAME'), '', acymailing_translation('FROM_NAME')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[from_name]" style="width:200px" value="<?php echo $this->escape($this->config->get('from_name')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('FROM_ADDRESS_DESC'), acymailing_translation('FROM_ADDRESS'), '', acymailing_translation('FROM_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" onchange="if(this.value.indexOf('@') == -1){ alert('Wrong email address supplied for the <?php echo addslashes(acymailing_translation('FROM_ADDRESS')); ?> field: '+this.value); return false; }" id="fromemail" name="config[from_email]" style="width:200px" value="<?php echo $this->escape($this->config->get('from_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REPLYTO_NAME_DESC'), acymailing_translation('REPLYTO_NAME'), '', acymailing_translation('REPLYTO_NAME')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[reply_name]" style="width:200px" value="<?php echo $this->escape($this->config->get('reply_name')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('REPLYTO_ADDRESS_DESC'), acymailing_translation('REPLYTO_ADDRESS'), '', acymailing_translation('REPLYTO_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" onchange="if(this.value.indexOf('@') == -1){ alert('Wrong email address supplied for the <?php echo addslashes(acymailing_translation('REPLYTO_ADDRESS')); ?> field: '+this.value); return false; }" id="replyemail" name="config[reply_email]" style="width:200px" value="<?php echo $this->escape($this->config->get('reply_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('BOUNCE_ADDRESS_DESC'), acymailing_translation('BOUNCE_ADDRESS'), '', acymailing_translation('BOUNCE_ADDRESS')); ?>
				</td>
				<td>
					<input class="inputbox" type="text" onchange="if(this.value.indexOf('@') == -1){ alert('Wrong email address supplied for the <?php echo addslashes(acymailing_translation('BOUNCE_ADDRESS')); ?> field: '+this.value); return false; }" id="bounceemail" name="config[bounce_email]" style="width:200px" value="<?php echo $this->escape($this->config->get('bounce_email')); ?>">
				</td>
			</tr>
			<tr>
				<td class="acykey">
					<?php echo acymailing_tooltip(acymailing_translation('ADD_NAMES_DESC'), acymailing_translation('ADD_NAMES'), '', acymailing_translation('ADD_NAMES')); ?>
				</td>
				<td>
					<?php echo $this->elements->add_names; ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('MAIL_CONFIG'); ?></span>

		<div id="mailer_method">
			<?php $mailerMethod = $this->config->get('mailer_method', 'phpmail');
			if(!in_array($mailerMethod, array('elasticemail', 'smtp', 'qmail', 'sendmail', 'phpmail'))) $mailerMethod = 'phpmail';
			?>
			<?php
			if(!ACYMAILING_J30 || ACYMAILING_J40 || 'joomla' == 'wordpress'){
				?>
				<div class="acyblockoptions" style="float: left;">
					<span class="acyblocktitle" style="font-size:13px;"><?php echo acymailing_translation('SEND_SERVER'); ?></span>
					<span><input type="radio" name="config[mailer_method]" onclick="updateMailer('phpmail')" value="phpmail" <?php if($mailerMethod == 'phpmail') echo 'checked="checked"'; ?> id="mailer_phpmail"/><label for="mailer_phpmail"> PHP Mail Function</label></span>
					<span><input type="radio" name="config[mailer_method]" onclick="updateMailer('sendmail')" value="sendmail" <?php if($mailerMethod == 'sendmail') echo 'checked="checked"'; ?> id="mailer_sendmail"/><label for="mailer_sendmail"> SendMail</label></span>
					<span><input type="radio" name="config[mailer_method]" onclick="updateMailer('qmail')" value="qmail" <?php if($mailerMethod == 'qmail') echo 'checked="checked"'; ?> id="mailer_qmail"/><label for="mailer_qmail"> QMail</label></span>
				</div>
				<div class="acyblockoptions" style="float: left; margin-left: 20px;">
					<span class="acyblocktitle" style="font-size:13px;"><?php echo acymailing_translation('SEND_EXTERNAL'); ?></span>
					<span><input type="radio" name="config[mailer_method]" onclick="updateMailer('smtp')" value="smtp" <?php if($mailerMethod == 'smtp') echo 'checked="checked"'; ?> id="mailer_smtp"/><label for="mailer_smtp"> SMTP Server</label></span>
					<span><input type="radio" name="config[mailer_method]" onclick="updateMailer('elasticemail')" value="elasticemail" <?php if($mailerMethod == 'elasticemail') echo 'checked="checked"'; ?> id="mailer_elasticemail"/><label for="mailer_elasticemail"> Elastic Email</label></span>
				</div>
				<?php
			}else{
				$values = array('<div class="acyblockoptions" style="padding:10px;"><span class="acyblocktitle" style="font-size:13px;">'.acymailing_translation('SEND_SERVER').'</span>',
					acymailing_selectOption('phpmail', 'PHP Mail Function'),
					acymailing_selectOption('sendmail', 'SendMail'),
					acymailing_selectOption('qmail', 'QMail'),
					'</div><div class="acyblockoptions" style="padding:10px;"><span class="acyblocktitle" style="font-size:13px;">'.acymailing_translation('SEND_EXTERNAL').'</span>',
					acymailing_selectOption('smtp', 'SMTP Server'),
					acymailing_selectOption('elasticemail', 'Elastic Email'),
					'</div>');
				echo acymailing_radio($values, 'config[mailer_method]', 'onchange="updateMailer(this.value)"', 'value', 'text', $mailerMethod);
			}
			?>
		</div>
		<div style="clear: both;"></div>
		<div id="mailer_method_config">
			<div id="sendmail_config" style="display:none" class="acymailing_deploy">
				<span class="acyblocktitle">SendMail</span>
				<table class="acymailing_table" cellspacing="1">
					<tr>
						<td width="185" class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SENDMAIL_PATH_DESC'), acymailing_translation('SENDMAIL_PATH'), '', acymailing_translation('SENDMAIL_PATH')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[sendmail_path]" style="width:160px" value="<?php echo $this->config->get('sendmail_path', '/usr/sbin/sendmail') ?>"/>
						</td>
					</tr>
				</table>
			</div>
			<div id="smtp_config" style="display:none" class="acymailing_deploy">
				<span class="acyblocktitle"><?php echo acymailing_translation('SMTP_CONFIG'); ?></span>
				<table class="acymailing_table" cellspacing="1">
					<tr>
						<td width="185" class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_SERVER_DESC'), acymailing_translation('SMTP_SERVER'), '', acymailing_translation('SMTP_SERVER')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[smtp_host]" style="width:160px" value="<?php echo $this->escape($this->config->get('smtp_host')); ?>"/>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_PORT_DESC'), acymailing_translation('SMTP_PORT'), '', acymailing_translation('SMTP_PORT')); ?>
						</td>
						<td>
							<input class="inputbox" type="text" name="config[smtp_port]" style="width:50px" value="<?php echo $this->escape($this->config->get('smtp_port')); ?>"/>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_SECURE_DESC'), acymailing_translation('SMTP_SECURE'), '', acymailing_translation('SMTP_SECURE')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_secured; ?>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_ALIVE_DESC'), acymailing_translation('SMTP_ALIVE'), '', acymailing_translation('SMTP_ALIVE')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_keepalive; ?>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_AUTHENT_DESC'), acymailing_translation('SMTP_AUTHENT'), '', acymailing_translation('SMTP_AUTHENT')); ?>
						</td>
						<td>
							<?php echo $this->elements->smtp_auth; ?>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('USERNAME_DESC'), acymailing_translation('ACY_USERNAME'), '', acymailing_translation('ACY_USERNAME')); ?>
						</td>
						<td>
							<input class="inputbox" autocomplete="off" type="text" name="config[smtp_username]" style="width:200px" value="<?php echo $this->escape(acymailing_punycode($this->config->get('smtp_username'), 'emailToUTF8')); ?>"/>
						</td>
					</tr>
					<tr>
						<td class="acykey">
							<?php echo acymailing_tooltip(acymailing_translation('SMTP_PASSWORD_DESC'), acymailing_translation('SMTP_PASSWORD'), '', acymailing_translation('SMTP_PASSWORD')); ?>
						</td>
						<td>
							<input class="inputbox" autocomplete="off" type="text" name="config[smtp_password]" style="width:200px" value="<?php echo str_repeat('*', strlen($this->config->get('smtp_password'))); ?>"/>
						</td>
					</tr>
				</table>
				<?php echo $this->toggleClass->toggleText('guessport', '', 'config', acymailing_translation('ACY_GUESSPORT')); ?>
			</div>
			<div id="elasticemail_config" style="display:none" class="acymailing_deploy">
				<span class="acyblocktitle">Elastic Email</span>
				<?php echo acymailing_translation_sprintf('SMTP_DESC', 'Elastic Email'); ?>

				<table class="acymailing_table" cellspacing="1">
					<tr>
						<td width="185" class="acykey">
							<?php echo acymailing_translation('ACY_USERNAME'); ?>
						</td>
						<td>
							<input class="inputbox" autocomplete="off" type="text" name="config[elasticemail_username]" style="width:160px" value="<?php echo $this->config->get('elasticemail_username', '') ?>"/>
						</td>
					</tr>
					<tr>
						<td width="185" class="acykey">
							API Key
						</td>
						<td>
							<input class="inputbox" autocomplete="off" type="text" name="config[elasticemail_password]" style="width:160px" value="<?php echo str_repeat('*', strlen($this->config->get('elasticemail_password'))); ?>"/>
						</td>
					</tr>
					<tr>
						<td width="185" class="acykey">
							<?php echo acymailing_translation('SMTP_PORT'); ?>
						</td>
						<td>
							<?php
							$elasticPort = array();
							$elasticPort[] = acymailing_selectOption('25', 25);
							$elasticPort[] = acymailing_selectOption('2525', 2525);
							$elasticPort[] = acymailing_selectOption('rest', 'REST API');
							echo acymailing_radio($elasticPort, 'config[elasticemail_port]', 'size="1" ', 'value', 'text', $this->config->get('elasticemail_port', 'rest'));
							?>
						</td>
					</tr>
				</table>
				<?php echo acymailing_translation('NO_ACCOUNT_YET').' <a href="'.ACYMAILING_REDIRECT.'elasticemail" target="_blank" >'.acymailing_translation('CREATE_ACCOUNT').'</a>'; ?>
				<?php echo '<br /><a href="'.ACYMAILING_REDIRECT.'smtp_services" target="_blank">'.acymailing_translation('TELL_ME_MORE').'</a>'; ?>
			</div>
		</div>
	</div>
	<div class="onelineblockoptions">
		<span class="acyblocktitle"><?php echo acymailing_translation('ACY_SERVER_CONFIGURATION'); ?></span>
		<table width="100%">
			<tr>
				<td width="50%" valign="top">
					<table class="acymailing_table" cellspacing="1">
						<?php if(!empty($this->elements->special_chars)){ ?>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('ACY_SPECIAL_CHARS_DESC'), acymailing_translation('ACY_SPECIAL_CHARS'), '', acymailing_translation('ACY_SPECIAL_CHARS')); ?>
							</td>
							<td>
								<?php echo $this->elements->special_chars; ?>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('ENCODING_FORMAT_DESC'), acymailing_translation('ENCODING_FORMAT'), '', acymailing_translation('ENCODING_FORMAT')); ?>
							</td>
							<td>
								<?php echo $this->elements->encoding_format; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('CHARSET_DESC'), acymailing_translation('CHARSET'), '', acymailing_translation('CHARSET')); ?>
							</td>
							<td>
								<?php echo $this->elements->charset; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('WORD_WRAPPING_DESC'), acymailing_translation('WORD_WRAPPING'), '', acymailing_translation('WORD_WRAPPING')); ?>
							</td>
							<td>
								<input class="inputbox" type="text" name="config[word_wrapping]" style="width:50px" value="<?php echo $this->config->get('word_wrapping', 0) ?>">
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('ACY_SSLCHOICE_DESC'), acymailing_translation('ACY_SSLCHOICE'), '', acymailing_translation('ACY_SSLCHOICE')); ?>
							</td>
							<td>
								<?php echo $this->elements->ssl_links; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('EMBED_IMAGES_DESC'), acymailing_translation('EMBED_IMAGES'), '', acymailing_translation('EMBED_IMAGES')); ?>
							</td>
							<td>
								<?php echo $this->elements->embed_images; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('EMBED_ATTACHMENTS_DESC'), acymailing_translation('EMBED_ATTACHMENTS'), '', acymailing_translation('EMBED_ATTACHMENTS')); ?>
							</td>
							<td>
								<?php echo $this->elements->embed_files; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('MULTIPLE_PART_DESC'), acymailing_translation('MULTIPLE_PART'), '', acymailing_translation('MULTIPLE_PART')); ?>
							</td>
							<td>
								<?php echo $this->elements->multiple_part; ?>
							</td>
						</tr>
						<tr>
							<td class="acykey">
								<?php echo acymailing_tooltip(acymailing_translation('ACY_DKIM_DESC'), acymailing_translation('ACY_DKIM'), '', acymailing_translation('ACY_DKIM')); ?>
							</td>
							<td>
								<?php echo $this->elements->dkim; ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td valign="top">

					<?php
					if(acymailing_level(1)){
						?>
						<div class="acyblockoptions acymailing_deploy" id="dkim_config" <?php echo ($this->config->get('dkim', 0) == 1) ? 'style="display:block"' : 'style="display:none"' ?> >
							<span class="acyblocktitle"><?php echo acymailing_translation('ACY_DKIM'); ?></span>
							<?php
							$domain = $this->config->get('dkim_domain', '');
							if(empty($domain)){
								$domain = preg_replace(array('#^https?://(www\.)*#i', '#^www\.#'), '', ACYMAILING_LIVE);
								$domain = substr($domain, 0, strpos($domain, '/'));
							}

							if(($this->config->get('dkim_selector', 'acy') != 'acy' && $this->config->get('dkim_selector', 'acy') != '') || $this->config->get('dkim_passphrase', '') != '' || acymailing_getVar('int', 'dkimletme')){
								?>
								<table class="acymailing_table" cellspacing="1">
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_DOMAIN'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" id="dkim_domain" name="config[dkim_domain]" style="width:160px" value="<?php echo $this->escape($domain); ?>"/> *
										</td>
									</tr>
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_SELECTOR'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" id="dkim_selector" name="config[dkim_selector]" style="width:160px" value="<?php echo $this->escape($this->config->get('dkim_selector', 'acy')); ?>"/> *
										</td>
									</tr>
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_PRIVATE'); ?>
										</td>
										<td>
											<textarea cols="65" rows="16" id="dkim_private" style="width:460px;font-size:10px;" name="config[dkim_private]"><?php echo $this->config->get('dkim_private', ''); ?></textarea> *
										</td>
									</tr>
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_PASSPHRASE'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" id="dkim_passphrase" name="config[dkim_passphrase]" style="width:160px" value="<?php echo $this->escape($this->config->get('dkim_passphrase', '')); ?>"/>
										</td>
									</tr>
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_IDENTITY'); ?>
										</td>
										<td>
											<input class="inputbox" type="text" id="dkim_identity" name="config[dkim_identity]" style="width:160px" value="<?php echo $this->escape($this->config->get('dkim_identity', '')); ?>"/>
										</td>
									</tr>
									<tr>
										<td width="185" class="acykey">
											<?php echo acymailing_translation('DKIM_PUBLIC'); ?>
										</td>
										<td>
											<textarea cols="65" rows="5" id="dkim_public" style="width:460px;font-size:10px;" name="config[dkim_public]"><?php echo $this->config->get('dkim_public', ''); ?></textarea>
										</td>
									</tr>
								</table>
							<?php }else{
								if($this->config->get('dkim_private', '') == '' || $this->config->get('dkim_public', '') == ''){
									echo 'Please save your AcyMailing configuration page first';
									acymailing_addScript(false, 'https://www.acyba.com/index.php?option=com_updateme&ctrl=generatedkim');
									?>
									<input type="hidden" id="dkim_private" name="config[dkim_private]"/>
									<input type="hidden" id="dkim_public" name="config[dkim_public]"/>

									<?php
								}else{
									$publicKey = trim(str_replace(array('acy._domainkey	IN	TXT	"', 'v=DKIM1;k=rsa;g=*;s=email;h=sha1;t=s;p=', '-----BEGIN PUBLIC KEY-----', '-----END PUBLIC KEY-----', "\n"), '', $this->config->get('dkim_public', '')), '"');

									echo acymailing_translation_sprintf('DKIM_CONFIGURE', '<input class="inputbox" type="text" id="dkim_domain" name="config[dkim_domain]" style="width:120px;" value="'.$this->escape($domain).'" />'); ?><br/>
									<?php echo acymailing_translation('DKIM_KEY') ?> <input type="text" readonly="readonly" onclick="select();" style="width:80px;font-size:10px;" value="acy._domainkey"/>
									<br/><?php echo acymailing_translation('DKIM_VALUE') ?> <input type="text" readonly="readonly" onclick="select();" style="width:220px;font-size:10px;" value="v=DKIM1;s=email;t=s;p=<?php echo $this->escape($publicKey); ?>"/>
									<br/><input type="checkbox" value="1" id="dkimletme" name="dkimletme"/> <label for="dkimletme"><?php echo acymailing_translation('DKIM_LET_ME'); ?></label>
									<?php
								}
								echo '<br />';
							} ?>
							<span class="acymailing_button_grey">
								<i class="acyicon-help"></i>
								<a style="color:#666;text-decoration: none;" href="https://www.acyba.com/acymailing/156-acymailing-dkim.html" target="_blank"><?php echo acymailing_translation('ACY_HELP'); ?></a>
							</span>
						</div>
						<?php
					}
					?>
				</td>
			</tr>
		</table>
	</div>
</div>
