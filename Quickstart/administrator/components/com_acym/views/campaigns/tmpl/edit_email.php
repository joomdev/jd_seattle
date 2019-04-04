<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" class="acym__form__campaign__edit" data-abide novalidate>
	<input type="hidden" value="<?php echo htmlspecialchars($data['campaignID']); ?>" name="id" id="acym__campaign__recipients__form__campaign">
    <?php echo $data['needDisplayStylesheet'] ?>
	<div class="grid-x">
		<div class="cell medium-auto"></div>
		<div class="cell acym__content xxlarge-6 large-9">

            <?php
            $workflow = acym_get('helper.workflow');
            if (empty($data['campaignID'])) {
                $workflow->disabledAfter = 'editEmail';
            }
            echo $workflow->display($this->steps, $this->step, $this->edition);
            ?>
			<div class="cell margin-bottom-2">
				<label>
                    <?php echo acym_translation('ACYM_CAMPAIGN_NAME') ?>
					<input name="mail[name]" type="text" class="acy_required_field" value="<?php echo htmlspecialchars($data['mailInformation']->name) ?>" required>
				</label>
			</div>
			<div class="cell">
				<label>
                    <?php echo acym_translation('ACYM_EMAIL_SUBJECT') ?>
					<div class="input-group">
						<input id="acym_subject_field" name="mail[subject]" type="text" class="acy_required_field" value="<?php echo htmlspecialchars($data['mailInformation']->subject) ?>" required>
                        <?php if ($data['editor']->editor == 'acyEditor') { ?>
							<button class="button" id="dtext_subject_button"><i class="mce-ico mce-i-codesample"></i></button>
                        <?php } ?>
					</div>
				</label>
			</div>
			<div class="cell">
				<label>
                    <?php echo acym_translation('ACYM_TAGS'); ?>
                    <?php echo acym_selectMultiple($data['allTags'], "template_tags", !empty($data['mailInformation']->tags) ? $data['mailInformation']->tags : array(), array('id' => 'acym__tags__field', 'placeholder' => acym_translation('ACYM_ADD_TAGS')), "name", "name"); ?>
				</label>
			</div>
			<div class="cell grid-x" id="acym__campaigns__edit_email__attachments">
				<label class="cell"><?php echo acym_translation('ACYM_ATTACHMENTS'); ?></label>
                <?php if (!empty($data['mailInformation']->attachments)) { ?>
                    <?php
                    foreach ($data['mailInformation']->attachments as $i => $oneAttach) {
                        $onlyFilename = explode("/", $oneAttach->filename);

                        $onlyFilename = end($onlyFilename);

                        if (strlen($onlyFilename) > 40) {
                            $onlyFilename = substr($onlyFilename, 0, 15)."...".substr($onlyFilename, strlen($onlyFilename) - 15);
                        }

                        echo '<div class="acym__listing__row cell grid-x" id="acym__campaigns__attach__del'.$i.'">';

                        echo acym_tooltip('<span class="cell acym__campaigns__attachments__already">'.$onlyFilename.' ('.(round($oneAttach->size / 1000, 1)).' Ko)</span>', $oneAttach->filename, 'medium-11 cell');
                        echo '<div class="cell medium-1 text-center"><a data-id="'.$i.'" data-mail="'.$data['mailInformation']->mail_id.'" class="acym__campaigns__attach__delete"><i class="fa fa-trash-o acym__color__red"></i></a></div>';
                        echo '</div>';
                    }
                }

                $uploadfileType = acym_get('type.uploadFile');
                for ($i = 0; $i < 10; $i++) {
                    $result = '<div '.($i >= 1 ? 'style="display:none"' : '').' class="cell grid-x grid-margin-x acym__campaigns__attach__elements" id="acym__campaigns__attach__'.$i.'">';
                    $result .= $uploadfileType->display('attachments', $i);
                    $result .= '<div class="cell medium-auto"></div><div class="cell medium-1 text-center "><i style="display: none;" id="attachments'.$i.'suppr" data-id="'.$i.'" class="fa fa-trash-o acym__color__red acym__campaigns__attach__remove"></i></div>';
                    $result .= '</div>';
                    echo $result;
                }
                ?>
			</div>
			<div class="cell margin-bottom-2 margin-top-1">
				<a href="javascript:void(0);" id="acym__campaigns__attach__add"><?php echo acym_translation('ACYM_ADD_ATTACHMENT'); ?></a>
                <?php echo acym_translation_sprintf('ACYM_MAX_UPLOAD', $data['maxupload']); ?>
			</div>
			<div class="cell grid-x text-center acym__campaign__email__save-button cell">
				<div class="cell medium-shrink medium-margin-bottom-0 margin-bottom-1 text-left">
                    <?php echo acym_backToListing("campaigns") ?>
				</div>
				<div class="cell medium-auto grid-x text-right">
					<div class="cell medium-auto"></div>
                    <?php if (empty($data['campaignID'])) { ?>
						<button data-task="save" data-step="recipients" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
						</button>
                    <?php } else { ?>
						<button data-task="save" data-step="listing" type="submit" class="cell button-secondary medium-shrink button medium-margin-bottom-0 margin-right-1 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_EXIT'); ?>
						</button>
						<button data-task="save" data-step="recipients" type="submit" class="cell medium-shrink button margin-bottom-0 acy_button_submit">
                            <?php echo acym_translation('ACYM_SAVE_CONTINUE'); ?><i class="fa fa-chevron-right"></i>
						</button>
                    <?php } ?>
				</div>
			</div>
		</div>
		<div class="cell medium-auto"></div>

	</div>

    <?php echo acym_formOptions(true, 'edit', 'editEmail'); ?>

    <?php echo $data['editor']->display();
    ?>
</form>


