<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm">
    <div id="acym_content" class="acym__language__modal popup_size">
        <div class="acym__language__modal__header cell grid-x">
            <h1 class="shrink acym__language__modal__title acym__color__blue"><?php echo acym_translation('ACYM_FILE').' : '.$data['file']->name; ?></h1>
            <div class="auto cell"></div>
            <button data-task="share" class="acy_button_submit button margin-right-1 button-secondary shrink cell acym__language__modal__header__share"><?php echo acym_translation('ACYM_SHARE_TRANSLATION'); ?></button>
            <button data-task="saveLanguage" class="acy_button_submit button shrink cell acym__language__modal__header__save"><?php echo acym_translation('ACYM_SAVE') ?></button>
        </div>
        <div class="cell grid-x acym__language__modal__existing acym__content">
            <div class="cell grid-x">
                <h6 class="small-shrink acym__language__modal__title acym__language__modal__existing__name-file"><?php echo acym_translation('ACYM_FILE').' : '.$data['file']->name; ?></h6>
                <?php if (!empty($data['showLatest'])) { ?>
                    <button data-task="latest" id="acym__button__load__latest__language" class="button small-shrink margin-left-1 acy_button_submit"> <?php echo acym_translation('ACYM_LOAD_LATEST_LANGUAGE'); ?> <i class="material-icons">file_download</i></button>
                <?php } ?>
                <a href="#customcontent" id="edit_translation" class="button"><?php echo acym_translation('ACYM_EDIT'); ?></a>
            </div>
            <textarea readonly rows="18" name="content" id="translation" class="acym__language__modal__existing__translation"><?php echo $data['file']->content; ?></textarea>
        </div>
        <div class="acym__content acym__language__modal__custom margin-top-2" id="customcontent">
            <h6 class="acym__language__modal__title"><?php echo acym_translation('ACYM_CUSTOM_TRANS'); ?></h6>
            <?php echo acym_translation('ACYM_CUSTOM_TRANS_DESC'); ?>
            <textarea rows="10" name="customcontent" class="acym__language__modal__body"><?php echo $data['file']->customcontent; ?></textarea>

            <div class="cell grid-x">
                <button id="copy_translations" class="button"><?php echo acym_translation('ACYM_COPY_DEFAULT_TRANSLATIONS'); ?></button>
            </div>
        </div>
        <div class="clr"></div>
        <input type="hidden" name="code" value="<?php echo $data['file']->name; ?>"/>
        <?php acym_formOptions(); ?>
    </div>
</form>
