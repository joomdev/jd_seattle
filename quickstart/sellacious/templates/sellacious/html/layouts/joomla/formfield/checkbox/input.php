<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('_JEXEC') or die;

/** @var  array  $displayData */
$field = (object) $displayData;
?>
<label for="<?php echo $field->id ?>" class="checkbox">
	<input type="checkbox" name="<?php echo $field->name ?>" id="<?php echo $field->id ?>"
		   value="<?php echo htmlspecialchars($field->value, ENT_COMPAT, 'UTF-8') ?>" title=""
	<?php echo $field->class . $field->checked . $field->disabled . $field->onclick . $field->onchange . $field->required . $field->autofocus ?>/>
	<span> <?php echo JText::_('JYES') ?></span>
</label>
