<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl').'&id='.htmlspecialchars($data['listId'])); ?>" method="post" name="acyForm">
	<div id="acym__unsubscribe" class="acym__content">

        <?php
        $workflow = acym_get('helper.workflow');
        echo $workflow->display($this->steps, $this->step, $this->edition);
        ?>

		<div class="grid-x margin-bottom-0">
			<div class="cell acym__unsubscribe__title">
                <?php echo '<b>'.acym_translation('ACYM_UNSUBSCRIBE_MAIL').'</b> ('.strtolower(acym_translation('ACYM_OPTIONAL')).')'; ?>
			</div>
            <?php if (empty($data['unsubscribeMails']) && empty($data["search"])){ ?>
				<h1 class="cell text-center acym__listing__empty__title"><?php echo acym_translation_sprintf('ACYM_NO_TEMPLATE', strtolower(acym_translation('ACYM_UNSUBSCRIBE'))); ?><a href="<?php echo acym_completeLink('mails&task=edit&step=editEmail&type=unsubscribe&type_editor=acyEditor&return='.urlencode(acym_completeLink('lists&task=edit&step=unsubscribe&id='.$data['listId'].'&edition=1'))) ?>"> <?php echo acym_translation('ACYM_CREATE_ONE') ?></a></h1>
            <?php }else{ ?>
			<div class="cell acym__unsubscribe__subtitle">
                <?php echo acym_translation_sprintf('ACYM_LIST_MAIL_SELECT', '<b>'.strtolower(acym_translation('ACYM_UNSUBSCRIBE')).'</b>'); ?>
			</div>
			<div class="large-6 medium-8 cell">
                <?php echo acym_filterSearch(htmlspecialchars($data["search"]), 'unsubscribe_search', 'ACYM_SEARCH_TEMPLATE'); ?>
			</div>
		</div>
    <?php if (!empty($data['unsubscribeMails'])) { ?>
		<div class="grid-x grid-padding-x grid-padding-y grid-margin-x grid-margin-y xxlarge-up-6 large-up-4 medium-up-3 small-up-1">
            <?php echo acym_selectTemplates($data['unsubscribeMails'], $data['selectedUnsubscribe'], 'unsubscribe', $data['listId']); ?>
		</div>
    <?php } ?>
    <?php echo $data['pagination']->display('unsubscribe'); ?>
    <?php } ?>
		<div class="cell grid-x">
			<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                <?php echo acym_backToListing("lists") ?>
			</div>
			<div class="cell medium-auto grid-x text-right">
				<div class="cell medium-auto"></div>
				<button data-task="save" data-step="listing" type="submit" class="cell medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit"><?php echo acym_translation('ACYM_SAVE_EXIT') ?></button>
			</div>
		</div>
	</div>
	<input type="hidden" name="typeMail" value="unsubscribe">
	<input type="hidden" name="id" value="<?php echo htmlspecialchars($data['listId']); ?>">
    <?php echo acym_formOptions(true, 'edit', 'unsubscribe'); ?>
</form>
