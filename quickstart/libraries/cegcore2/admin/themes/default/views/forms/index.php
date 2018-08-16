<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php \GApp::document()->_('g2.validation'); ?>

<form class="ui form ce_form" method="post" action="<?php echo (!empty($url) ? $url : \G2\L\Url::current()); ?>">
	<?php foreach($fields as $field): ?>
		<?php if(!empty($field['type']) AND empty($field['parent_id'])): ?>
			<?php $this->view('views.forms.field', ['field' => $field, 'fields' => $fields]); ?>
		<?php endif; ?>
	<?php endforeach; ?>
</form>