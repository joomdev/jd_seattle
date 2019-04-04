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
	<div class="acym__content acym__content__tab" id="acym_stats">
        <?php

        $data['tab']->startTab(acym_translation('ACYM_GLOBAL_STATISTICS'));
        include(dirname(__FILE__).DS.'global_stats.php');
        $data['tab']->endTab();

        $data['tab']->startTab(acym_translation('ACYM_DETAILED_STATS'));
        if (acym_level(1)) {
            include(dirname(__FILE__).DS.'detailed_stats.php');
        }

        if (acym_level(0) && !acym_level(1)) {
            $data['version'] = 'essential';
            include(ACYM_VIEW.'dashboard'.DS.'tmpl'.DS.'upgrade.php');
        }
        $data['tab']->endTab();

        $data['tab']->display('stats');
        ?>
	</div>
</form>
