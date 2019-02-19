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

/** @var  array  $displayData */
$field  = (object) $displayData;
$medias = $field->value ? (array) $field->value : array();
?>
<div class="jff-eproductmedia-wrapper" id="<?php echo $field->id ?>_wrapper">
	<?php
		$layoutFile = 'com_sellacious.formfield.eproductmedia.inactive';
		$options    = array('client' => 2, 'debug' => 0);

		echo JLayoutHelper::render($layoutFile, array(), '', $options);
	?>
	<div class="messages-container"></div>
	<div class="jff-eproductmedia-active hidden">
		<input type="file" class="hidden"/>
		<table class="table table-bordered">
			<thead>
				<tr>
					<td colspan="10">
						<a class="btn btn-xs btn-success jff-eproductmedia-addrow pull-left"><i
							class="fa fa-plus fa-lg"></i> <?php echo JText::_('COM_SELLACIOUS_ADD_FILES_MORE'); ?></a>
					</td>
				</tr>
				<tr>
					<th class="center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_FILE_MEDIA') ?></th>
					<th class="center"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_FILE_SAMPLE') ?></th>
					<th class="center" style="width: 150px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_TAGS') ?></th>
					<th class="center" style="width:  80px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_VERSION') ?></th>
					<th class="center" style="width: 150px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_RELEASED') ?></th>
					<th class="center" style="width:  60px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_IS_LATEST') ?></th>
					<th class="center" style="width:  60px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_STATE') ?></th>
					<th class="center" style="width:  60px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_HOTLINK') ?></th>
					<th class="center" style="width:  30px;"><?php echo JText::_('COM_SELLACIOUS_PRODUCT_EPRODUCT_DOWNLOADS') ?></th>
					<th class="center" style="width:  30px;"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			foreach ($medias as $media)
			{
				$layoutFile = 'com_sellacious.formfield.eproductmedia.rowtemplate';
				$options    = array('client' => 2, 'debug' => 0);

				$args        = new stdClass;
				$args->field = $field;
				$args->media = (object) $media;

				echo JLayoutHelper::render($layoutFile, $args, '', $options);
			}
			?>
			</tbody>
			<tfoot class="hidden jff-eproductmedia-rowtemplate">
				<?php
				$layoutFile = 'com_sellacious.formfield.eproductmedia.rowtemplate';
				$options    = array('client' => 2, 'debug' => 0);

				$media = array(
					'id'         => '#ID#',
					'product_id' => '#PRODUCT_ID#',
					'tags'       => '#TAGS#',
					'version'    => '#VERSION#',
					'released'   => '#RELEASED#',
					'is_latest'  => '#IS_LATEST#',
					'hotlink'    => '0',
					'state'      => '#STATE#',
				);

				$args        = new stdClass;
				$args->field = $field;
				$args->media = (object) $media;

				echo JLayoutHelper::render($layoutFile, $args, '', $options);
				?>
			</tfoot>
		</table>
	</div>
</div>
