<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui grid equal width stackable">
	<?php if(!empty($field['params']['columns'])): ?>
		<?php $subfields = \G2\L\Arr::searchVal($fields, ['[n]', 'parent_id'], [$field['id']]); ?>
		<?php $range = range(0, $field['params']['columns'] - 1); ?>
		<?php foreach($range as $i): ?>
			<div class="column">
			<?php foreach($subfields as $subfield): ?>
				<?php if(($subfield['parent_id'] == $field['id']) AND ($subfield['parent_sub_id'] == $i)): ?>
					<?php $this->view('views.forms.field', ['field' => $subfield, 'fields' => $fields]); ?>
				<?php endif; ?>
			<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	<?php endif; ?>
</div>