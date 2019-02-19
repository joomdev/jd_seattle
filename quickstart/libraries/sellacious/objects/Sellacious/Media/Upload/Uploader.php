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
namespace Sellacious\Media\Upload;

use Joomla\CMS\Filter\InputFilter;
use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\MediaHelper;

defined('_JEXEC') or die;

/**
 * Sellacious media upload handler object.
 *
 * @since  1.5.2
 */
class Uploader
{
	/**
	 * Allow only strictly safe files
	 *
	 * @since   1.5.3
	 */
	const UNSAFE_OPT_ALLOW_NONE = 0;

	/**
	 * Allow PHP tag '<?php' in uploaded file's content
	 *
	 * @since   1.5.3
	 */
	const UNSAFE_OPT_ALLOW_PHP_TAG_IN_CONTENT = 1;

	/**
	 * Allow PHP Short tag '<?' in uploaded file's content
	 *
	 * @since   1.5.3
	 */
	const UNSAFE_OPT_ALLOW_SHORT_TAG_IN_CONTENT = 2;

	/**
	 * Allow unsafe file extensions inside the file content for archive type uploads (e.g. - zip, tar, gz etc.)
	 *
	 * @since   1.5.3
	 */
	const UNSAFE_OPT_ALLOW_FOBIDDEN_EXT_IN_CONTENT = 4;

	/**
	 * Allow All files without performing any safety checks
	 *
	 * @since   1.5.3
	 */
	const UNSAFE_OPT_ALLOW_ALL = 4096;

	/**
	 * The list of files uploaded in the session, with all relevant upload metadata
	 *
	 * @var   UploadedFile[]
	 *
	 * @since  1.5.2
	 */
	protected static $files = null;

	/**
	 * The list of files selected in the upload batch, with all relevant upload metadata
	 *
	 * @var   UploadedFile[]
	 *
	 * @since  1.5.2
	 */
	protected $selected = null;

	/**
	 * Restrict file mime types allowed to upload such as - 'image/jpeg', 'application/zip'
	 *
	 * @var   string[]
	 *
	 * @since  1.5.2
	 */
	protected $mime = array();

	/**
	 * Restrict file extensions allowed to upload such as - 'jpg', 'zip'
	 *
	 * @var   string[]
	 *
	 * @since  1.5.2
	 */
	protected $ext = array();

	/**
	 * Flag to indicate whether to ignore any upload error and continue processing the file(s) further
	 *
	 * @var   bool
	 *
	 * @since  1.5.2
	 */
	protected $ignore = false;

	/**
	 * Flag to indicate whether to allow any unsafe file(s) for upload, such as files containing PHP code, Joomla extension etc.
	 *
	 * @var   int
	 *
	 * @see   UNSAFE_OPT_* Constants in this class
	 *
	 * @since  1.5.3
	 */
	protected $unsafe = 0;

	/**
	 * Constructor
	 *
	 * @param   string[]  $extensions  The allowed file extensions for upload
	 * @param   bool      $ignore
	 *
	 * @since  1.5.2
	 */
	public function __construct($extensions, $ignore = false)
	{
		$this->addExt($extensions)->setIgnore($ignore);
	}

	/**
	 * Short circuit method to select-move-save the media files all in a single call.
	 *
	 * @param   string  $control    Variable name from the HTML form. Returns all uploaded files if blank is given.
	 *                              Example: The name "jform[picture]" should be given as "jform.picture"
	 * @param   string  $path       The destination folder path for the file(s)
	 * @param   string  $filename   A pattern for the new filename. Short-code: @@ = Auto-generated string, * = Original name.
	 * @param   bool    $relative   Whether the new path is site root relative (TRUE), or absolute filesystem path (FALSE)
	 * @param   int     $limit      Maximum number of files to allow in this batch
	 * @param   string  $tableName  The table name to which this file is linked
	 * @param   string  $context    The media context for this file within the linked table
	 * @param   int     $recordId   The record id in the linked table to which this file is associated
	 *
	 * @return  UploadedFile[]  The list of matching files
	 *
	 * @throws  \Exception
	 *
	 * @since  1.5.2
	 */
	public function upload($control, $path, $filename = '*', $relative = true, $limit = null, $tableName = null, $context = null, $recordId = null)
	{
		$this->select($control, $limit);

		$this->moveTo($path, $filename, $relative);

		if ($tableName && $context && $recordId)
		{
			$this->saveTo($tableName, $context, $recordId);
		}

		return $this->getSelected();
	}

