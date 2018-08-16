<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui message <?php echo (!empty($block['params']['color']) ? $block['params']['color'] : ''); ?>">
	<?php echo $block['params']['content']; ?>
</div>