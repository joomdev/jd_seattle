<?php
/**
* @package    mod_jdprocess
* @author     JoomDev https://www.joomdev.com
* @copyright  Copyright (C) 2019 Joomdev, Inc. All rights reserved.
* @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
*/

// no direct access
defined('_JEXEC') or die;
$items = $params->get('items', []);
?>

<div id="jdprocess" class="jdprocess">
	<div class="row jdprocess-boxs">
		<?php $i=1; foreach($items as $item) {?>
		<div class="col-12 col-md-3 jdprocess-box">
			<div class="process-wrapper">
				<span class="pro-icon" title="<?php echo  $i; ?>">
					<i class="<?php echo $item->icon; ?>"></i>
				</span>
				<h6 class="process-heading">
					<?php echo $item->title; ?>
				</h6>
				<?php echo !empty($item->short_description) ? '<p class="process-short-description">'.$item->short_description.'</p>' : "" ?>
				<i class="process-icon fas fa-long-arrow-alt-right d-none d-md-block"></i>
			</div>
		</div>
		<?php $i++; } ?>
	</div>
</div>