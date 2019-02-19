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

JFormHelper::loadFieldClass('file');

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for files
 *
 * @link   http://www.w3.org/TR/html-markup/input.file.html#input.file
 */
class JFormFieldDocument extends JFormFieldFile
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Document';

	/** @var  int */
	protected $record_id;

	/**
	 * @var  string
	 */
	protected $context;

	/**
	 * @var  string
	 */
	protected $filetype;

	/**
	 * @var  string
	 */
	protected $limit;

	/**
	 * @var  bool
	 */
	protected $rename;

	/**
	 * @var bool
	 */
	protected $numeric;

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
			case 'record_id':
			case 'limit':
			case 'context':
			case 'filetype':
			case 'rename':
			case 'numeric':
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
			case 'record_id':
			case 'limit':
			case 'context':
			case 'filetype':
			case 'rename':
			case 'numeric':
			$this->$name = $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}


	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
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
			$this->record_id = (int)$this->element['record_id'];
			$this->limit     = (int)$this->element['limit'];
			$this->context   = (string)$this->element['context'];
			$this->filetype  = (string)$this->element['filetype'];
			$this->rename    = (string)$this->element['rename'] == 'false' ? false : true;
			$this->numeric   = (string)$this->element['numeric'] == 'true' ? true : false;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 */
	protected function getInput()
	{
		// todo: Add feature for readonly attribute support
		$helper = SellaciousHelper::getInstance();

		// Context should be like 'table_name/image', eg: #__abc_xyz => abc_xyz
		list($tbl_name, $context) = explode('.', $this->context, 2);

		// Load value automatically, don't depend on model
		$filter = array(
			'list.select' => 'a.id, a.path, a.state, a.original_name, a.doc_type, a.doc_reference',
			'table_name'  => $tbl_name,
			'context'     => $context,
			'record_id'   => $this->record_id,
		);

		$this->value  = $helper->media->loadObjectList($filter);
		$this->class .= ' hidden hidden-lg hidden-md hidden-sm hidden-xs';

		JHtml::_('behavior.framework');
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'com_sellacious/field.document.css', array('version' => S_VERSION_CORE, 'relative' => true));
		JHtml::_('script', 'com_sellacious/field.document.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$formToken = JSession::getFormToken();
		$jsTarget = array(
			'table'     => $tbl_name,
			'context'   => $context,
			'record_id' => $this->record_id,
			'rename'    => $this->rename,
			'type'      => $this->filetype,
			'limit'     => $this->limit,
			'temp'      => '0',
		);

		$jsTarget = json_encode($jsTarget);

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
			(function ($) {
				$(document).ready(function () {
					var o = new JFormFieldDocument;
					o.setup({
						wrapper : '{$this->id}_wrapper',
						siteRoot: '" . JUri::root(true) . "',
						target: {$jsTarget},
						token: '{$formToken}=1',
					});
				});
			})(jQuery);
		");

		$options     = $this->getOptions();
		$displayData = array_merge(get_object_vars($this), array('options' => $options));
		$config      = array('client' => 2, 'debug' => 0);
		$html        = JLayoutHelper::render('com_sellacious.formfield.document', (object) $displayData, '', $config);

		return $html;
	}

	/**
	 * Method to get the field options for document types.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		/** @var SimpleXMLElement $children */
		$children = $this->element->children();

		/** @var SimpleXMLElement $option */
		foreach ($children as $option)
		{
			// Only add <option /> elements.
			if ($option->getName() != 'option')
			{
				continue;
			}

			$value    = (string)$option['value'];
			$disabled = (string)$option['disabled'];
			$disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
			$disabled = $disabled || $this->readonly;

			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', $value, JText::alt(trim((string)$option), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text', $disabled);

			// Make label available for Javascript
			JText::script(trim((string) $option));

			// Set some option attributes.
			$tmp->class = (string)$option['class'];

			// Set some JavaScript option attributes.
			$tmp->onclick = (string)$option['onclick'];

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
