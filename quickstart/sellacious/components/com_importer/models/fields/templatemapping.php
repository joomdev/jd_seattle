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
use Sellacious\Import\ImportHelper;

defined('_JEXEC') or die;

/**
 * Field to map import template columns in template editor
 *
 * @since   1.5.2
 */
class JFormFieldTemplateMapping extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.6
	 */
	public $type = 'templateMapping';

	/**
	 * Method to get the user field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$importer = null;
		$handler  = $this->form->getValue('import_type');

		if (!$handler)
		{
			$message = JText::_('COM_IMPORTER_FORM_FIELD_TEMPLATE_MAPPING_INPUT_SELECT_HANDLER_MESSAGE');

			return '<div class="alert alert-info">' . $message . '</div>';
		}

		try
		{
			$importer = ImportHelper::getImporter($handler);
		}
		catch (Exception $e)
		{
		}

		if ($importer && is_callable(array($importer, 'getColumns')) && count($columns = $importer->getColumns()))
		{
			$data    = (object) array_merge(get_object_vars($this), array('columns' => $columns));
			$options = array('client' => 2, 'debug' => false);

			return JLayoutHelper::render('com_importer.formfield.templatemapping', $data, '', $options);
		}

		$message = JText::_('COM_IMPORTER_FORM_FIELD_TEMPLATE_MAPPING_INPUT_COLUMN_UNSUPPORTED_MESSAGE');

		return '<div class="alert alert-info">' . $message . '</div>';
	}
}
