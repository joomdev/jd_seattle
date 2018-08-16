<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	\GApp::document()->addJsFile(\G2\Globals::get('FRONT_URL').'admin/themes/default/views/admin/assets/designer.js');
	\GApp::document()->addCssFile(\G2\Globals::get('FRONT_URL').'admin/themes/default/views/admin/assets/designer.css');
?>
<div class="ui form">
fffff
</div>
<div class="ui form">
	<?php foreach($fields as $field): ?>
		<?php if(!empty($field['type']) AND empty($field['parent_id'])): ?>
			<?php $this->view('views.forms.field', ['field' => $field, 'fields' => $fields]); ?>
		<?php endif; ?>
	<?php endforeach; ?>
</div>