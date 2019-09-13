<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><h2 class="cell acym__walkthrough__title margin-bottom-2"><?php echo acym_translation('ACYM_WALKTHROUGH_HOUSTON'); ?></h2>

<div class="cell margin-top-2 margin-bottom-2">
	<i class="fa fa-ambulance" style="font-size: 8rem;"></i>
</div>

<div class="cell margin-top-2 margin-bottom-3">
	<p class="acym__walkthrough__text">
        <?php echo acym_translation('ACYM_CONTACT_WELL_CONTACT_YOU'); ?><br />
        <?php echo acym_translation('ACYM_CONTACT_NEEDED_INFO'); ?><br /> <br />
        <?php echo acym_translation_sprintf('ACYM_CONTACT_DIRECT', '<a class="acym__color__blue" href="'.ACYM_ACYWEBSITE.'contact-us.html" target="_blank">'.acym_translation('ACYM_GET_IN_TOUCH').'</a>'); ?>
	</p>
</div>

<div class="cell margin-top-3">
	<button type="button" class="acy_button_submit button" data-task="saveStepSupport"><?php echo acym_translation('ACYM_CONTINUE'); ?></button>
</div>

