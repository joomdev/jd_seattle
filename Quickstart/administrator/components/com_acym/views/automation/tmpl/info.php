<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" data-abide novalidate>
	<input type="hidden" name="id" value="<?php echo !empty($data['automation']->id) ? $data['automation']->id : '' ?>">
	<input type="hidden" name="stepAutomationId" value="<?php echo empty($data['step']->id) ? '' : $data['step']->id ?>">
	<div class="acym__content cell grid-x" id="acym__automation__info">
        <?php
        $workflow = acym_get('helper.workflow');
        if (empty($data['automation']->id)) {
            $this->edition = 1;
            $workflow->disabledAfter = 'info';
        }
        echo $workflow->display($this->steps, $this->step, $this->edition);
        ?>
		<div class="cell grid-x grid-margin-x">
			<div class="medium-12 small-12 cell grid-x acym__content acym__automation__info__first">
				<h6 class="cell acym__content__title__light-blue"><?php echo acym_translation('ACYM_INFORMATION') ?></h6>
				<label class="cell medium-6">
					<h6 class=""><?php echo acym_translation('ACYM_NAME') ?></h6>
					<input required type="text" name="automation[name]" value="<?php echo !empty($data['automation']->name) ? $data['automation']->name : ''; ?>">
				</label>
				<div class="cell medium-shrink grid-x acym_vcenter margin-left-2" <?php echo empty($data['automation']->id) ? 'style="display: none"' : '' ?>>
                    <?php echo acym_switch('automation[active]', $data['automation']->active, acym_translation('ACYM_ACTIVE')); ?>
				</div>
				<label class="cell">
					<h6 id="acym__automation__info__desc__button" class="cursor-pointer"><?php echo acym_translation('ACYM_DESCRIPTION') ?><i class="material-icons">keyboard_arrow_down</i></h6>
					<textarea style="display: none" name="automation[description]" rows="6" class="margin-top-1"><?php echo !empty($data['automation']->description) ? $data['automation']->description : ''; ?></textarea>
				</label>
			</div>
			<div class="medium-12 cell grid-x acym__content acym__automation__info__trigger margin-top-2">
				<h6 class="cell acym__content__title__light-blue margin-bottom-2"><?php echo acym_translation('ACYM_CHOOSE_TRIGGER') ?></h6>
				<div class="cell grid-x margin-bottom-2" id="acym__automation__info__choose__trigger__type">
					<input type="hidden" name="type_trigger" value="<?php echo !empty($data['type_trigger']) ? $data['type_trigger'] : 'classic'; ?>" id="acym__automation__trigger__type__input">
					<div class="cell auto"></div>
					<p id="acym__automation__info__group" data-trigger-type="classic" class="shrink cell <?php echo((!empty($data['type_trigger']) && $data['type_trigger'] == 'classic') ? 'selected-trigger' : (empty($data['type_trigger']) ? 'selected-trigger' : '')); ?> margin-right-2"><?php echo acym_translation('ACYM_CLASSIC_TRIGGER') ?></p>
					<p id="acym__automation__info__one-user" data-trigger-type="user" class="shrink cell <?php echo((!empty($data['type_trigger']) && $data['type_trigger'] == 'user') ? 'selected-trigger' : '') ?>"><?php echo acym_translation('ACYM_TRIGGER_BASED_ON_USER_ACTIONS') ?></p>
					<div class="cell auto"></div>
				</div>
				<div class="acym__automation__info__choose__trigger cell grid-x grid-margin-x grid-margin-y" id="acym__automation__info__choose__trigger__classic" <?php echo((!empty($data['type_trigger']) && $data['type_trigger'] == 'classic') ? '' : (empty($data['type_trigger']) ? '' : 'style="display: none"')); ?>>
					<div class="cell large-6 acym__content grid-x acym__automation__draggable">
						<h6 class="acym__content__title__light-blue"><?php echo acym_translation('ACYM_ALL_TRIGGER'); ?></h6>
						<div class="cell acym__automation__all-trigger__classic grid-x">
                            <?php foreach ($data['classic'] as $key => $classic) {
                                echo '<div '.(in_array($key, $data['defaultValues']) ? 'style="display: none"' : '').' class="acym__automation__trigger__droppable__classic margin-top-1 cell" data-trigger="'.$key.'">'.$classic->name.'<span class="acym__automation__trigger__action">'.$classic->option.'</span></div>';
                            } ?>
						</div>
					</div>

					<div class="cell large-6 acym__content grid-x acym__automation__droppable__classic">
						<h6 class="acym__content__title__light-blue cell"><?php echo acym_translation('ACYM_DRAG_YOUR_TRIGGERS'); ?></h6>
						<div class="cell acym__automation__user-trigger__classic acym__automation__trigger__sortable">
                            <?php
                            foreach ($data['classic'] as $key => $classic) {
                                if (!in_array($key, $data['defaultValues'])) {
                                    continue;
                                }
                                ?>
								<div class="acym__automation__droppable__trigger margin-top-1">
									<div class="acym__automation__one__trigger">
                                        <?php echo $classic->name; ?>
										<span class="acym__automation__trigger__action"><?php echo $classic->option; ?></span>
									</div>
									<i data-trigger-show="<?php echo $key; ?>" class="material-icons acym__color__red acym__automation__delete__trigger cursor-pointer">close</i>
								</div>
                                <?php
                            } ?>
						</div>
					</div>
				</div>
				<div class="acym__automation__info__choose__trigger cell grid-x grid-margin-x grid-margin-y" id="acym__automation__info__choose__trigger__user" <?php echo((!empty($data['type_trigger']) && $data['type_trigger'] == 'user') ? '' : 'style="display: none"') ?>>
					<div class="cell large-6 acym__content grid-x acym__automation__draggable">
						<h6 class="acym__content__title__light-blue"><?php echo acym_translation('ACYM_ALL_TRIGGER'); ?></h6>
						<div class="cell acym__automation__all-trigger__action grid-x">
                            <?php foreach ($data['user'] as $key => $triggerUser) {
                                echo '<div '.(in_array($key, $data['defaultValues']) ? 'style="display: none"' : '').' class="acym__automation__trigger__droppable__action margin-top-1 cell" data-trigger="'.$key.'">'.$triggerUser->name.'<span class="acym__automation__trigger__action">'.$triggerUser->option.'</span></div>';
                            } ?>
						</div>
					</div>

					<div class="cell large-6 acym__content grid-x acym__automation__droppable__action">
						<h6 class="acym__content__title__light-blue cell"><?php echo acym_translation('ACYM_DRAG_YOUR_TRIGGERS'); ?></h6>
						<div class="cell acym__automation__user-trigger__action acym__automation__trigger__sortable">
                            <?php foreach ($data['user'] as $key => $triggerUser) {
                                if (in_array($key, $data['defaultValues'])) {
                                    echo '<div class="acym__automation__droppable__trigger margin-top-1"><div class="acym__automation__one__trigger">'.$triggerUser->name.'<span class="acym__automation__trigger__action">'.$triggerUser->option.'</span></div><i data-trigger-show="'.$key.'" class="material-icons acym__color__red acym__automation__delete__trigger cursor-pointer">close</i></div>';
                                }
                            } ?>
						</div>
					</div>
				</div>
			</div>
			<div class="cell grid-x margin-top-2">
				<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("lists") ?>
				</div>
				<div class="cell medium-auto grid-x grid-margin-x text-right">
					<div class="cell auto"></div>
					<button class="cell medium-shrink button medium-margin-bottom-0 acy_button_submit button-secondary" type="button" data-task="saveExitInfo"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
					<button class="cell medium-shrink button margin-bottom-0 acy_button_submit" type="button" data-task="saveInfo"><?php echo acym_translation('ACYM_SAVE_CONTINUE') ?></button>
				</div>
			</div>
		</div>
	</div>
    <?php echo acym_formOptions(true) ?>
</form>
