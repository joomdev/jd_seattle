<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Form\Header;

use FOF30\Form\Form;
use FOF30\Form\HeaderInterface;
use SimpleXMLElement;

defined('_JEXEC') or die;

/**
 * A base class for HeaderInterface fields, used to define the filters and the
 * elements of the header row in repeatable (browse) views
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 *
 * @deprecated 3.1  Support for XML forms will be removed in FOF 4
 */
abstract class HeaderBase
{
	/**
	 * The description text for the form field.  Usually used in tooltips.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $description;

	/**
	 * The SimpleXMLElement object of the <field /> XML element that describes the header field.
	 *
	 * @var    SimpleXMLElement
	 * @since  2.0
	 */
	protected $element;

	/**
	 * The Form object of the form attached to the header field.
	 *
	 * @var    Form
	 * @since  2.0
	 */
	protected $form;

	/**
	 * The label for the header field.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $label;

	/**
	 * The header HTML.
	 *
	 * @var    string|null
	 * @since  2.0
	 */
	protected $header;

	/**
	 * The filter HTML.
	 *
	 * @var    string|null
	 * @since  2.0
	 */
	protected $filter;

	/**
	 * The buttons HTML.
	 *
	 * @var    string|null
	 * @since  2.0
	 */
	protected $buttons;

	/**
	 * The options for a drop-down filter.
	 *
	 * @var    array|null
	 * @since  2.0
	 */
	protected $options;

	/**
	 * The name of the form field.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $name;

	/**
	 * The name of the field.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $fieldname;

	/**
	 * The group of the field.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $group;

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $type;

	/**
	 * The value of the filter.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $value;

	/**
	 * The intended table data width (in pixels or percent).
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $tdwidth;

	/**
	 * The key of the filter value in the model state.
	 *
	 * @var    mixed
	 * @since  2.0
	 */
	protected $filterSource;

	/**
	 * The name of the filter field
	 *
	 * @var    mixed
	 * @since  3.0
	 */
	protected $filterFieldName;

	/**
	 * Is this a sortable column?
	 *
	 * @var    bool
	 * @since  2.0
	 */
	protected $sortable = false;

	/**
	 * Should we ignore the header (have the field act as just a filter)?
	 *
	 * @var    bool
	 * @since  3.0
	 */
	protected $onlyFilter = false;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   Form  $form  The form to attach to the form field object.
	 *
	 * @since   2.0
	 */
	public function __construct(Form $form = null)
	{
		// If there is a form passed into the constructor set the form and form control properties.
		if ($form instanceof Form)
		{
			$this->form = $form;
		}
	}

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
			case 'description':
			case 'name':
			case 'type':
			case 'fieldname':
			case 'group':
			case 'tdwidth':
			case 'filterSource':
			case 'filterFieldName':
				return $this->$name;
				break;

			case 'label':
				if (empty($this->label))
				{
					$this->label = $this->getLabel();
				}

				return $this->label;

			case 'value':
				if (empty($this->value))
				{
					$this->value = $this->getValue();
				}

				return $this->value;
				break;

			case 'header':
				if (empty($this->header))
				{
					$this->header = $this->onlyFilter ? '' : $this->getHeader();
				}

				return $this->header;
				break;

			case 'filter':
				if (empty($this->filter))
				{
					$this->filter = $this->getFilter();
				}

				return $this->filter;
				break;

			case 'buttons':
				if (empty($this->buttons))
				{
					$this->buttons = $this->getButtons();
				}

				return $this->buttons;
				break;

			case 'options':
				if (empty($this->options))
				{
					$this->options = $this->getOptions();
				}

				return $this->options;
				break;

			case 'sortable':
				if (empty($this->sortable))
				{
					$this->sortable = $this->getSortable();
				}

