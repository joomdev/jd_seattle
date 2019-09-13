<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.8
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><input type="hidden" name="step" value="5">
<div id="acym_walk-through_4" class="cell grid-x">
	<div class="cell large-3"></div>
	<div class="cell grid-x large-6 small-12 acym__walk-through-4__content">
		<a class="acym__walk_through__back" href="<?php echo acym_completeLink('dashboard&task=walkThrough&step=3'); ?>">
			<i class="fa fa-chevron-left"></i>
			<span><?php echo acym_translation('ACYM_BACK'); ?></span>
		</a>
		<div class="cell grid-x acym__content cell text-center">
			<h1 class="acym__walk-through__content__title cell"><?php echo acym_translation('ACYM_INTERFACE'); ?></h1>
			<p class="acym__walk-through__step cell"><?php echo acym_translation_sprintf('ACYM_STEP_X', 4, 4); ?></p>

			<div class="acym__walk-through-4__switch cell grid-x"><?php echo acym_switch('interface[small_display]', $data['small_display'], acym_translation('ACYM_COMPACT_DISPLAY')); ?></div>
            <?php
            ?>
			<div class="cell text-center">
				<div class="large-auto"></div>
				<button data-task="step4" class="button acy_button_submit acym__walk-through__content__save large-shrink"><?php echo acym_translation('ACYM_SAVE_FINISH'); ?></button>
				<div class="large-auto"></div>
			</div>
		</div>
	</div>
</div>
