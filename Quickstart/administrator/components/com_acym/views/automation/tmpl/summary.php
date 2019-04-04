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
	<input type="hidden" name="id" value="<?php echo empty($data['id']) ? '' : $data['id'] ?>">
	<div class="cell grid-x">
		<div class="cell auto"></div>
		<div class="acym__content grid-x cell large-6 medium-12" id="acym__automation__summary">
            <?php
            $workflow = acym_get('helper.workflow');
            echo $workflow->display($this->steps, 'summary', $this->edition);
            if (!empty($data['id'])) {
                ?>
				<div class="acym__automation__summary__info cell grid-x acym__content">
					<h6 class="cell acym__content__title__light-blue"><?php echo acym_translation('ACYM_INFORMATION') ?></h6>
					<div class="cell acym__automation__summary__information__one"><span class="acym__automation__summary__information__one__title"><?php echo acym_translation('ACYM_NAME_SUMMARY') ?></span> : <?php echo $data['automation']->name; ?></div>
					<div class="cell acym__automation__summary__information__one"><span class="acym__automation__summary__information__one__title"><?php echo acym_translation('ACYM_DESCRIPTION') ?></span> : <?php echo $data['automation']->description ?></div>
				</div>
				<div class="acym__automation__summary__filters cell grid-x margin-top-2 acym__content">
					<h6 class="cell acym__content__title__light-blue"><?php echo acym_translation('ACYM_TRIGGERS'); ?></h6>
					<div class="cell acym__automation__summary__information__one"><span class="acym__automation__summary__information__one__title"><?php echo acym_translation('ACYM_AUTOMATION_TRIGGER') ?></span></div>
					<br/>
					<div class="cell acym__automation__summary__information__one">
                        <?php echo implode('<br /><span class="acym__automation__summary__information__one__title">'.acym_translation('ACYM_OR').'</span><br />', $data['step']->triggers) ?>
					</div>
				</div>
            <?php } ?>
			<div class="acym__automation__summary__actions cell grid-x margin-top-2 acym__content">
				<h6 class="cell acym__content__title__light-blue"><?php echo acym_translation('ACYM_FILTERS'); ?></h6>
				<div class="cell acym__automation__summary__information__one grid-x">
                    <?php
                    if (!empty($data['action']->filters)) {
                        $orNum = 0;
                        $andNum = 0;
                        echo '<span class="acym__automation__summary__information__one__title">'.acym_translation_sprintf('ACYM_FILTERS_APPLY_TO', acym_translation(empty($data['id']) ? 'ACYM_MASS_ACTION' : 'ACYM_AUTOMATION'), acym_translation($data['action']->filters['type_filter'] == 'classic' ? 'ACYM_ALL_ACYMAILING_USERS' : 'ACYM_ONE_ACYMAILING_USER')).'</span></div><div class="acym__automation__summary__information__one">';
                        foreach ($data['action']->filters as $or => $orValues) {
                            if ($or === 'type_filter') continue;
                            $andNum = 0;
                            if ($orNum > 0) echo '<span class="acym__automation__summary__information__one__title">'.acym_translation('ACYM_OR').'</span><br />';
                            foreach ($orValues as $and => $andValue) {
                                if ($andNum > 0) echo '<span class="acym__automation__summary__information__one__title">'.acym_translation('ACYM_AND').'</span><br />';
                                echo $andValue.'<br />';
                                $andNum++;
                            }
                            $orNum++;
                        }
                    } else {
                        echo '<strong class="acym__color__red cell text-center">'.acym_translation('ACYM_SELECT_FILTERS').'</strong>';
                    }
                    ?>
				</div>
			</div>
			<div class="acym__automation__summary__actions cell grid-x margin-top-2 margin-bottom-2 acym__content">
				<h6 class="cell acym__content__title__light-blue"><?php echo acym_translation('ACYM_ACTIONS'); ?></h6>
				<div class="cell acym__automation__summary__information__one grid-x">
                    <?php
                    if (!empty($data['action']->actions)) {
                        echo '<span class="acym__automation__summary__information__one__title">'.acym_translation_sprintf('ACYM_ACTIONS_USER_WILL', acym_translation(empty($data['id']) ? 'ACYM_MASS_ACTION' : 'ACYM_AUTOMATION')).'</span></div><div class="acym__automation__summary__information__one">';
                        $andNum = 0;
                        foreach ($data['action']->actions as $and => $andValue) {
                            if ($andNum > 0) echo '<span class="acym__automation__summary__information__one__title">'.acym_translation('ACYM_AND').'</span><br />';
                            echo $andValue.'<br />';
                            $andNum++;
                        }
                    } else {
                        echo '<strong class="acym__color__red cell text-center">'.acym_translation('ACYM_SELECT_ACTIONS').'</strong>';
                    }
                    ?>
				</div>
			</div>
			<div class="cell grid-x grid-margin-x">
				<div class="auto cell"></div>
                <?php if (empty($data['id'])) { ?>
					<button type="button" data-task="listing" class="cell shrink button-secondary button acy_button_submit"><?php echo acym_translation('ACYM_CANCEL') ?></button>
					<button type="button" data-task="processMassAction" class="cell shrink button acy_button_submit"><?php echo acym_translation('ACYM_PROCESS_MASS_ACTION') ?></button>
                <?php } else { ?>
					<button type="button" data-task="listing" class="cell shrink button button-secondary acy_button_submit"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
					<button type="button" data-task="activeAutomation" class="cell shrink button acy_button_submit"><?php echo acym_translation('ACYM_ACTIVE_AUTOMATION') ?></button>
                <?php } ?>
			</div>
		</div>
		<div class="cell auto"></div>
	</div>
    <?php echo acym_formOptions(true) ?>
</form>
