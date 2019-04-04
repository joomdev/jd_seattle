<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><input type="hidden" name="step" value="4">
<input type="hidden" name="mailer_method" value="phpmail" id="acym__walk-through-3__way-mail">
<div id="acym_walk-through_3" class="cell grid-x">
    <div class="cell large-3"></div>
    <div class="cell grid-x large-6 small-12 acym__walk-through-3__content">
        <a class="acym__walk_through__back" href="<?php echo acym_completeLink('dashboard&task=walkThrough&step=2'); ?>"><i class="fa fa-chevron-left"></i><span><?php echo acym_translation('ACYM_BACK'); ?></span></a>
        <div class="cell grid-x acym__content cell text-center">
            <h1 class="acym__walk-through__content__title cell"><?php echo acym_translation('ACYM_SERVER_CONFIGURATION'); ?></h1>
            <p class="acym__walk-through__step cell"><?php echo acym_translation_sprintf('ACYM_STEP_X', 3, 3); ?></p>
            <div class="acym__walk-through-3__switch cell grid-x"><?php echo acym_switch('config[special_char]', $data['special_chars'], acym_translation('ACYM_SPECIAL_CHARS')); ?></div>
            <label for="acym__walk-through-3__config__encoding" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_CONFIGURATION_ENCODING'); ?></label>
            <?php
            $encodingHelper = acym_get('helper.encoding');
            echo $encodingHelper->encodingField('config[encoding_format]', $data['encoding_format'], 'class="acym__walk-through__select" id="acym__walk-through-3__config__encoding"');
            ?>
            <label for="acym__walk-through-2__config__charset" class="cell text-left acym__walk-through__content__label"><?php echo acym_translation('ACYM_CONFIGURATION_CHARSET'); ?></label>
            <?php
            echo $encodingHelper->charsetField('config[charset]', $data['charset'], 'class="acym__walk-through__select" id="acym__walk-through-3__config__charset"');
            ?>
            <div class="acym__walk-through-3__switch cell grid-x"><?php echo acym_switch('config[https]', $data['use_https'], acym_translation('ACYM_CONFIGURATION_HTTPS')); ?></div>
            <div class="acym__walk-through-3__switch cell grid-x"><?php echo acym_switch('config[images]', $data['embed_images'], acym_translation('ACYM_CONFIGURATION_EMBED_IMAGES')); ?></div>
            <div class="acym__walk-through-3__switch cell grid-x"><?php echo acym_switch('config[attachments]', $data['embed_files'], acym_translation('ACYM_CONFIGURATION_EMBED_ATTACHMENTS')); ?></div>
            <div class="cell text-center">
                <div class="large-auto"></div>
                <button data-task="step3" class="button acy_button_submit acym__walk-through__content__save large-shrink"><?php echo acym_translation('ACYM_SAVE_FINISH'); ?></button>
                <div class="large-auto"></div>
            </div>
        </div>
    </div>
</div>
