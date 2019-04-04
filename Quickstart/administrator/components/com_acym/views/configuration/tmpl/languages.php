<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__content acym_area padding-vertical-1 padding-horizontal-2" id="acym__configuration__languages">
    <div class="acym_area_title"><?php echo acym_translation('ACYM_CONFIGURATION_LANGUAGES'); ?></div>
    <div class="acym__listing margin-top-2">
        <div class="grid-x cell acym__configuration__languages__listing acym__listing__header">
            <div class="grid-x medium-auto small-11 cell">
                <div class="medium-1 small-1 cell acym__listing__header__title">
                    <?php echo acym_translation('ACYM_EDIT'); ?>
                </div>
                <div class="medium-auto small-3 cell text-left acym__listing__header__title">
                    <?php echo acym_translation('ACYM_NAME'); ?>
                </div>
                <div class="medium-2 small-2 text-center cell acym__listing__header__title">
                    <?php echo acym_translation('ACYM_ID'); ?>
                </div>
            </div>
        </div>
        <?php foreach ($data['languages'] as $oneLanguage) { ?>
            <div class="grid-x cell acym__listing__row">
                <div class="medium-1 small-1 cell acym__listing__text">
                    <?php echo $oneLanguage->edit; ?>
                </div>
                <div class="medium-auto small-auto cell acym__listing__text">
                    <?php echo $oneLanguage->name; ?>
                </div>
                <div class="medium-2 small-2 cell text-center acym__listing__text">
                    <?php echo $oneLanguage->language; ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
