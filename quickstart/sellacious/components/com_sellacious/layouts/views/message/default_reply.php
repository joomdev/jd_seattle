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

/** @var  SellaciousViewMessage $this */
?>
<div class="message-toolbar">
	<span class="title"><i class="fa fa-pencil-square-o"></i>
		<?php echo JText::_('COM_SELLACIOUS_MESSAGE_COMPOSE_REPLY_TITLE', true); ?></span>
	<button type="button" onclick="Joomla.submitbutton('message.save', this.form);"
		class="btn btn-primary"><i class="fa fa-location-arrow"></i> Send</button>
	<button type="reset" class="btn btn-danger"><i class="fa fa-trash-o"></i> Discard</button>
	<div class="clearfix"></div>
</div>

<?php
$fieldsets = $this->form->getFieldsets();

foreach ($fieldsets as $fs_key => $fieldset)
{
	$visible = array();
	$fields  = $this->form->getFieldset($fieldset->name);

	// echo hidden input right away, and count how many we have for the box
	foreach ($fields as $field)
	{
		if ($field->hidden)
		{
			echo $field->input;
		}
		else
		{
			$visible[] = $field;
		}
	}

	// If there are only hidden fields then skip box structure.
	if (count($visible))
	{
		?>
		<!-- Widget ID (each widget will need unique ID)-->
		<div class="jarviswidget" id="wid-id-<?php echo $fs_key ?>" style="margin: 0; padding-top: 0;">
			<!-- widget content -->
			<div class="widget-body">
				<fieldset>
					<?php
					foreach ($visible as $field)
					{
						$input = $field->input;
						$label = ($fieldset->name == 'basic' && $field->fieldname == 'body') ? '' : $field->label;
						?>
						<div class="row">
							<?php
							if ($label == '' || (isset($fieldset->width) && $fieldset->width == 12))
							{
								echo '<div class="controls col-md-12">' . $input . '</div>';
							}
							else
							{
								echo '<div class="form-label col-sm-4 col-md-4 col-lg-3">' . $label . '</div>';
								echo '<div class="controls col-sm-8 col-md-8 col-lg-9">' . $input . '</div>';
							}
							?>
						</div>
						<div class="clearfix"></div>
						<?php
					}
					?>
				</fieldset>
			</div>
			<!-- end widget div -->
		</div>
		<!-- end widget -->
		<?php
	}
}
