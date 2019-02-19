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
$file     = (object)$displayData;
$helper   = SellaciousHelper::getInstance();
$tip      = strtoupper(JFile::getExt($file->path));
$url      = $helper->media->getURL($file->path, false);
$class404 = ($url == '') ? 'jff-document-404' : '';
$is_image = $helper->media->isImage($file->path);
?>
<li data-id="<?php echo $file->id ?>" class="hasTooltip" title="<?php echo $tip ?>"
	data-placement="right" data-html="true"><?php

	if ($file->state == 1)
	{
		?><a href="#" onclick="return false;" class="jff-document-disable hasTooltip" title="Hide"
			 data-placement="left"><i class="fa fa-eye txt-color-red state-btn"></i></a><?php
	}
	else
	{
		?><a href="#" onclick="return false;" class="jff-document-enable hasTooltip" title="Unhide"
			 data-placement="left"><i class="fa fa-eye-slash txt-color-red state-btn"></i></a><?php
	}

	// Preload if it is an image
	$preview  = $is_image ? '<img class="jff-document-preview" src="' . $url . '">' : '';
	$doc_type = $file->doc_type ? sprintf('<em>%s: </em>', $file->doc_type) : '';
	$doc_ref  = $file->doc_reference ? $file->doc_reference : '';
	$title    = $doc_type . $doc_ref;
	?><a href="#" onclick="return false;" class="jff-document-download hasTooltip <?php echo $class404 ?>"><i
			class="fa fa-file-text"></i>&nbsp;<?php echo $preview . $title ?>&nbsp;</a>
	<a href="#" onclick="return false;" class="jff-document-remove hasTooltip" title="Remove" data-placement="right">
		<i class="fa fa-times-circle txt-color-red remove-btn"></i></a>
</li>
