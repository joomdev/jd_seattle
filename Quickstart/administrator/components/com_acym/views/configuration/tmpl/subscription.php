<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__configuration__subscription acym__content acym_area padding-vertical-1 padding-horizontal-2">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_SUBSCRIPTION'); ?></div>
    <div class="grid-x">
        <div class="cell grid-x grid-margin-x">
            <?php echo acym_switch('config[allow_visitor]', $data['config']->get('allow_visitor'), acym_translation('ACYM_ALLOW_VISITOR'), array(), 'xlarge-3 medium-5 small-9', "auto", "tiny", 'visitor_config'); ?>
        </div>
        <div class="cell grid-x grid-margin-x">
            <?php echo acym_switch('config[generate_name]', $data['config']->get('generate_name'), acym_translation('ACYM_GENERATE_NAME'), array(), 'xlarge-3 medium-5 small-9', "auto", "tiny", 'generate_config'); ?>
        </div>
        <div class="cell grid-x grid-margin-x">
            <?php echo acym_switch('config[require_confirmation]', $data['config']->get('require_confirmation'), acym_translation('ACYM_REQUIRE_CONFIRMATION'), array(), 'xlarge-3 medium-5 small-9', "auto", "tiny", 'confirm_config'); ?>
        </div>
        <div class="cell grid-x" id="confirm_config">
            <div class="cell grid-x">
                <div class="cell xlarge-3 medium-5"></div>
                <div class="cell medium-auto">
                    <a class="smaller-button button" href="<?php echo acym_completeLink('mails&task=edit&notification=acy_confirm&type_editor=acyEditor'); ?>"><?php echo acym_translation('ACYM_EDIT_EMAIL'); ?></a>
                </div>
            </div>
            <label for="confirm_redirect" class="cell grid-x">
                <span class="xlarge-3 medium-5"><?php echo acym_translation('ACYM_CONFIRMATION_REDIRECTION'); ?></span>
                <input id="confirm_redirect" class="cell xlarge-4 medium-auto" type="text" name="config[confirm_redirect]" value="<?php echo $data['config']->get('confirm_redirect'); ?>">
                <div class="large-auto hide-for-large-only hide-for-medium-only"></div>
            </label>
        </div>
        <div class="cell medium-3"><?php echo acym_translation('ACYM_ALLOW_MODIFICATION'); ?></div>
        <div class="cell medium-9">
            <?php
            $allowModif = array(
                'none' => acym_translation('ACYM_NO'),
                'data' => acym_translation('ACYM_ALLOW_ONLY_THEIRS'),
                'all' => acym_translation('ACYM_YES'),
            );
            echo acym_radio($allowModif, 'config[allow_modif]', $data['config']->get('allow_modif', 'data'));
            ?>
        </div>
    </div>
</div>
