<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>
	<input type="hidden" name="bounce[id]" value="<?php echo empty($data['rule']) ? '' : intval($data['rule']->id); ?>">
	<input type="hidden" name="bounce[ordering]" value="<?php echo empty($data['rule']) ? '' : intval($data['rule']->ordering); ?>">
	<div id="acym__bounces__listing" class="acym__content grid-x cell grid-margin-x margin-left-0">
		<div class="cell grid-x">
			<div class="hide-for-small-only medium-auto"></div>
			<button type="button" data-task="apply" class="button button-secondary acy_button_submit cell large-shrink medium-shrink small-12"><?php echo acym_translation('ACYM_APPLY'); ?></button>
			<button type="button" data-task="save" class="button margin-left-2 acy_button_submit cell large-shrink medium-shrink small-12"><?php echo acym_translation('ACYM_SAVE_EXIT'); ?></button>
		</div>
		<div class="acym__content cell grid-x large-6">
			<h1 class="acym__title__light__blue cell"><?php echo acym_translation('ACYM_GLOBAL_INFORMATION'); ?></h1>
			<label class="cell grid-x">
				<span class="cell medium-4 acym__label"><?php echo acym_translation('ACYM_NAME'); ?></span>
				<input required class="cell medium-7" type="text" name="bounce[name]" value="<?php echo empty($data['rule']) ? '' : $data['rule']->name; ?>">
			</label>
			<label class="cell grid-x">
				<span class="cell medium-4 acym__label"><?php echo acym_translation('ACYM_REGEX'); ?></span>
				<span class="cell medium-8 acym__label grid-x">
					<span style="margin-left: -10px">#</span><input class="intext_input_large medium-11 cell" type="text" name="bounce[regex]" value="<?php echo acym_escape((empty($data['rule']) || empty($data['rule']->regex)) ? '' : $data['rule']->regex); ?>"><span class="medium-1 cell">#ims</span>
				</span>
			</label>
			<div class="cell grid-x margin-top-1"><?php echo acym_switch('bounce[active]', (empty($data['rule']) ? 1 : $data['rule']->active), acym_translation('ACYM_ENABLED'), [], 'medium-4'); ?></div>
            <?php
            $valuesRegex = [
                'senderInfo' => acym_translation('ACYM_SENDER_INFORMATION'),
                'subject' => acym_translation('ACYM_EMAIL_SUBJECT'),
                'body' => acym_translation('ACYM_BODY'),
            ];
            acym_checkbox($valuesRegex, 'bounce[executed_on][]', (empty($data['rule']) || empty($data['rule']->executed_on)) ? [] : $data['rule']->executed_on, acym_translation('ACYM_EXECUTE_REGEX_ON'), 'cell margin-top-1 grid-x', 'medium-4 margin-right-1'); ?>
			<div class="cell grid-x margin-top-1"><?php echo acym_switch('bounce[increment_stats]', (!empty($data['rule']) ? $data['rule']->increment_stats : 1), acym_translation('ACYM_INCREMENT_BOUNCE_STATISTICS_IF_RULE_MATCHES'), [], 'medium-4'); ?></div>
		</div>
		<div class="acym__content cell grid-x large-6">
			<div class="cell">
				<h1 class="acym__title__light__blue"><?php echo acym_translation('ACYM_ACTION_ON_USER'); ?></h1>
				<div class="cell grid-x">
					<p class="acym__label"><?php echo acym_translation_sprintf('ACYM_EXECUTE_ACTIONS_AFTER', '<input type="text" name="bounce[execute_action_after]" value="'.acym_escape(!empty($data['rule']) ? $data['rule']->execute_action_after : '0').'" class="intext_input">'); ?></p>
                    <?php
                    $valuesActionUser = [
                        'delete_user_subscription' => acym_translation('ACYM_DELETE_USER_SUBSCRITION'),
                        'unsubscribe_user' => acym_translation('ACYM_UNSUBSCRIBE_USER'),
                        'block_user' => acym_translation('ACYM_BLOCK_USER'),
                        'delete_user' => acym_translation('ACYM_DELETE_USER'),
                        'empty_queue_user' => acym_translation('ACYM_EMPTY_QUEUE_USER'),
                        'subscribe_user' => '<div class="cell shrink margin-right-1 acym__label">'.acym_translation('ACYM_SUBSCRIBE_USER_TO').'</div><div class="cell large-6 input__in__checkbox acym__bounce__select__subscribe">'.acym_select($data['lists'], 'bounce[subscribe_user_list]', (!empty($data['rule']) && !empty($data['rule']->action_user['subscribe_user_list'])) ? $data['rule']->action_user['subscribe_user_list'] : '', 'class="acym__select shrink"').'</div>',
                    ];
                    echo '<div class="margin-top-1 margin-left-2">';
                    acym_checkbox($valuesActionUser, 'bounce[action_user][]', (empty($data['rule']) || empty($data['rule']->action_user)) ? [] : $data['rule']->action_user);
                    echo '</div>'; ?>
				</div>
			</div>
			<div class="cell">
				<h1 class="acym__title__light__blue"><?php echo acym_translation('ACYM_ACTION_ON_EMAIL'); ?></h1>
				<div class="cell grid-x">
                    <?php
                    $valuesActionEmail = [
                        'save_message' => acym_translation('ACYM_SAVE_MESSAGE_DATABASE'),
                        'delete_message' => acym_translation('ACYM_DELETE_MESSAGE_FROM_MAILBOX'),
                        'forward_message' => '<div class="cell medium-10 grid-x"><span class="medium-4 cell acym__label">'.acym_translation('ACYM_FORWARD_EMAIL').'</span><input class="medium-7 input__in__checkbox cell" type="email" name="bounce[action_message][forward_to]" value="'.(!empty($data['rule']) && in_array('forward_message', $data['rule']->action_message) ? $data['rule']->action_message['forward_to'] : '').'"></div>',
                    ];
                    echo '<div class="cell grid-x margin-top-1 margin-left-2">';
                    acym_checkbox($valuesActionEmail, 'bounce[action_message][]', (!empty($data['rule']) && !empty($data['rule']->action_message)) ? $data['rule']->action_message : []);
                    echo '</div>';
                    ?>
				</div>
			</div>
		</div>
	</div>
    <?php acym_formOptions(); ?>
</form>

