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

/** @var  object $displayData */
$file     = (object)$displayData;
$helper   = SellaciousHelper::getInstance();
$tip      = strtoupper(JFile::getExt($file->path));
$url      = $helper->media->getURL($file->path, false);
$class404 = ($url == '') ? 'jff-fileplus-404' : '';
$is_image = $helper->media->isImage($file->path);
?>
<li data-id="<?php echo $file->id ?>" class="hasTooltip" title="<?php echo $tip ?>"
	data-placement="right" data-html="true"><?php

	if ($file->state == 1)
	{
		?><a href="#" onclick="return false;" class="jff-fileplus-disable hasTooltip" title="Hide"
			 data-placement="left"><i class="fa fa-eye txt-color-red state-btn"></i></a><?php
	}
	else
	{
		?><a href="#" onclick="return false;" class="jff-fileplus-enable hasTooltip" title="Unhide"
			 data-placement="left"><i class="fa fa-eye-slash txt-color-red state-btn"></i></a><?php
	}

	// Preload if it is an image
	$preview = $is_image ? '<img class="jff-fileplus-preview" src="' . $url . '">' : '';
	$title   = !empty($file->original_name) ? $file->original_name : basename($file->path);

	?><a href="#" onclick="return false;" class="jff-fileplus-download hasTooltip <?php echo $class404 ?>"><i
			class="fa fa-file-text"></i>&nbsp;<?php echo $preview . $title ?>&nbsp;</a>
	<a href="#" onclick="return false;" class="jff-fileplus-remove hasTooltip" title="Remove" data-placement="right">
		<i class="fa fa-times-circle txt-color-red remove-btn"></i></a>
</li>
