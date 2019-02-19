<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array $displayData */
$data = $displayData;

/** @var JForm $form */
$form = $data['form'];

foreach ($form->getFieldset() as $field)
{
	/** @var  JFormField  $field */
	if (strtolower($field->type) == 'note')
	{
		?>
		<tr>
			<td class="v-top" colspan="2"><?php echo $field->label ?></td>
		</tr>
		<?php
	}
	else
	{
		$form->setFieldAttribute($field->fieldname, 'required', false, $field->group);
		?>
		<tr>
			<td class="v-top"><?php echo $field->label ?></td>
			<td><?php echo $field->input; ?></td>
		</tr>
		<?php
	}
}
