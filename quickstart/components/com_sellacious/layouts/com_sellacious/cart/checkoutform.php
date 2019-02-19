<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
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
