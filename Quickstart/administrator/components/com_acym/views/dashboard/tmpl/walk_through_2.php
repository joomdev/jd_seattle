<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><input type="hidden" name="step" value="3">
<input type="hidden" name="mailer_method" value="<?php echo $data['mailer_method'] ?>" id="acym__walk-through-2__way-mail">
<div id="acym_walk-through_2" class="cell grid-x">
	<div class="cell large-3"></div>
	<div class="cell grid-x large-6 small-12 acym__walk-through-2__content">
		<a class="acym__walk_through__back" href="<?php echo acym_completeLink('dashboard&task=walkThrough&step=1'); ?>"><i class="fa fa-chevron-left"></i><span><?php echo acym_translation('ACYM_BACK'); ?></span></a>
		<div class="cell grid-x acym__content cell text-center">
			<h1 class="acym__walk-through__content__title cell"><?php echo acym_translation('ACYM_MAIL_CONFIGURATION'); ?></h1>
			<p class="acym__walk-through__step cell"><?php echo acym_translation_sprintf('ACYM_STEP_X', 2, 3); ?></p>
			<p class="cell acym__walk-through-2__send-question"><?php echo acym_translation('ACYM_CONFIGURATION_MAIL_DESCRIPTION'); ?></p>
			<div class="cell acym__walk-through-2__toggle-mail text-center">
				<span id="your-server" class="acym__walk_through_toggle-span <?php echo $data['use_server'] ? 'walk-through_selected' : '' ?>"><?php echo acym_translation('ACYM_USING_YOUR_SERVER'); ?></span>
				<span id="external-server" class="acym__walk_through_toggle-span <?php echo !$data['use_server'] ? 'walk-through_selected' : '' ?>"><?php echo acym_translation('ACYM_USING_AN_EXTERNAL_SERVER'); ?></span>
			</div>
			<div class="cell grid-x acym__walk-through-2__choose_your-server" <?php echo $data['use_server'] ? '' : 'style="display:none"' ?>>
				<div class="medium-3"></div>
				<button id="phpmail" class="cell medium-6 acym__walk-through-2__button button <?php echo ($data['use_server'] && $data['mailer_method'] == 'phpmail') || !$data['use_server'] ? 'your_server_selected' : 'unselected' ?>"><?php echo acym_translation('ACYM_PHP_MAIL_FUNCTION'); ?></button>
				<div class="medium-3"></div>
				<div class="medium-3"></div>
				<button id="sendmail" class="cell medium-6 acym__walk-through-2__button button <?php echo ($data['use_server'] && $data['mailer_method'] != 'sendmail') || !$data['use_server'] ? 'unselected' : 'your_server_selected' ?>">SendMail</button>
				<div class="medium-3"></div>
				<div class="medium-3"></div>
				<button id="qmail" class="cell medium-6 acym__walk-through-2__button button <?php echo ($data['use_server'] && $data['mailer_method'] != 'qmail') || !$data['use_server'] ? 'unselected' : 'your_server_selected' ?>">QMail</button>
				<div class="medium-3"></div>
			</div>
			<div class="cell grid-x acym__walk-through-2__choose_external-server" <?php echo $data['use_server'] ? 'style="display:none"' : '' ?>>
				<span id="smtp" class=" shrink acym__walk_through_toggle-span <?php echo (!$data['use_server'] && $data['mailer_method'] == 'smtp') || $data['use_server'] ? 'walk-through_selected' : ''; ?>"><?php echo acym_translation('ACYM_SMTP'); ?></span>
				<span id="elasticemail" class="shrink acym__walk_through_toggle-span <?php echo (!$data['use_server'] && $data['mailer_method'] == 'smtp') || $data['use_server'] ? '' : 'walk-through_selected'; ?>">Elastic Email</span>
				<div class="cell grid-x acym__walk-through-2__smtp-server" <?php echo (!$data['use_server'] && $data['mailer_method'] == 'smtp') || $data['use_server'] ? '' : 'style="display:none"'; ?>>
					<label for="acym__walk-through-2__smtp_server" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_SERVER'); ?></label>
					<input <?php echo empty($data['smtp_host']) ? '' : 'value="'.$data['smtp_host'].'"' ?> type="text" class="input-group-field cell" name="smtp[server]" id="acym__walk-through-2__smtp_server" placeholder="<?php echo acym_translation('ACYM_SERVER_ADDRESS'); ?>">
					<label for="acym__walk-through-2__smtp_port" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_PORT'); ?></label>
					<input <?php echo empty($data['smtp_port']) ? '' : 'value="'.$data['smtp_port'].'"' ?> type="text" class="input-group-field cell" name="smtp[port]" id="acym__walk-through-2__smtp_port" placeholder="<?php echo acym_translation('ACYM_SMTP_PORT'); ?>">
					<div id="available_ports" class="cell text-left">
						<a href="#" id="available_ports_check"><?php echo acym_translation('ACYM_SMTP_AVAILABLE_PORTS'); ?></a>
					</div>
					<label for="acym__walk-through-2__smtp_secure" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_SECURE'); ?></label>
                    <?php
                    $secure = array('ssl' => 'SSL', 'tsl' => 'TSL');
                    echo acym_select($secure, 'smtp[secure]', empty($data['smtp_secured']) ? $secure['ssl'] : $data['smtp_secured'], 'class="acym__walk-through__select" id="acym__walk-through-2__smtp_secure"', 'value', 'name'); ?>
					<div class="cell medium-6 grid-x">
                        <?php echo acym_switch('smtp[keepalive]', empty($data['smtp_keepalive']) ? '0' : '1', acym_translation('ACYM_SMTP_ALIVE')); ?>
					</div>
					<div class="cell medium-6 grid-x">
                        <?php echo acym_switch('smtp[auth]', empty($data['smtp_auth']) ? '0' : '1', acym_translation('ACYM_SMTP_AUTHENTICATION')); ?>
					</div>
					<label for="acym__walk-through-2__smtp_username" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_USERNAME'); ?></label>
					<input <?php echo empty($data['smtp_username']) ? '' : 'value="'.$data['smtp_username'].'"' ?> type="text" class="input-group-field cell" name="smtp[username]" id="acym__walk-through-2__smtp_username" placeholder="<?php echo acym_translation('ACYM_SMTP_USERNAME'); ?>">
					<label for="acym__walk-through-2__smtp_password" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?></label>
					<input <?php echo empty($data['smtp_password']) ? '' : 'value="'.str_repeat('*', strlen($data['smtp_password'])).'"' ?> type="text" class="input-group-field cell" name="smtp[password]" id="acym__walk-through-2__smtp_password" placeholder="<?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?>">
				</div>
				<div class="cell grid-x acym__walk-through-2__elastic-email" <?php echo !$data['use_server'] && $data['mailer_method'] == 'smtp' ? 'style="display:none"' : ''; ?> <?php echo $data['use_server'] ? 'style="display:none"' : ''; ?>>
					<label for="acym__walk-through-2__elastic_username" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_USERNAME'); ?></label>
					<input <?php echo empty($data['elasticemail_username']) ? '' : 'value="'.$data['elasticemail_username'].'"' ?> type="text" class="input-group-field cell" name="elastic[username]" id="acym__walk-through-2__elastic_username" placeholder="<?php echo acym_translation('ACYM_SMTP_USERNAME'); ?>">
					<label for="acym__walk-through-2__elastic_password" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?></label>
					<input <?php echo empty($data['elasticemail_password']) ? '' : 'value="'.$data['elasticemail_password'].'"' ?> type="text" class="input-group-field cell" name="elastic[password]" id="acym__walk-through-2__elastic_password" placeholder="<?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?>">
					<div class="cell medium-up-2 grid-x acym__walk-through-2__elastic_port">
						<span><?php echo acym_translation('ACYM_SMTP_PORT'); ?></span>
                        <?php
                        $ports = array(
                            '25' => '25',
                            '2525' => '2525',
                            'restapi' => acym_translation('ACYM_REST_API'),
                        );
                        echo acym_radio($ports, 'elastic[port]', empty($data['elasticemail_port']) ? 'restapi' : $data['elasticemail_port']);
                        ?>
					</div>

				</div>
			</div>
			<div class="cell text-center">
				<div class="large-auto"></div>
				<button data-task="step2" class="button acy_button_submit acym__walk-through__content__save large-shrink"><?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?></button>
				<div class="large-auto"></div>
			</div>
		</div>
	</div>
</div>
