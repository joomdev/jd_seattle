<?php
defined('_JEXEC') or die('Restricted access');
?><div class="grid-x text-center">
	<h1 class="acym__listing__empty__title cell"><?php echo acym_translation_sprintf('ACYM_YOU_DONT_HAVE_ANY_X', $data['element_to_display']); ?></h1>
	<h1 class="acym__listing__empty__subtitle cell"><?php echo acym_translation('ACYM_CREATE_ONE_NOW'); ?></h1>
	<div class="medium-4"></div>
	<div class="medium-4 cell">
		<button data-task="newEmail" type="button" class="button expanded acy_button_submit"><?php echo acym_translation('ACYM_CREATE_NEW_EMAIL'); ?></button>
	</div>
	<div class="medium-4"></div>
</div>

