<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><form id="acym_form" action="<?php echo acym_completeLink('dashboard'); ?>" method="post" name="acyForm" data-abide novalidate>
    <div id="acym_walk_through">
        <?php

        $file = ACYM_VIEW.'dashboard'.DS.'tmpl'.DS.'walk_through_'.$data['step'].'.php';

        include($file);
        ?>
    </div>
    <?php echo acym_formOptions(true, '', null, 'dashboard'); ?>
</form>

