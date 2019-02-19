<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/** @var  stdClass  $displayData */
$field     = $displayData;
$options   = ArrayHelper::getColumn($field->options, 'text', 'value');
$selection = $field->value ? (is_array($field->value) ? $field->value : explode(',', $field->value)) : array();

JHtml::_('jquery.framework');
JHtml::_('script', 'sellacious/jquery-ui-sortable.min.js', array('relative' => true, 'version' => S_VERSION_CORE));
JHtml::_('stylesheet', 'sellacious/field.sortablelist.css', array('relative' => true, 'version' => S_VERSION_CORE));
?>
<div id="<?php echo $field->id ?>" class="jff-sortablelist-table table-bordered <?php echo $field->class ?>">
	<ul class="sortable-group">
		<?php foreach ($selection as $name): ?>
			<?php if (array_key_exists($name, $options)): ?>
				<?php $val = htmlspecialchars($name, ENT_COMPAT, 'UTF-8'); ?>
				<li class="sortable-item" data-alias="<?php echo $val; ?>"><?php echo $options[$name]; ?>
					<input type="hidden" name="<?php echo $field->name ?>[]" value="<?php echo $val; ?>"></li>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php foreach ($options as $name => $label): ?>
			<?php if (!in_array($name, $selection)): ?>
				<?php $val = htmlspecialchars($name, ENT_COMPAT, 'UTF-8'); ?>
				<li class="sortable-item" data-alias="<?php echo $val; ?>"><?php echo $label; ?>
					<input type="hidden" name="<?php echo $field->name ?>[]" value="<?php echo $val; ?>"></li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
