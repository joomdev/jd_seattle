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
$data = $displayData;

$field_id   = $data->id;
$field_name = $data->name;
$component  = $data->component;
$section    = $data->section;
$assetId    = $data->assetId;
$actions    = $data->actions;
$group      = $data->group;

/** @var JAccessRules $assetRules */
$assetRules = $data->assetRules;

// Prepare full width format output.
?>
<?php if (empty($group)): ?>

	<p class="alert alert-info"><?php echo JText::_('COM_SELLACIOUS_PERMISSIONS_SELECT_GROUP_MSG') ?></p>

<?php else: ?>
	<p class="rule-desc"><?php echo JText::_('JLIB_RULES_SETTINGS_DESC') ?></p>
	<br>
	<table class="table table-striped table-hover" id="<?php echo $field_id ?>-table">
		<thead>
		<tr>
			<th class="actions" id="actions-th<?php echo $group->value ?>">
				<span class="acl-action"><?php echo JText::_('JLIB_RULES_ACTION') ?></span>
			</th>
			<th class="settings" id="settings-th<?php echo $group->value ?>">
				<span class="acl-action"><?php echo JText::_('JLIB_RULES_SELECT_SETTING') ?></span>
			</th>
			<?php
			// The calculated setting is not shown for the root group of global configuration as it cannot inherit.
			// $can_inherit = !($group->parent_id == 0 && empty($component));
			// if ($group->inherit)
			if (true)
			{
				?>
				<th id="aclactionth<?php echo $group->value ?>">
					<span class="acl-action"><?php echo JText::_('JLIB_RULES_CALCULATED_SETTING') ?></span>
				</th>
				<?php
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		$choices = array(
			'2' => JText::_($group->inherit ? 'JLIB_RULES_INHERITED' : 'JLIB_RULES_NOT_SET'),
			'1' => JText::_('JLIB_RULES_ALLOWED'),
			'0' => JText::_('JLIB_RULES_DENIED'),
		);
		$icons   = array(
			'2' => 'fa-circle',
			'1' => 'fa-check',
			'0' => 'fa-times',
		);

		foreach ($actions as $action)
		{
			// Get the actual setting and inherited setting for the action for this group.
			$assetRule     = $assetRules->allow($action->name, $group->value);
			$inheritedRule = JAccess::checkGroup($group->value, $action->name, $assetId);
			$selected      = is_bool($assetRule) ? ($assetRule ? '1' : '0') : '2';
			$denyInherit   = $inheritedRule === false && $assetRule !== false;
			?>
			<tr>
				<td headers="actions-th<?php echo $group->value ?>">
					<?php $label_tip = JText::_($action->title) . ' ' . JText::_($action->description); ?>
					<label for="<?php echo $field_id . '_' . $action->name . '_' . $group->value ?>"
						class="hasTooltip" title="<?php echo htmlspecialchars($label_tip, ENT_COMPAT, 'UTF-8') ?>">
						<?php echo JText::_($action->title) ?> &nbsp;&nbsp;&nbsp;<em class="label label-primary">(<?php echo $action->name ?>)</em>
					</label>
				</td>
				<td headers="settings-th<?php echo $group->value ?>" class="nowrap">
					<?php
					$input_name = $field_name . '[' . $action->name . '][' . $group->value . ']';
					$input_id   = $field_id . '_' . str_replace('.', '-', $action->name) . '_' . $group->value;
					?>
					<div id="<?php echo $input_id ?>" class="btn-group required nowrap" aria-required="true" data-toggle="buttons">
						<?php
						foreach ($choices as $index => $choice)
						{
							?>
							<label for="<?php echo $input_id . $index ?>"
								class="btn btn-default <?php echo $index == $selected ? 'active' : ''; ?>
								<?php echo $denyInherit ? ' disabled ' : '' ?>">
								<input type="radio" id="<?php echo $input_id . $index ?>" name="<?php echo $input_name ?>"
									value="<?php echo $index == 2 ? '' : $index ?>"
									<?php echo $denyInherit ? ' disabled ' : '' ?>
									<?php echo $selected == $index ? ' checked="checked" ' : '' ?>>
								<span class="hidden-xs hidden-sm hidden-md"><?php echo $choice ?></span>
								<i class="fa <?php echo $icons[$index] ?> hidden-lg"></i>
							</label>
							<?php
						}
						?>
					</div>
					<?php
					// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
					if ($assetRule === true && $inheritedRule === false)
					{
						echo JText::_('JLIB_RULES_CONFLICT');
					}
					?>
				</td>
				<?php
				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				// if ($group->inherit)
				if (true)
				{
					?>
					<td headers="aclactionth<?php echo $group->value ?>">
						<?php
						// This is where we show the current effective settings considering current group, path and cascade.
						// Check whether this is a component or global. Change the text slightly.
						if (JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true)
						{
							if ($inheritedRule === null)
							{
								echo '<span class="label label-info">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === true)
							{
								echo '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === false)
							{
								if ($assetRule === false)
								{
									echo '<span class="label label-danger">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
								}
								else
								{
									echo '<span class="label label-danger"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED') . '</span>';
								}
							}
						}
						elseif (!empty($component))
						{
							echo '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span>';
						}
						else
						{
							// Special handling for  groups that have global admin because they can't  be denied.
							// The admin rights can be changed.
							if ($action->name === 'core.admin')
							{
								echo '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
							}
							elseif ($inheritedRule === false)
							{
								// Other actions cannot be changed.
								echo '<span class="label label-info"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span>';
							}
							else
							{
								echo '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN') . '</span>';
							}
						}
						?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}
		?>
		<tr>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		</tbody>
	</table>

	<div class="alert">
		<?php
		if ($section == 'component' || $section == null)
		{
			echo JText::_('JLIB_RULES_SETTING_NOTES');
		}
		else
		{
			echo JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}
		?>
	</div>
<?php endif; ?>