				return $this->sortable;
				break;
		}

		return null;
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   Form  $form  The JForm object to attach to the form field.
	 *
	 * @return  HeaderInterface  The form field object so that the method can be used in a chain.
	 *
	 * @since   2.0
	 */
	public function setForm(Form $form)
	{
		$this->form = $form;

		return $this;
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.0
	 */
	public function setup(SimpleXMLElement $element, $group = null)
	{
		// Make sure there is a valid JFormField XML element.
		if ((string) $element->getName() != 'header')
		{
			return false;
		}

		// Reset the internal fields
		$this->label = null;
		$this->header = null;
		$this->filter = null;
		$this->buttons = null;
		$this->options = null;
		$this->value = null;
		$this->filterSource = null;
		$this->filterFieldName = null;

		// Set the XML element object.
		$this->element = $element;

		// Get some important attributes from the form field element.
		$id = (string) $element['id'];
		$name = (string) $element['name'];
		$filterSource = (string) $element['filter_source'];
		$filterFieldName = (string) $element['searchfieldname'];
		$tdwidth = (string) $element['tdwidth'];

		// Set the field description text.
		$this->description = (string) $element['description'];

		// Set the group of the field.
		$this->group = $group;

		// Set the td width of the field.
		$this->tdwidth = $tdwidth;

		// Set the field name and id.
		$this->fieldname = $this->getFieldName($name);
		$this->name = $this->getName($this->fieldname);
		$this->id = $this->getId($id, $this->fieldname);
		$this->filterSource = $this->getFilterSource($filterSource);
		$this->filterFieldName = $this->getFilterFieldName($filterFieldName);

		// Set the field default value.
		$this->value = $this->getValue();

		// Setup the onlyFilter property
		$onlyFilter = $this->element['onlyFilter'] ? (string) $this->element['onlyFilter'] : false;
		$this->onlyFilter = in_array($onlyFilter, array('yes', 'on', '1', 'true'));

		return true;
	}

	/**
	 * Method to get the id used for the field input tag.
	 *
	 * @param   string  $fieldId    The field element id.
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The id to be used for the field input tag.
	 *
	 * @since   2.0
	 */
	protected function getId($fieldId, $fieldName)
	{
		$id = '';

		// If the field is in a group add the group control to the field id.

		if ($this->group)
		{
			// If we already have an id segment add the group control as another level.

			if ($id)
			{
				$id .= '_' . str_replace('.', '_', $this->group);
			}
			else
			{
				$id .= str_replace('.', '_', $this->group);
			}
		}

		// If we already have an id segment add the field id/name as another level.

		if ($id)
		{
			$id .= '_' . ($fieldId ? $fieldId : $fieldName);
		}
		else
		{
			$id .= ($fieldId ? $fieldId : $fieldName);
		}

		// Clean up any invalid characters.
		$id = preg_replace('#\W#', '_', $id);

		return $id;
	}

	/**
	 * Method to get the name used for the field input tag.
	 *
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The name to be used for the field input tag.
	 *
	 * @since   2.0
	 */
	protected function getName($fieldName)
	{
		$name = '';

		// If the field is in a group add the group control to the field name.

		if ($this->group)
		{
			// If we already have a name segment add the group control as another level.
			$groups = explode('.', $this->group);

			if ($name)
			{
				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
			else
			{
				$name .= array_shift($groups);

				foreach ($groups as $group)
				{
					$name .= '[' . $group . ']';
				}
			}
		}

		// If we already have a name segment add the field name as another level.

		if ($name)
		{
			$name .= '[' . $fieldName . ']';
		}
		else
		{
			$name .= $fieldName;
		}

		return $name;
	}

	/**
	 * Method to get the field name used.
	 *
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The field name
	 *
	 * @since   2.0
	 */
	protected function getFieldName($fieldName)
	{
		return $fieldName;
	}

	/**
	 * Method to get the field label.
	 *
	 * @return  string  The field label.
	 *
	 * @since   2.0
	 */
	protected function getLabel()
	{
		// Get the label text from the XML element, defaulting to the element name.
		$title = $this->element['label'] ? (string) $this->element['label'] : '';

		if (empty($title))
		{
			$viewObject = $this->form->getView();
			$viewName = $viewObject->getName();
			$componentName = $viewObject->getContainer()->componentName;

			$title = $componentName . '_' .
				$this->form->getModel()->getContainer()->inflector->pluralize($viewName) . '_FIELD_' .
				(string) $this->element['name'];
			$title = strtoupper($title);
			$result = \JText::_($title);

			if ($result === $title)
			{
				$title = ucfirst((string) $this->element['name']);
			}
		}

		return $title;
	}

	/**
	 * Get the filter value for this header field
	 *
	 * @return  mixed  The filter value
	 */
	protected function getValue()
	{
		$model = $this->form->getModel();

		return $model->getState($this->filterSource);
	}

	/**
	 * Return the key of the filter value in the model state or, if it's not set,
	 * the name of the field.
	 *
	 * @param   string  $filterSource  The filter source value to return
	 *
	 * @return  string
	 */
	protected function getFilterSource($filterSource)
	{
		if ($filterSource)
		{
			return $filterSource;
		}
		else
		{
			return $this->name;
		}
	}

	/**
	 * Return the name of the filter field
	 *
	 * @param   string  $filterFieldName  The filter field name source value to return
	 *
	 * @return  string
	 */
	protected function getFilterFieldName($filterFieldName)
	{
		if ($filterFieldName)
		{
			return $filterFieldName;
		}
		else
		{
			return $this->filterSource;
		}
	}

	/**
	 * Is this a sortable field?
	 *
	 * @return  boolean  True if it's sortable
	 */
	protected function getSortable()
	{
		$sortable = ($this->element['sortable'] != 'false');

		if ($sortable)
		{
			if (empty($this->header))
			{
				$this->header = $this->onlyFilter ? '' : $this->getHeader();
			}

			$sortable = !empty($this->header);
		}

		return $sortable;
	}

	/**
	 * Returns the HTML for the header row, or null if this element should
	 * render no header element
	 *
	 * @return  string|null  HTML code or null if nothing is to be rendered
	 *
	 * @since 2.0
	 */
	protected function getHeader()
	{
		return null;
	}

	/**
	 * Returns the HTML for a text filter to be rendered in the filter row,
	 * or null if this element should render no text input filter.
	 *
	 * @return  string|null  HTML code or null if nothing is to be rendered
	 *
	 * @since 2.0
	 */
	protected function getFilter()
	{
		return null;
	}

	/**
	 * Returns the HTML for the buttons to be rendered in the filter row,
	 * next to the text input filter, or null if this element should render no
	 * text input filter buttons.
	 *
	 * @return  string|null  HTML code or null if nothing is to be rendered
	 *
	 * @since 2.0
	 */
	protected function getButtons()
	{
		return null;
	}

	/**
	 * Returns the JHtml options for a drop-down filter. Do not include an
	 * empty option, it is added automatically.
	 *
	 * @return  array  The JHtml options for a drop-down filter
	 *
	 * @since 2.0
	 */
	protected function getOptions()
	{
		return array();
	}
}
