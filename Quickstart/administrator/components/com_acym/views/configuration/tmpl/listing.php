<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink(acym_getVar('cmd', 'ctrl')); ?>" method="post" name="acyForm" novalidate data-abide>
    <div class="grid-x acym__content acym__content__tab">

        <?php
        $data['tab']->content[] = '
        <div class="cell grid-x align-right">
            <button type="submit" data-task="test" class="cell medium-shrink button margin-1 acy_button_submit button-secondary">
                '.acym_translation('ACYM_SEND_TEST').'
            </button>
            <button type="submit" data-task="save" class="cell medium-shrink button margin-1 acy_button_submit">
                '.acym_translation('ACYM_SAVE').'
            </button>
        </div>';

        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_MAIL'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'mail.php');
        echo '</div>';
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_QUEUE'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'queue.php');
        echo '</div>';
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_SUBSCRIPTION'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'subscription.php');
        echo '</div>';
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_DATA_COLLECTION'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'data.php');
        echo '</div>';
        $data['tab']->endTab();


        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_SECURITY'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'security.php');
        echo '</div>';
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_CONFIGURATION_LANGUAGES'));
        echo '<div class="acym__configuration__content">';
        include(dirname(__FILE__).DS.'languages.php');
        echo '</div>';
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_BOUNCE_HANDLING'));
        include(dirname(__FILE__).DS.'bounce.php');
        $data['tab']->endTab();

        $data['tab']->display('configuration');
        ?>

        <div class="cell grid-x align-right">
            <button type="submit" data-task="save" class="cell margin-1 shrink button acy_button_submit">
                <?php echo acym_translation('ACYM_SAVE'); ?>
            </button>
        </div>
    </div>


    <?php acym_formOptions(); ?>
</form>
