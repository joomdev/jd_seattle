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

/** @var  object $displayData */
$field = $displayData->field;
$media = $displayData->media;
$helper = SellaciousHelper::getInstance();

$options    = array('client' => 2, 'debug' => 0);
$layoutFile = 'com_sellacious.formfield.eproductmedia.mediarow';
?>
<tr class="jff-eproductmedia-media" data-state="<?php echo $media->state ?>">
	<td class="jff-eproductmedia-media-file" data-id="<?php echo $media->id ?>" data-context="media">
		<div><a class="btn btn-xs btn-primary jff-eproductmedia-add pull-left"><i
				class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_EPRODUCTMEDIA_UPLOAD_PRODUCT_FULL') ?></a></div>
		<ul class="list-unstyled jff-eproductmedia-list pull-left">
			<?php if (isset($media->media)): ?>
			<?php echo JLayoutHelper::render($layoutFile, $media->media, '', $options); ?>
			<?php endif; ?>
		</ul>
	</td>
	<td class="jff-eproductmedia-media-file" data-id="<?php echo $media->id ?>" data-context="sample">
		<div>
			<a class="btn btn-xs btn-primary jff-eproductmedia-add pull-left"><i
				class="fa fa-upload"></i>&nbsp;<?php echo JText::_('COM_SELLACIOUS_EPRODUCTMEDIA_UPLOAD_PRODUCT_SAMPLE') ?></a>
		</div>
		<ul class="list-unstyled jff-eproductmedia-list pull-left">
			<?php if (isset($media->sample)): ?>
			<?php echo JLayoutHelper::render($layoutFile, $media->sample, '', $options); ?>
			<?php endif; ?>
		</ul>
	</td>
	<td>
		<input type="hidden" name="<?php echo $field->name ?>[<?php echo $media->id ?>][tags]" class="select2-tags w100p"
		       value="<?php echo htmlspecialchars($media->tags, ENT_COMPAT, 'UTF-8'); ?>" data-role="tagsinput" title="">
	</td>
	<td>
		<input type="text" name="<?php echo $field->name ?>[<?php echo $media->id ?>][version]" class="inputbox"
		       value="<?php echo htmlspecialchars($media->version, ENT_COMPAT, 'UTF-8'); ?>" title="">
	</td>
	<td>
		<input type="text" name="<?php echo $field->name ?>[<?php echo $media->id ?>][released]" class="inputbox"
		       value="<?php echo htmlspecialchars($media->released, ENT_COMPAT, 'UTF-8') ?>" title="">
	</td>
	<td class="center">
		<span class="onoffswitch">
			<input type="checkbox" name="<?php echo $field->name ?>[<?php echo $media->id ?>][is_latest]"
			       id="<?php echo $field->name . '_' . $media->id ?>_is_latest" class="onoffswitch-checkbox"
			       value="1" <?php echo $media->is_latest ? 'checked' : '' ?> title="">
			<label class="onoffswitch-label" for="<?php echo $field->name . '_' . $media->id ?>_is_latest">
			<span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span>
			<span class="onoffswitch-switch"></span> </label>
		</span>
	</td>
	<td class="center">
		<span class="onoffswitch">
			<input type="checkbox" name="<?php echo $field->name ?>[<?php echo $media->id ?>][state]"
			       id="<?php echo $field->name . '_' . $media->id ?>_state" class="onoffswitch-checkbox"
			       value="1" <?php echo $media->state ? 'checked' : '' ?> title="">
			<label class="onoffswitch-label" for="<?php echo $field->name . '_' . $media->id ?>_state">
			<span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span>
			<span class="onoffswitch-switch"></span> </label>
		</span>
	</td>
	<td class="center">
		<span class="onoffswitch">
			<input type="checkbox" name="<?php echo $field->name ?>[<?php echo $media->id ?>][hotlink]"
			       id="<?php echo $field->name . '_' . $media->id ?>_hotlink" class="onoffswitch-checkbox"
			       value="1" <?php echo $media->hotlink ? 'checked' : '' ?> title="">
			<label class="onoffswitch-label" for="<?php echo $field->name . '_' . $media->id ?>_hotlink">
			<span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span>
			<span class="onoffswitch-switch"></span> </label>
		</span>
	</td>
	<td class="center">
		<?php
		$cFilter = array(
			'list.select' => 'SUM(a.dl_count)',
			'list.from'   => '#__sellacious_eproduct_downloads',
			'file_id'     => isset($media->media->id) ? $media->media->id : 0,
		);
		$count   = $helper->media->loadResult($cFilter);
		echo (int) $count ?>
	</td>
	<td>
		<input type="hidden" name="<?php echo $field->name ?>[<?php echo $media->id ?>][id]" value="<?php echo $media->id ?>">
		<button type="button" class="btn btn-xs bg-color-red txt-color-white jff-eproductmedia-removerow"><i
				class="fa fa-lg fa-times"></i> </button>
	</td>
</tr>
