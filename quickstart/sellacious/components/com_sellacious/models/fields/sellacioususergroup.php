<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Form Field class for the user groups in sellacious.
 *
 * @since  1.0.0
 */
class JFormFieldSellaciousUserGroup extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'SellaciousUserGroup';

	/**
	 * Method to get the field options.
	 *
	 * @return  string  The field option objects.
	 *
	 * @since   1.0.0
	 *
	 * @todo    Add access check
	 */
	protected function getInput()
	{
		static $count;

		$count++;

		$me       = JFactory::getUser();
		$helper   = SellaciousHelper::getInstance();
		$type     = strtolower((string) $this->element['group']);
		$groups   = $helper->user->getGroups($type);
		$required = $this->required ? 'required' : '';
		$isSuper  = $me->authorise('core.admin');
		$g_count  = count($groups);

		$html   = array();
		$html[] = '<fieldset id="' . $this->id . '" class="form-group checkboxes '. $required . '" style="padding-left:15px;">';

		$value  = is_object($this->value) ? ArrayHelper::fromObject($this->value) : (array) $this->value;

		foreach ($groups as $item)
		{
			// [SECURITY] Add item if the user is super admin or the group is not super admin
			if ($isSuper || !JAccess::checkGroup($item->id, 'core.admin'))
			{
				// Setup the variable attributes.
				$eid      = $count . 'group_' . $item->id;
				$checked  = ($g_count == 1 || ($this->value && in_array($item->id, $value))) ? ' checked="checked"' : '';
				$rel      = ($item->parent_id > 0) ? ' rel="' . $count . 'group_' . $item->parent_id . '"' : '';
				$disabled = $item->disabled ? ' disabled="disabled"' : '';
				$attrName = $item->disabled ? '' : ' name="' . $this->name . '"';
				$i_type   = $this->multiple ? 'checkbox' : 'radio';
				$i_class  = $this->multiple ? 'checkbox' : 'radiobox';

				// Build the HTML for the item
				// $text_prefix = '';
				$text_prefix = str_repeat('<span class="gi">|&mdash;</span>', $item->level) . ' ';

				$html[] = <<<H
<div class="$i_type">
	<label><input type="{$i_type}" $attrName value="$item->id" id="$eid" $rel $checked $disabled class="$i_class style-0"/>
	<span>$text_prefix $item->title</span></label>
</div>
H;
			}
		}

		$html[] = '</fieldset>';

		return implode("\n", $html);
	}
}
