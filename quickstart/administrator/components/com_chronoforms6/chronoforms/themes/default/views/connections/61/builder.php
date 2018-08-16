<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui grid">
	<div class="four wide column">
		<div class="ui segment blue">
			
		</div>
	</div>
	
	<div class="twelve wide column">
		<div class="ui segment black">
			<?php foreach($this->data('Connection.views', []) as $view_n => $view): ?>
				<?php pr($view); ?>
			<?php endforeach; ?>
		</div>
	</div>
</div>