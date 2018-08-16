<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="field delement">
	<div class="ui label tools_menu">
		<i class="sort icon yellow link"></i>
		Edit
		<i class="delete icon red"></i>
	</div>
	<?php
		$this->view(
			\G2\Globals::get('ADMIN_PATH').'themes'.DS.'default'.DS.'views'.DS.'forms'.DS.'field.php', 
			['field' => $field, 'fields' => $fields]
		);
	?>
	<div class="delement_config">Config here</div>
</div>