<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Field;

use FOF30\Form\FieldInterface;
use FOF30\Form\Form;
use FOF30\Model\DataModel;
use JHtml;
use JText;

defined('_JEXEC') or die;

\JFormHelper::loadFieldClass('rules');

/**
 * Form Field class for FOF
 * Joomla! ACL Rules
 *
 * @deprecated 3.1  Support for XML forms will be removed in FOF 4
 */
class Rules extends \JFormFieldRules implements FieldInterface
{
	/**
	 * @var  string  Static field output
	 */
	protected $static;

	/**
	 * @var  string  Repeatable field output
	 */
	protected $repeatable;

	/**
	 * The Form object of the form attached to the form field.
	 *
	 * @var    Form
	 */
	protected $form;

	/**
	 * A monotonically increasing number, denoting the row number in a repeatable view
	 *
	 * @var  int
	 */
	public $rowid;

	/**
	 * The item being rendered in a repeatable form field
	 *
	 * @var  DataModel
	 */
	public $item;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   2.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			// This field cannot provide a static display
			case 'static':
				return '';
				break;

			// This field cannot provide a repeateable display
			case 'repeatable':
				return '';
				break;

			default:
				return parent::__get($name);
		}
	}

	/**
	 * Get the rendering of this field type for static display, e.g. in a single
	 * item view (typically a "read" task).
	 *
	 * @since 2.0
	 *
	 * @return  string  The field HTML
	 */
	public function getStatic()
	{
		return '';
	}

	/**
	 * Get the rendering of this field type for a repeatable (grid) display,
	 * e.g. in a view listing many item (typically a "browse" task)
	 *
	 * @since 2.1
	 *
	 * @return  string  The field HTML
	 */
	public function getRepeatable()
	{
		return '';
	}

	/**
	 * At the timing of this writing (2013-12-03), the Joomla "rules" field is buggy. When you are
	 * dealing with a new record it gets the default permissions from the root asset node, which
	 * is fine for the default permissions of Joomla articles, but unsuitable for third party software.
	 * We had to copy & paste the whole code, since we can't "inject" the correct asset id if one is
	 * not found. Our fixes are surrounded by `FOF Library fix` remarks.
	 *
	 * @return  string  The input field's HTML for this field type
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');

		// Initialise some field attributes.
		$section    = $this->section;
		$component  = $this->component;
		$assetField = $this->assetField;

		// Get the actions for the asset.
		$actions = \JAccess::getActions($component, $section);

		// Iterate over the children and add to the actions.
		/** @var \SimpleXMLElement $el */
		foreach ($this->element->children() as $el)
		{
			if ($el->getName() == 'action')
			{
				$actions[] = (object) array('name' => (string) $el['name'], 'title' => (string) $el['title'],
					'description' => (string) $el['description']);
			}
		}

		// Get the explicit rules for this asset.
		if ($section == 'component')
		{
			// Need to find the asset id by the name of the component.
			$db    = $this->form->getContainer()->platform->getDbo();
			$query = $db->getQuery(true)
						->select($db->quoteName('id'))
						->from($db->quoteName('#__assets'))
						->where($db->quoteName('name') . ' = ' . $db->quote($component));

			$assetId = (int) $db->setQuery($query)->loadResult();
		}
		else
		{
			// Find the asset id of the content.
			// Note that for global configuration, com_config injects asset_id = 1 into the form.
			$assetId = $this->form->getValue($assetField);

			// ==== FOF Library fix - Start ====
			// If there is no assetId (let's say we are dealing with a new record), let's ask the table
			// to give it to us. Here you should implement your logic (ie getting default permissions from
			// the component or from the category)
			if(!$assetId)
			{
				$table   = $this->form->getModel();
				$assetId = $table->getAssetParentId();
			}
			// ==== FOF Library fix - End   ====
		}

		// Full width format.

		// Get the rules for just this asset (non-recursive).
		$assetRules = \JAccess::getAssetRules($assetId);

		// Get the available user groups.
		$groups = $this->getUserGroups();

		// Prepare output
		$html = array();

		// Description
		$html[] = '<p class="rule-desc">' . JText::_('JLIB_RULES_SETTINGS_DESC') . '</p>';

		// Begin tabs
		$html[] = '<div id="permissions-sliders" class="tabbable tabs-left">';

		// Building tab nav
		$html[] = '<ul class="nav nav-tabs">';

		foreach ($groups as $group)
		{
			// Initial Active Tab
			$active = "";

			if ($group->value == 1)
			{
				$active = "active";
			}

			$html[] = '<li class="' . $active . '">';
			$html[] = '<a href="#permission-' . $group->value . '" data-toggle="tab">';
			$html[] = str_repeat('<span class="level">&ndash;</span> ', $curLevel = $group->level) . $group->text;
			$html[] = '</a>';
			$html[] = '</li>';
		}

		$html[] = '</ul>';

		$html[] = '<div class="tab-content">';

		// Start a row for each user group.
		foreach ($groups as $group)
		{
			// Initial Active Pane
			$active = "";

			if ($group->value == 1)
			{
				$active = " active";
			}

			$html[] = '<div class="tab-pane' . $active . '" id="permission-' . $group->value . '">';
			$html[] = '<table class="table table-striped">';
			$html[] = '<thead>';
			$html[] = '<tr>';

			$html[] = '<th class="actions" id="actions-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_ACTION') . '</span>';
			$html[] = '</th>';

			$html[] = '<th class="settings" id="settings-th' . $group->value . '">';
			$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_SELECT_SETTING') . '</span>';
			$html[] = '</th>';

			// The calculated setting is not shown for the root group of global configuration.
			$canCalculateSettings = ($group->parent_id || !empty($component));

			if ($canCalculateSettings)
			{
				$html[] = '<th id="aclactionth' . $group->value . '">';
				$html[] = '<span class="acl-action">' . JText::_('JLIB_RULES_CALCULATED_SETTING') . '</span>';
				$html[] = '</th>';
			}

			$html[] = '</tr>';
			$html[] = '</thead>';
			$html[] = '<tbody>';

			foreach ($actions as $action)
			{
				$html[] = '<tr>';
				$html[] = '<td headers="actions-th' . $group->value . '">';
				$html[] = '<label for="' . $this->id . '_' . $action->name . '_' . $group->value . '" class="hasTooltip" title="'
					. htmlspecialchars(JText::_($action->title) . ' ' . JText::_($action->description), ENT_COMPAT, 'UTF-8') . '">';
				$html[] = JText::_($action->title);
				$html[] = '</label>';
				$html[] = '</td>';

				$html[] = '<td headers="settings-th' . $group->value . '">';

				$html[] = '<select class="input-small" name="' . $this->name . '[' . $action->name . '][' . $group->value . ']" id="' . $this->id . '_' . $action->name
					. '_' . $group->value . '" title="'
					. JText::sprintf('JLIB_RULES_SELECT_ALLOW_DENY_GROUP', JText::_($action->title), trim($group->text)) . '">';

				$inheritedRule = \JAccess::checkGroup($group->value, $action->name, $assetId);

				// Get the actual setting for the action for this group.
				$assetRule = $assetRules->allow($action->name, $group->value);

				// Build the dropdowns for the permissions sliders

				// The parent group has "Not Set", all children can rightly "Inherit" from that.
				$html[] = '<option value=""' . ($assetRule === null ? ' selected="selected"' : '') . '>'
					. JText::_(empty($group->parent_id) && empty($component) ? 'JLIB_RULES_NOT_SET' : 'JLIB_RULES_INHERITED') . '</option>';
				$html[] = '<option value="1"' . ($assetRule === true ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_ALLOWED')
					. '</option>';
				$html[] = '<option value="0"' . ($assetRule === false ? ' selected="selected"' : '') . '>' . JText::_('JLIB_RULES_DENIED')
					. '</option>';

				$html[] = '</select>&#160; ';

				// If this asset's rule is allowed, but the inherited rule is deny, we have a conflict.
				if (($assetRule === true) && ($inheritedRule === false))
				{
					$html[] = JText::_('JLIB_RULES_CONFLICT');
				}

				$html[] = '</td>';

				// Build the Calculated Settings column.
				// The inherited settings column is not displayed for the root group in global configuration.
				if ($canCalculateSettings)
				{
					$html[] = '<td headers="aclactionth' . $group->value . '">';

					// This is where we show the current effective settings considering currrent group, path and cascade.
					// Check whether this is a component or global. Change the text slightly.

					if (\JAccess::checkGroup($group->value, 'core.admin', $assetId) !== true)
					{
						if ($inheritedRule === null)
						{
							$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === true)
						{
							$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							if ($assetRule === false)
							{
								$html[] = '<span class="label label-important">' . JText::_('JLIB_RULES_NOT_ALLOWED') . '</span>';
							}
							else
							{
								$html[] = '<span class="label"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_NOT_ALLOWED_LOCKED')
									. '</span>';
							}
						}
					}
					elseif (!empty($component))
					{
						$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
							. '</span>';
					}
					else
					{
						// Special handling for  groups that have global admin because they can't  be denied.
						// The admin rights can be changed.
						if ($action->name === 'core.admin')
						{
							$html[] = '<span class="label label-success">' . JText::_('JLIB_RULES_ALLOWED') . '</span>';
						}
						elseif ($inheritedRule === false)
						{
							// Other actions cannot be changed.
							$html[] = '<span class="label label-important"><i class="icon-lock icon-white"></i> '
								. JText::_('JLIB_RULES_NOT_ALLOWED_ADMIN_CONFLICT') . '</span>';
						}
						else
						{
							$html[] = '<span class="label label-success"><i class="icon-lock icon-white"></i> ' . JText::_('JLIB_RULES_ALLOWED_ADMIN')
								. '</span>';
						}
					}

					$html[] = '</td>';
				}

				$html[] = '</tr>';
			}

			$html[] = '</tbody>';
			$html[] = '</table></div>';
		}

		$html[] = '</div></div>';

		$html[] = '<div class="alert">';

		if ($section == 'component' || $section == null)
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES');
		}
		else
		{
			$html[] = JText::_('JLIB_RULES_SETTING_NOTES_ITEM');
		}

		$html[] = '</div>';

		return implode("\n", $html);
	}
}
