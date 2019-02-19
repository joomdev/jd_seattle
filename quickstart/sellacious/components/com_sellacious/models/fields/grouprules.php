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
 * Form Field class for the Joomla Platform.
 * Field for assigning permissions to groups for a given asset
 *
 * @see    JAccess
 * @since  11.1
 */
class JFormFieldGroupRules extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'GroupRules';

	/**
	 * The section.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $section;

	/**
	 * The component.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $component;

	/**
	 * The assetField.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $assetField;

	/**
	 * The field containing user group for which to set the access.
	 *
	 * @var   string
	 *
	 * @since  1.2.0
	 */
	protected $groupField;

	/**
	 * The setting to specify whether to list actions in groups ("true"|"false").
	 *
	 * @var    string
	 *
	 * @since  1.2.0
	 */
	protected $accordion;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string $name The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'section':
			case 'component':
			case 'assetField':
			case 'groupField':
			case 'accordion':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string $name  The property name for which to the the value.
	 * @param   mixed  $value The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'section':
			case 'component':
			case 'assetField':
			case 'groupField':
			case 'accordion':
			$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed            $value    The form field value to validate.
	 * @param   string           $group    The field name group control value. This acts as as an array container for the field.
	 *                                     For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                     full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->section    = $this->element['section'] ? (string) $this->element['section'] : '';
			$this->component  = $this->element['component'] ? (string) $this->element['component'] : '';
			$this->assetField = $this->element['asset_field'] ? (string) $this->element['asset_field'] : 'asset_id';
			$this->groupField = $this->element['group_field'] ? (int) $this->element['group_field'] : 'user_group';
			$this->accordion  = $this->element['accordion'] ? (int) $this->element['accordion'] : 'true';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for Access Control Lists.
	 * Optionally can be associated with a specific component and section.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		JHtml::_('bootstrap.tooltip');

		// Get required parameters.
		$assetId = $this->getAssetId();
		$actions = $this->getActions();
		$groupId = (int) $this->form->getValue($this->groupField);
		$group   = $this->getUserGroup($groupId);

		// Get the rules for just this asset (non-recursive).
		$assetRules = JAccess::getAssetRules($assetId);

		$data = new stdClass;

		$data->id         = $this->id;
		$data->name       = $this->name;
		$data->component  = $this->component;
		$data->section    = $this->section;
		$data->assetId    = $assetId;
		$data->actions    = $actions;
		$data->group      = $group;
		$data->assetRules = $assetRules;

		$layout = $this->accordion ? '.accordion' : '.grid';
		$html   = JLayoutHelper::render('com_sellacious.formfield.grouprules' . $layout, $data, '', array('debug' => 0));

		return $html;
	}

	/**
	 * Get a list of the user groups.
	 *
	 * @param  int  $groupId  Group Id to load
	 *
	 * @return  stdClass
	 *
	 * @since   1.2.0
	 */
	protected function getUserGroup($groupId)
	{
		$helper = SellaciousHelper::getInstance();
		$groups = $helper->user->getGroups();

		$ids    = ArrayHelper::getColumn($groups, 'id');
		$groups = ArrayHelper::pivot($groups, 'id');
		$group  = ArrayHelper::getValue($groups, $groupId, null);

		if ($group)
		{
			$group->inherit = in_array($group->parent_id, $ids);
		}

		return $group;
	}

	/**
	 * Get the asset id for the concern asset object for which permissions are to be set.
	 *
	 * @return  int
	 *
	 * @since   1.3.5
	 */
	protected function getAssetId()
	{
		if ($this->section == 'component')
		{
			// Need to find the asset id by the name of the component.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select($db->qn('id'))
				->from($db->qn('#__assets'))
				->where($db->qn('name') . ' = ' . $db->q($this->component));

			$db->setQuery($query);
			$assetId = (int) $db->loadResult();
		}
		else
		{
			// Find the asset id of the content.
			// Note that for global configuration, com_config injects asset_id = 1 into the form.
			$assetId = (int) $this->form->getValue($this->assetField);
		}

		return $assetId;
	}

	/**
	 * Method to return a list of actions from the component's access.xml file for which permissions can be set.
	 *
	 * @return  stdClass[]  The list of actions available
	 *
	 * @since   1.2.0
	 */
	protected function getActions()
	{
		$helper  = SellaciousHelper::getInstance();
		$actions = $this->accordion ? $helper->access->getActionGroups() : $helper->access->getActions();

		return $actions;
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 *
	 * @since   1.5.2
	 */
	protected function getLabel()
	{
		return '';
	}
}
