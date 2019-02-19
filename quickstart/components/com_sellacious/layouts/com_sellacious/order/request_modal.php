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

/**
 * Layout for return and exchange modal views. This renders the form object with a submit button with given task/label
 *
 * @usage  com_sellacious.order.request_modal
 */

/** @var object $displayData */
$task  = $displayData->task;
$label = $displayData->label;

/** @var JForm $form */
$form      = $displayData->form;
$fieldsets = $form->getFieldsets();
?>
<form action="index.php" method="post" class="form-validate form-vertical">
	<?php
	foreach ($fieldsets as $fs_key => $fieldset)
	{
		$fields = $form->getFieldset($fieldset->name);
		?>
		<h4><?php echo JText::_($fieldset->label, true) ?></h4>
		<p><?php echo JText::_($fieldset->description, true) ?></p>
		<table class="w100p">
			<tbody>
			<?php
			foreach ($fields as $field)
			{
				if ($field->hidden)
				{
					echo $field->input;
				}
				else
				{
					?>
					<tr><td><?php echo $field->label; ?></td></tr>
					<tr><td><?php echo $field->input; ?></td></tr>
					<?php
				}
			}
			?>
			</tbody>
			<tfoot>
			<tr>
				<td class="text-right">
					<hr class="simple">
					<input type="hidden" name="option" value="com_sellacious"/>
					<input type="hidden" name="task" value=""/>
					<?php echo JHtml::_('form.token'); ?>

					<button type="button" class="btn btn-primary"
						onclick="Joomla.submitbutton('<?php echo $task ?>', this.form);"><?php echo $label; ?></button>
				</td>
			</tr>
			</tfoot>
		</table>
		<?php
	}
	?>
</form>
