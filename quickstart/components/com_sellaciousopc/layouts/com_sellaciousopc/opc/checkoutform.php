<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access
defined('_JEXEC') or die;

/** @var   stdClass  $displayData */
/** @var   JForm     $form */
$form = $displayData->form;
?>
<fieldset class="form-horizontal checkoutform w100p">
	<?php foreach ($form->getFieldset() as $field): ?>
		<div class="control-group">
			<?php if ($field->label): ?>
				<div class="control-label"><?php echo $field->label ?></div>
				<div class="controls"><?php echo $field->input ?></div>
			<?php else: ?>
				<?php echo $field->input ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</fieldset>
