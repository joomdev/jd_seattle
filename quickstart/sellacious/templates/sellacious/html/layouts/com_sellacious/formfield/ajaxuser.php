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

/** @var array $displayData */
$field = (object) $displayData;

$user  = new Joomla\Registry\Registry($field->user);
$id    = $user->get('id');
$name  = $user->get('name');
$email = $user->get('email');
?>
<div class="w100p">
	<input type="text" id="<?php echo $field->id ?>_ui" value="<?php echo $email ?>"
		class="<?php echo preg_replace('/required/i', '', $field->class) ?>" title="" placeholder="Enter an email&hellip;" />
	<div id="<?php echo $field->id ?>_name" class="input-legend form-control"><?php echo $name ?></div>
	<input type="hidden" id="<?php echo $field->id ?>" name="<?php echo $field->name ?>"
		value="<?php echo $id ?>" class="<?php echo $field->class ?>" readonly />
</div>
