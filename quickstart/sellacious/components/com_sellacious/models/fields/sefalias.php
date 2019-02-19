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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.6.0
 */
class JFormFieldSefAlias extends JFormField
{
	/**
	 * The field type
	 *
	 * @var		string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'sefAlias';

	/**
	 * Method to get the field input markup
	 *
	 * @return  string  The field input markup
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		$views = $this->getViews();
		$data  = get_object_vars($this);

		$data['views'] = $views;

		$html = JLayoutHelper::render('com_sellacious.formfield.' . strtolower($this->type), (object) $data, '', array('client' => 2, 'debug' => 0));

		return $html;
	}

	/**
	 * Method to get the field options
	 *
	 * @return  array  The field option objects
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getViews()
	{
		$value     = (is_array($this->value) || is_object($this->value)) ? new Registry($this->value) : new Registry;
		$fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
		$options   = array();

		foreach ($this->element->xpath('view') as $view)
		{
			$viewName   = (string) $view['name'];
			$layoutName = (string) $view['layout'] ?: 'default';
			$default    = (string) $view['default'];
			$disabled   = (string) $view['disabled'];
			$readonly   = (string) $view['readonly'];
			$label      = trim((string) $view);

			if ($layoutName)
			{
				$fullName  = $viewName . '.' . $layoutName;
				$fieldName = $this->name . '[' . $viewName . '][' . $layoutName . ']';
				$fieldId   = $this->id . '_' . $viewName . '_' . $layoutName;
			}
			else
			{
				$fullName  = $viewName;
				$fieldName = $this->name . '[' . $viewName . ']';
				$fieldId   = $this->id . '_' . $viewName;
			}

			$option = array(
				'field_name' => $fieldName,
				'field_id'   => $fieldId,
				'label'      => JText::alt($label, $fieldname),
				'disabled'   => $disabled == 'true',
				'readonly'   => $readonly == 'true',
				'default'    => $default,
				'value'      => $value->get($fullName),
			);

			$options[] = (object) $option;
		}

		reset($options);

		return $options;
	}

}
