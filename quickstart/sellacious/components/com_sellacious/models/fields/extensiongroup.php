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

/**
 * Form Field class for the Extension Group
 *
 * @since   1.6.0
 */
class JFormFieldExtensionGroup extends JFormField
{
	/**
	 * The field type
	 *
	 * @var  string
	 *
	 * @since   1.6.0
	 */
	protected $type = 'ExtensionGroup';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>';
		}

		// May be we should also check for data structure of value. Skipping for now!
		$this->value = !is_object($this->value) && !is_array($this->value) ? array() : (array) $this->value;

		$options = array('client' => 2, 'debug' => 0);
		$data    = (object) get_object_vars($this);

		$data->extensions = $this->getExtensions();

		$html    = JLayoutHelper::render('com_sellacious.formfield.' . strtolower($this->type), $data, '', $options);

		return $html;
	}

	/**
	 * Method to get a list of installed extensions
	 *
	 * @return  string[]  The extensions list
	 *
	 * @since   1.6.0
	 */
	protected function getExtensions()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions');

		$extensions = $db->setQuery($query)->loadObjectList();

		$names = array();

		foreach ($extensions as $extension)
		{
			switch ($extension->type)
			{
				case 'module':
				case 'component':
				case 'template':
					$names[] = $extension->element;
					break;
				case 'plugin':
					$names[] = 'plg_' . $extension->folder . '_' . $extension->element;
			}
		}

		return $names;
	}
}
