<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php $this->view('views.common.validationalert', ['ext' => 'chronoforms', 'name' => 'ChronoForms', 'msg' => '15 displayed fields per form limit, and a credits link at the bottom of the form']); ?>
<?php
	$this->view('views.common.admin_menu', ['etitle' => 'ChronoForms6', 'menuitems' => [
		['cont' => 'connections', 'title' => rl('Forms')],
		['cont' => 'blocks', 'title' => rl('Blocks')],
		['act' => 'install_feature', 'title' => rl('Install feature'), 'hidden' => true],
		['act' => 'info', 'title' => rl('Shortcodes')],
	]]);
?>

<div class="ui segment fluid container">
	{VIEW}
</div>