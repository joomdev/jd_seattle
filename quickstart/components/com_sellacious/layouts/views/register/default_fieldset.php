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

/** @var  \stdClass  $tplData */
$fieldset = $tplData;

/** @var SellaciousViewUser $this */
$fields = $this->form->getFieldset($fieldset->name);

if (array_filter($fields, function ($field) { return !$field->hidden; })):
	echo JHtml::_('bootstrap.addSlide', 'profile_accordion', JText::_($fieldset->label), 'profile_accordion_' . $fieldset->name, 'accordion'); ?>
	<fieldset class="w100p">
		<?php
		foreach ($fields as $field):
			if ($field->hidden):
				echo $field->input;
			else:
				?>
				<div class="control-group">
					<?php if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12)): ?>
						<div class="control-label"><?php echo $field->label ?></div>
						<div class="controls"><?php echo $field->input ?></div>
					<?php else: ?>
						<div class="controls col-md-12"><?php echo $field->input ?></div>
					<?php endif; ?>
				</div>
			<?php
			endif;
		endforeach;
		?>
	</fieldset>
	<div class="clearfix"></div><?php
	echo JHtml::_('bootstrap.endSlide');
else:
	foreach ($fields as $field):
		echo $field->input;
	endforeach;
endif;
