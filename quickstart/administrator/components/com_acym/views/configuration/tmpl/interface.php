<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><div class="acym__content acym_area padding-vertical-1 padding-horizontal-2 margin-bottom-2">
	<div class="acym_area_title"><?php echo acym_translation('ACYM_LISTING'); ?></div>
	<div class="grid-x grid-margin-x">
        <?php echo acym_switch('config[small_display]', $data['config']->get('small_display'), acym_translation('ACYM_COMPACT_DISPLAY'), [], 'xlarge-3 medium-5 small-9', "auto", "tiny"); ?>
	</div>
</div>

