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

/** @var SellaciousViewProduct $this */
JHtml::_('jquery.framework');
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip', '.hasTooltip');

JHtml::_('stylesheet', 'com_sellacious/fe.component.css', null, true);
// JHtml::_('stylesheet', 'com_sellacious/fe.view.product.query.css', null, true);
// JHtml::_('script', 'com_sellacious/fe.view.product.query.js', true, true);

$form = $this->form;
?>
<style>
	.form-horizontal .control-label.left {
		text-align: left;
	}
	.margin-center {
		margin-right: auto;
		margin-left: auto;
		float: left;
	}
</style>
<script>
	Joomla.submitbutton = function (task, form) {
		form = form || document.getElementById('adminForm');

		if (document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		} else {
			alert(Joomla.JText._('COM_SELLACIOUS_VALIDATION_FORM_FAILED'));
		}
	}
</script>
<form action="<?php echo JUri::getInstance()->toString(array('path', 'query', 'fragment')) ?>" method="post"
	id="queryForm" name="queryForm" class="form-horizontal">
	<div class="w100p">
		<div class="margin-top-10">
			<?php
			/** @var JForm $form */
			$fieldsets = $form->getFieldsets();

			foreach ($fieldsets as $fieldset)
			{
				$fields = $this->form->getFieldset($fieldset->name);

				if (count($fields))
				{
					?><fieldset><?php
					if (isset($fieldset->label))
					{
						echo JText::_($fieldset->label);
					}

					foreach ($fields as $field)
					{
						if ($field->hidden)
						{
							echo $field->input;
						}
						elseif ($field->label)
						{
							?>
							<div class="control-group">
								<div class="control-label left"><?php echo $field->label; ?></div>
								<div class="controls"><?php echo $field->input; ?></div>
							</div>
							<?php
						}
						elseif (strtolower($field->type) != 'fieldgroup')
						{
							?>
							<div class="control-group"><?php echo $field->input; ?></div>
							<?php
						}
					}
					?></fieldset><?php
				}
			}
			?>
			<div class="clearfix"></div>
			<button type="button" class="btn btn-small btn-primary pull-right"
				onclick="Joomla.submitbutton('product.submitQuery', this.form);"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_QUERY_SUBMIT'); ?>
			</button>
		</div>
	</div>

	<input type="hidden" name="p" value="<?php echo $this->state->get('product.code') ?>" />

	<input type="hidden" name="option" value="com_sellacious" />
	<input type="hidden" name="view" value="product" />
	<input type="hidden" name="layout" value="query" />
	<input type="hidden" name="tmpl" value="component" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