	/**
	 * Select the file(s) matching the given control name for upload
	 *
	 * @param   string  $control  Variable name from the HTML form. Returns all uploaded files if blank is given.
	 *                            Example: The name "jform[picture]" should be given as "jform.picture"
	 * @param   int     $limit    Maximum number of files to allow in this batch
	 *
	 * @return  UploadedFile[]  The list of matching files
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public function select($control, $limit = null)
	{
		$this->selected = array();

		if (!$this->mime && !$this->ext)
		{
			throw new \Exception(\JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_FILE_TYPE_FILTER_NOT_SET'));
		}

		$files = $this->getFiles($control);

		// We count also the files with upload error because a user attempt for upload should not be ignored silently.
		if ($limit && count($files) > $limit)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_ERROR_UPLOAD_MAX_UPLOAD_COUNT_EXCEEDED_N', (int) $limit));
		}

		// Check file extension and mime restrictions.
		foreach ($files as $file)
		{
			if ($file->valid)
			{
				if ($this->ext && !in_array($file->extension, $this->ext))
				{
					$file->valid   = false;
					$file->message = \JText::sprintf('LIB_SELLACIOUS_ERROR_UPLOAD_NOT_ALLOWED_X_EXTENSION', $file->extension);
				}
				elseif ($this->mime && !in_array($file->type, $this->mime))
				{
					$file->valid   = false;
					$file->message = \JText::sprintf('LIB_SELLACIOUS_ERROR_UPLOAD_NOT_ALLOWED_X_FORMAT', $file->type);
				}
			}

			if (!$file->valid && !$this->ignore)
			{
				throw new \Exception($file->message);
			}
		}

		$this->selected = $files;

		return $this->selected;
	}

	/**
	 * Method to move all selected uploaded files to a specific filesystem location.
	 * NOTE: The file extension is always preserved.
	 *
	 * @param   string  $path      The destination folder path for the file(s)
	 * @param   string  $filename  A pattern for the new filename. Short-code: @@ = Auto-generated string, * = Original name.
	 * @param   bool    $relative  Whether the new path is site root relative (TRUE), or absolute filesystem path (FALSE)
	 *
	 * @return  UploadedFile[]  The list of files processed
	 *
	 * @throws  \Exception
	 *
	 * @see     load()
	 *
	 * @since   1.5.2
	 */
	public function moveTo($path, $filename = '*', $relative = true)
	{
		foreach ($this->selected as $file)
		{
			if (!$file->moveTo($path, $filename, $relative))
			{
				if ($this->ignore)
				{
					// Sure to mark invalid?
					$file->valid   = false;
					$file->message = \JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_MOVE_FAILED');
				}
				else
				{
					throw new \Exception(\JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_MOVE_FAILED'));
				}
			}
		}

		return $this->selected;
	}

	/**
	 * Method to move all selected uploaded files to a specific filesystem location.
	 * NOTE: The file extension is always preserved.
	 *
	 * @param   string  $tableName  The table name to which this file is linked
	 * @param   string  $context    The media context for this file within the linked table
	 * @param   int     $recordId   The record id in the linked table to which this file is associated
	 *
	 * @return  UploadedFile[]  The list of files processed
	 *
	 * @throws  \Exception
	 *
	 * @see     load()
	 *
	 * @since   1.5.2
	 */
	public function saveTo($tableName, $context, $recordId)
	{
		foreach ($this->selected as $file)
		{
			try
			{
				$file->saveTo($tableName, $context, $recordId);
			}
			catch (\Exception $e)
			{
				if ($this->ignore)
				{
					$file->valid   = false;
					$file->message = \JText::sprintf('LIB_SELLACIOUS_ERROR_UPLOAD_SAVE_ERROR', $e->getMessage());
				}
				else
				{
					throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_ERROR_UPLOAD_SAVE_ERROR', $e->getMessage()));
				}
			}
		}

		return $this->selected;
	}

	/**
	 * Method to get the list of all selected uploaded files. This can be called at any stage after 'select' has been done.
	 *
	 * @return  UploadedFile[]  The list of files selected
	 *
	 * @see     select(), moveTo(), saveTo()
	 *
	 * @since   1.5.2
	 */
	public function getSelected()
	{
		return $this->selected;
	}

	/**
	 * Set the ignore flag for this instance
	 *
	 * @param   bool  $ignore  The new flag value
	 *
	 * @return  $this
	 *
	 * @since   1.5.2
	 */
	public function setIgnore($ignore)
	{
		$this->ignore = (bool) $ignore;

		return $this;
	}

	/**
	 * Set the unsafe flag for this instance
	 *
	 * @param   int  $value  The new flag value
	 *
	 * @return  $this
	 *
	 * @since   1.5.3
	 */
	public function allowUnsafe($value)
	{
		$this->unsafe = $value;

		return $this;
	}

	/**
	 * Add or replace an allowed mime type for uploads
	 *
	 * @param   string|string[]  $mime   The mime type(s) to add
	 * @param   bool             $clear  Whether to clear previously added mime types
	 *
	 * @return  $this
	 *
	 * @since  1.5.2
	 */
	public function addMime($mime, $clear = false)
	{
		if ($clear)
		{
			$this->mime = array();
		}

		// MIME detection is not working properly, hence disable it for now. Uncomment following lines to enable.
		/*
		foreach ((array) $mime as $m)
		{
			$this->mime[] = $m;
		}
		*/

		return $this;
	}

	/**
	 * Add or replace an allowed file extension for uploads
	 *
	 * @param   string|string[]  $ext    The extension(s) to add
	 * @param   bool             $clear  Whether to clear previously added extension
	 *
	 * @return  $this
	 *
	 * @since  1.5.2
	 */
	public function addExt($ext, $clear = false)
	{
		if ($clear)
		{
			$this->ext = array();
		}

		foreach ((array) $ext as $e)
		{
			$this->ext[] = ltrim($e, '.');
		}

		return $this;
	}

	/**
	 * Add or replace an allowed file category for uploads
	 *
	 * @param   string|string[]  $type   The category or categories to add
	 * @param   bool             $clear  Whether to clear previously added extension and mime
	 *
	 * @return  $this
	 *
	 * @throws  \Exception
	 *
	 * @since  1.5.2
	 */
	public function addType($type, $clear = false)
	{
		try
		{
			list($mime, $ext) = MediaHelper::getTypeInfo($type);

			$this->addExt($ext, $clear);
			$this->addMime($mime, $clear);
		}
		catch (\Exception $e)
		{
			throw new \Exception(\JText::sprintf('LIB_SELLACIOUS_MEDIA_ERROR_DETERMINING_CATEGORY_MIME_EXT', $e->getMessage()));
		}

		return $this;
	}

	/**
	 * Method to get list of all uploaded files in current session.
	 *
	 * @param   string  $control  Variable name from the HTML form. Returns all uploaded files if blank is given.
	 *                            Example: The name "jform[picture]" should be given as "jform.picture"
	 *
	 * @return  UploadedFile[]  The property <var>$this->files</var> will be populated with all uploaded files in the session.
	 *
	 * @since   1.5.2
	 */
	public function getFiles($control)
	{
		// Preload if not already loaded
		if (static::$files === null)
		{
			$this->load();
		}

		// Early exit if no uploaded files
		if (!static::$files)
		{
			return array();
		}

		// Prepare filtered list by input control
		$files = array();

		foreach (static::$files as $key => $file)
		{
			// We have a match if we are having exact control name or a control group
			if ($control === null)
			{
				$files[$key] = $file;
			}
			elseif ($key == $control || strpos($key, $control . '.') === 0)
			{
				$files[substr($key, strlen($control) + 1)] = $file;
			}
		}

		return $files;
	}

	/**
	 * Method to populate the list of all uploaded files in current session.
	 * We must make sure this is executed only once per session for performance reasons.
	 *
	 * NOTE: Only generic upload errors are checked and marked. Other validations must be performed separately.
	 *
	 * @return  void  The property <var>$this->files</var> will be populated
	 *
	 * @since   1.5.2
	 */
	protected function load()
	{
		try
		{
			$app      = \JFactory::getApplication();
			$controls = array_keys($_FILES);

			static::$files = array();

			foreach ($controls as $control)
			{
				$file = $app->input->files->get($control, null, 'raw');

				$this->push($file, $control);
			}
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * Push an uploaded file meta-data item into the local storage for further usage.
	 *
	 * NOTE: Only generic upload errors are checked and marked. Other validations must be performed separately.
	 *
	 * @param   array   $item    The file upload meta data or array of such meta data any level down
	 * @param   string  $prefix  The input name level
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function push($item, $prefix)
	{
		if (is_array($item))
		{
			if (!isset($item['name'], $item['type'], $item['tmp_name'], $item['error'], $item['size']))
			{
				// If this is not a file meta array, it must be a list of such meta arrays
				foreach ($item as $key => $file)
				{
					$this->push($file, $prefix . '.' . $key);
				}
			}
			elseif (is_numeric($item['size']) && $item['error'] != UPLOAD_ERR_NO_FILE)
			{
				$valid = false;

				// If a file was not uploaded we silently skip that item
				switch ($item['error'])
				{
					case UPLOAD_ERR_OK:
						$valid   = true;
						$message = null;
						break;

					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$message = \JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_FILE_TOO_LARGE');
						break;

					case UPLOAD_ERR_PARTIAL:
						$message = \JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_FILE_PARTIAL');
						break;

					case UPLOAD_ERR_NO_TMP_DIR:
					case UPLOAD_ERR_CANT_WRITE:
					case UPLOAD_ERR_EXTENSION:
						$message = \JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_FILE_WRITE_ERROR');
						break;

					default:
						$message = \JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_FILE_UNKNOWN');
						break;
				}

				// Check for safe file security, even if all insecure files are allowed. Just narrow down constraints if requested.
				$opts = array();

				if ($this->unsafe & static::UNSAFE_OPT_ALLOW_FOBIDDEN_EXT_IN_CONTENT)
				{
					$opts['fobidden_ext_in_content'] = false;
				}

				if ($this->unsafe & static::UNSAFE_OPT_ALLOW_SHORT_TAG_IN_CONTENT)
				{
					$opts['shorttag_in_content'] = false;
				}

				if ($this->unsafe & static::UNSAFE_OPT_ALLOW_PHP_TAG_IN_CONTENT)
				{
					$opts['php_tag_in_content'] = false;
				}

				$isSafeFile = InputFilter::isSafeFile($item, $opts);

				if ($isSafeFile || ($this->unsafe & static::UNSAFE_OPT_ALLOW_ALL))
				{
					$item['safe']      = $isSafeFile;
					$item['valid']     = $valid;
					$item['message']   = $message;
					$item['extension'] = strtolower(MediaHelper::getExtension($item['name']));

					static::$files[$prefix] = ArrayHelper::toObject($item, __NAMESPACE__ . '\UploadedFile');
				}
			}
		}
	}
}
