<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php //__START__enterprise_
if (acym_level(2)) {
    ?>
	<div class="acym_area padding-vertical-1 padding-horizontal-2">
		<div class="acym_area_title"><?php echo acym_translation('ACYM_BOUNCE_HANDLING'); ?></div>

		<div class="grid-x">
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_BOUNCE_EMAIL'); ?></span>
				<input class="cell medium-4" type="email" name="config[bounce_email]" placeholder="<?php echo acym_translation('ACYM_BOUNCE_EMAIL_PLACEHOLDER'); ?>" value="<?php echo $data['config']->get('bounce_email'); ?>"/>
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_SMTP_SERVER'); ?></span>
				<input class="cell medium-4" type="text" name="config[bounce_server]" value="<?php echo $data['config']->get('bounce_server'); ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_SMTP_PORT'); ?></span>
				<input class="cell medium-2" type="text" name="config[bounce_port]" value="<?php echo $data['config']->get('bounce_port'); ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_CONNECTION_METHOD'); ?></span>
				<div class="cell medium-2">
                    <?php
                    $connectionMethods = array(
                        "" => "---",
                        'imap' => 'IMAP',
                        'pop3' => 'POP3',
                    );

                    echo acym_select($connectionMethods, 'config[bounce_connection]', $data['config']->get('bounce_connection', 'imap'), null, '', '', 'acym__config__bounce__connection');
                    ?>
				</div>
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_SMTP_SECURE'); ?></span>
				<div class="cell medium-2">
                    <?php
                    $secureMethods = array(
                        "" => "---",
                        "ssl" => "SSL",
                        "tls" => "TLS",
                    );

                    echo acym_select($secureMethods, 'config[bounce_secured]', $data['config']->get('bounce_secured', 'ssl'), null, "", "", 'acym__config__bounce__secure_method');
                    ?>
				</div>
			</label>
			<div class="cell grid-x">
                <?php echo acym_switch('config[bounce_certif]', $data['config']->get('bounce_certif', 1), acym_translation('ACYM_SELF_SIGNED_CERTIFICATE'), array(), 'medium-3'); ?>
			</div>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_SMTP_USERNAME'); ?></span>
				<input class="cell medium-4" type="text" name="config[bounce_username]" value="<?php echo $data['config']->get('bounce_username'); ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_SMTP_PASSWORD'); ?></span>
				<input class="cell medium-4" type="text" name="config[bounce_password]" value="<?php echo str_repeat('*', strlen($data['config']->get('bounce_password'))); ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_CONNECTION_TIMEOUT_SECOND'); ?></span>
				<input class="cell medium-2" type="text" name="config[bounce_timeout]" value="<?php echo $data['config']->get('bounce_timeout', 10); ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-3"><?php echo acym_translation('ACYM_MAX_NUMBER_EMAILS'); ?></span>
				<input class="cell medium-2" type="text" name="config[bounce_max]" value="<?php echo $data['config']->get('bounce_max', 100); ?>">
			</label>
			<div class="cell grid-x">
                <?php echo acym_switch('config[auto_bounce]', $data['config']->get('auto_bounce'), acym_translation('ACYM_ENABLE_AUTO_BOUNCE'), array(), 'medium-3'); ?>
			</div>
			<div class="cell grid-x grid-margin-x" id="acym__configuration__bounce__auto_bounce__configuration" <?php echo $data['config']->get('auto_bounce') ? '' : "style='display: none'" ?>>
				<div class="cell grid-x">
					<label class="cell medium-3"><?php echo acym_translation('ACYM_FREQUENCY'); ?></label>
					<div class="cell medium-4">
                        <?php $delayTypeBounceAuto = acym_get('type.delay');
                        echo $delayTypeBounceAuto->display('config[auto_bounce_frequency]', $data['config']->get('auto_bounce_frequency', 21600), 1);
                        ?>
					</div>
				</div>
				<div class="cell grid-x">
					<span class="cell medium-3"><?php echo acym_translation('ACYM_LAST_RUN'); ?></span>
					<span class="cell medium-4"><?php echo $data['config']->get('auto_bounce_last') ?></span>
				</div>
				<div class="cell grid-x">
					<span class="cell medium-3"><?php echo acym_translation('ACYM_NEXT_RUN_TIME'); ?></span>
					<span class="cell medium-4"><?php echo $data['config']->get('auto_bounce_next') ?></span>
				</div>
				<div class="cell grid-x">
					<span class="cell medium-3"><?php echo acym_translation('ACYM_REPORT'); ?></span>
					<span class="cell medium-4"><?php echo $data['config']->get('auto_bounce_report') ?></span>
				</div>
			</div>
		</div>
	</div>
    <?php
}
if (!acym_level(2)) {
    $data['version'] = 'enterprise';
    echo '<div class="acym_area">
            <div class="acym_area_title">'.acym_translation('ACYM_BOUNCE_HANDLING').'</div>';
    include(ACYM_VIEW.'dashboard'.DS.'tmpl'.DS.'upgrade.php');
    echo '</div>';
} ?>
