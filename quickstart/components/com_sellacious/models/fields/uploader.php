<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('JPATH_BASE') or die;

/**
 * Form Field to provide an input field for file uploads
 *
 * @property-read  mixed             $value
 * @property-read  SimpleXMLElement  $element
 * @property-read  string            $id
 * @property-read  string            $name
 * @property-read  string            $tableName
 * @property-read  string            $context
 * @property-read  int               $recordId
 * @property-read  int               $uploadLimit
 * @property-read  int               $maxSize
 * @property-read  string[]          $extensions
 *
 * @since   1.6.0
 */
class JFormFieldUploader extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since   1.6.0
	 */
	public $type = 'Uploader';

	/**
	 * The target table name for the uploaded file, only needed if model
	 * is not supposed to pass the current value
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $tableName;

	/**
	 * The target table column name for the uploaded file,
	 * only needed if model is not supposed to pass the current value
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $context;

	/**
	 * The target record id in the <var>$tableName</var> above,
	 * only needed if model is not supposed to pass the current value
	 *
	 * @var    int
	 *
	 * @since  1.6.0
	 */
	protected $recordId;

	/**
	 * The maximum number of files to allow for upload
	 *
	 * @var    int
	 *
	 * @since  1.6.0
	 */
	protected $uploadLimit;

	/**
	 * The maximum file size of files to allow for upload. Size units like: M, MB, MiB etc. are allowed
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $maxSize;

	/**
	 * Whether to show publish/unpublish buttons @todo
	 *
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $showPublish = false;

	/**
	 * Whether to show an input to rename the uploaded file @todo
	 *
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $showRename = true;

	/**
	 * Whether to show remove file button @todo
	 *
	 * @var    bool
	 *
	 * @since  1.6.0
	 */
	protected $showRemove = true;

	/**
	 * The allowed file extensions like: "jpg,pdf,csv"
	 *
	 * @var    string[]
	 *
	 * @since  1.6.0
	 */
	protected $extensions;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 *
	 * @since  1.6.0
	 */
	protected $layout = 'sellacious.formfield.uploader.default';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  bool  True on success
	 *
	 * @since   1.6.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		if (!parent::setup($element, $value, $group))
		{
			return false;
		}

		$this->__set('tableName', (string) $this->element['tableName']);
		$this->__set('context', (string) $this->element['context']);
		$this->__set('recordId', (int) $this->element['recordId']);
		$this->__set('uploadLimit', (int) $this->element['uploadLimit']);
		$this->__set('maxSize', (string) $this->element['maxSize']);
		$this->__set('showPublish', (string) $this->element['showPublish'] == 'true');
		$this->__set('showRename', (string) $this->element['showRename'] == 'true');
		$this->__set('showRemove', (string) $this->element['showRemove'] == 'true');
		$this->__set('extensions', (string) $this->element['extensions']);

		return true;
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'tableName':
			case 'context':
				$this->$name = (string) $value;
				break;
			case 'recordId':
			case 'uploadLimit':
				$this->$name = (int) $value;
				break;
			case 'maxSize':
				// Max size is always throttled to server ini settings if exceeded
				$value       = JUtility::getMaxUploadSize($value);
				$this->$name = $value;
				break;
			case 'showPublish':
			case 'showRename':
			case 'showRemove':
				$this->$name = (bool) $value;
				break;
			case 'extensions':
				if (is_array($value))
				{
					$this->$name = $value;
				}
				elseif (is_string($value))
				{
					$this->$name = explode(',', $value);
				}
				else
				{
					$this->$name = array();
				}
				break;
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   1.6.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'tableName':
			case 'context':
			case 'recordId':
			case 'uploadLimit':
			case 'maxSize':
			case 'showPublish':
			case 'showRename':
			case 'showRemove':
			case 'extensions':
				return $this->$name;
			default:
				return parent::__get($name);
		}
	}

	/**
	 * Method to get the field input markup for the uploader field
	 *
	 * The field does not include an upload mechanism. It must be implemented by the appropriate model when form is submitted.
	 * The table/context/record_id are to load preview of existing files only. These are not submitted along the files.
	 *
	 * @return  string  The field input markup
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	protected function getInput()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('a.id, a.path, a.state, a.original_name AS title, a.doc_type, a.doc_reference')
			->from($db->qn('#__sellacious_media', 'a'))
			->where('a.table_name = ' . $db->q($this->tableName))
			->where('a.context = ' . $db->q($this->context))
			->where('a.record_id = ' . (int) ($this->recordId));

		$this->value = (array) $db->setQuery($query)->loadObjectList();

		JHtml::_('jquery.framework');
		JHtml::_('script', 'sellacious/field.uploader.js', false, true);

		$app = JFactory::getApplication();

		if ($app->isClient('site'))
		{
			JHtml::_('stylesheet', 'sellacious/fe.field.uploader.css', null, true);
		}
		else
		{
			JHtml::_('stylesheet', 'sellacious/field.uploader.css', null, true);
		}

		JText::script('LIB_SELLACIOUS_ERROR_FILE_UPLOAD_TYPE_NOT_ALLOWED');
		JText::script('LIB_SELLACIOUS_ERROR_FILE_UPLOAD_SIZE_EXCEEDED');

		return JLayoutHelper::render($this->layout, $this, '', array('client' => 2, 'debug' => false));
	}
}
