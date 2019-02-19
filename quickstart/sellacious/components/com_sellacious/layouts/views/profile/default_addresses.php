<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

JHtml::_('script', 'media/com_sellacious/js/plugin/serialize-object/jquery.serialize-object.min.js', array('version' => S_VERSION_CORE));

/** @var  SellaciousViewUser $this */
$fields = $this->form->getFieldset('addresses');

$me = JFactory::getUser();
?>
<script>
	jQuery(document).ready(function () {
		var o = new SellaciousViewUser.Address;
		o.init('#tab-addresses', '<?php echo JSession::getFormToken() ?>', 'profile');
	});
</script>
<div class="tab-pane fade" id="tab-addresses">
	<fieldset>
		<div class="pull-right padding-bottom-10">
			<button type="button" class="btn btn-xs btn-success" id="btn-apply-address"><i
					class="fa fa-save"></i> Save</button>
			<button type="button" class="btn btn-xs btn-primary" id="btn-save-address"><i
					class="fa fa-check"></i> Save &amp; Close</button>
			<button type="button" class="btn btn-xs btn-danger" id="btn-close-address"><i
					class="fa fa-times"></i> Close &amp; Discard</button>
		</div>
		<div class="clearfix"></div>
		<div id="address-fields">
			<?php
			foreach ($fields as $field)
			{
				if ($field->hidden):
					echo $field->input;
				else:
					?>
					<div class="row <?php echo $field->label ? 'input-row' : '' ?>">
						<?php
						if ($field->label && (!isset($fieldset->width) || $fieldset->width < 12))
						{
							echo '<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
							echo '<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
						}
						else
						{
							echo '<div class="controls col-md-12">' . $field->input . '</div>';
						}
						?>
					</div>
					<?php
				endif;
			}
			?>
		</div>
	</fieldset>
	<div class="clearfix"></div>
	<div class="pull-right margin-bottom-10 margin-top-10">
		<button type="button" class="btn btn-xs btn-success edit-address" id="add-address"
			data-id="0"><i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_SELLACIOUS_PROFILE_ADD_ADDRESS'); ?>
		</button>
	</div>
	<table class="table table-bordered table-striped table-hover" id="addresses-list">
		<tbody>
		<?php
		$addresses = $this->item->get('addresses');

		if (is_array($addresses))
		{
			foreach ($addresses as $i => $address)
			{
				$html = JLayoutHelper::render('com_sellacious.user.address.row', $address);

				echo preg_replace(array('|[\n\t]|', '|\s+|'), array('', ' '), $html);
			}
		}
		?>
		</tbody>
	</table>
</div>
