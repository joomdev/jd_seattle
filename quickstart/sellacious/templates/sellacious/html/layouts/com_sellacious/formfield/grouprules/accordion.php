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

/** @var stdClass $displayData */
$actions_groups = (array) $displayData->actions;

// Prepare full width format output.
if (empty($displayData->group))
{
	?><p class="alert alert-info"><?php echo JText::_('COM_SELLACIOUS_PERMISSIONS_SELECT_GROUP_MSG') ?></p><?php

	return;
}
?>
<p class="rule-desc"><?php echo JText::_('JLIB_RULES_SETTINGS_DESC') ?></p>
<br>
<?php
echo JHtml::_('bootstrap.startAccordion', $displayData->id . '_accordion', array('parent' => true, 'toggle' => false));

$data = (object) array(
	'id'            => $displayData->id,
	'name'          => $displayData->name,
	'component'     => $displayData->component,
	'assetId'       => $displayData->assetId,
	'group'         => $displayData->group,
	'assetRules'    => $displayData->assetRules,
	'actions_group' => null,
);

foreach ($actions_groups as $actions_group)
{
	$data->actions_group = $actions_group;

	echo JHtml::_('bootstrap.addSlide', $displayData->id . '_accordion', JText::_($actions_group->title), $actions_group->name, 'panel');
	echo JLayoutHelper::render('com_sellacious.formfield.grouprules.accordion_slide', $data);
	echo JHtml::_('bootstrap.endSlide');
}

echo JHtml::_('bootstrap.endAccordion');
?>
<div class="alert alert-info margin-top-5">
	<?php echo JText::_('JLIB_RULES_SETTING_NOTES'); ?>
</div>

