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

use Sellacious\Media\MediaHelper;

defined('_JEXEC') or die;

/**
 * Sellacious uploaded file object.
 *
 * @since  1.5.2
 */
class UploadedFile
{
	/**
	 * Record id in media table
	 *
	 * @var  int
	 *
	 * @since  1.5.2
	 */
	public $id = null;

	/**
	 * Record reference table name in media table
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $table_name;

	/**
	 * Published state of the file after it is saved in media table
	 *
	 * @var  int
	 *
	 * @since  1.5.3
	 */
	public $state = 0;

	/**
	 * Record reference field context in media table
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $context;

	/**
	 * Record reference id in media table
	 *
	 * @var  int
	 *
	 * @since  1.5.2
	 */
	public $record_id;

	/**
	 * Original filename
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $name;

	/**
	 * MIME type
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $type;

	/**
	 * PHP generated temporary path
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $tmp_name;

	/**
	 * PHP generated upload error code
	 *
	 * @var  int
	 *
	 * @see   http://php.net/manual/en/features.file-upload.errors.php
	 *
	 * @since  1.5.2
	 */
	public $error = 0;

	/**
	 * File size in number of bytes
	 *
	 * @var  int
	 *
	 * @since  1.5.2
	 */
	public $size = 0;

	/**
	 * File name extension (e.g. - jpg)
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $extension;

	/**
	 * The file path after it is moved to user defined location.
	 * This can be a relative path or an absolute path depending on (@see  $relative}
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $path;

	/**
	 * The absolute file path after it is moved to user defined location
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $location;

	/**
	 * Flag to indicate whether the $path is relative to site root
	 *
	 * @var  bool
	 *
	 * @since  1.5.2
	 */
	public $relative = true;

	/**
	 * Flag to indicate whether the file has passed safe file check
	 *
	 * @var  bool
	 *
	 * @since  1.5.3
	 */
	public $safe = false;

	/**
	 * Flag to indicate whether the $path is relative to site root
	 *
	 * @var  bool
	 *
	 * @since  1.5.2
	 */
	public $valid = false;

	/**
	 * Any relevant error or informational message pertaining to file upload
	 *
	 * @var  string
	 *
	 * @since  1.5.2
	 */
	public $message = null;

	/**
	 * Flag to indicate whether the file upload was processed and the file was moved from temporary path
	 *
	 * @var  bool
	 *
	 * @since  1.5.2
	 */
	public $uploaded = false;

	/**
	 * Method to move the uploaded file to a specific filesystem location.
	 * If the file was previously moved (uploaded) then it will be moved from its current location to the new specified location.
	 *
	 * NOTE: The file extension is always preserved.
	 *
	 * @param   string  $path      The destination folder path for the file(s)
	 * @param   string  $filename  A pattern for the new filename. Short-code: @@ = Auto-generated string, * = Original name.
	 * @param   bool    $relative  Whether the new path is site root relative (TRUE), or absolute filesystem path (FALSE)
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @since   1.5.2
	 */
	public function moveTo($path, $filename, $relative)
	{
		// We skip the already invalidated files as we have been called to process valid files only.
		if (!$this->valid)
		{
			return false;
		}

		$name = $filename ?: '*';

		if (stripos($name, '@@') !== false)
		{
			$uid      = \JFactory::getUser()->id;
			$autoName = uniqid($uid . '_');

			$name = str_ireplace('@@', $autoName, $name);
		}

		if (stripos($name, '*') !== false)
		{
			$basename = basename($this->name, '.' . $this->extension);
			$name     = str_ireplace('*', $basename, $name);
		}

		$nPath    = MediaHelper::sanitize($path . '/' . $name . '.' . $this->extension);
		$location = MediaHelper::sanitize($relative ? JPATH_SITE . '/' . $nPath : $nPath);

		jimport('joomla.filesystem.file');

		/**
		 * Once we move/upload this one, this the file will no longer be available at tmp_path.
		 * We'd need to pick from new location if so.
		 */
		if ($this->uploaded)
		{
			$moved = \JFile::move($this->location, $location);
		}
		else
		{
			// Safe file check has been performed on this file, if we passed there let it pass again.
			$moved = \JFile::upload($this->tmp_name, $location, false, true);
		}

		// Update references
		if ($moved)
		{
			$this->uploaded = true;
			$this->relative = $relative;
			$this->path     = $nPath;
			$this->location = $location;
		}

		return $moved;
	}

	/**
	 * Method to move all selected uploaded files to a specific filesystem location.
	 * NOTE: The file extension is always preserved.
	 *
	 * @param   string  $tableName  The table name to which this file is linked
	 * @param   string  $context    The media context for this file within the linked table
	 * @param   int     $recordId   The record id in the linked table to which this file is associated
	 *
	 * @return  bool
	 *
	 * @throws  \Exception
	 *
	 * @see     load()
	 *
	 * @since   1.6.0
	 */
	public function saveTo($tableName, $context, $recordId)
	{
		$this->id         = null;
		$this->table_name = $tableName;
		$this->context    = $context;
		$this->record_id  = $recordId;

		// We skip the already invalidated files as we have been called to process valid files only.
		if (!$this->uploaded)
		{
			throw new \Exception(\JText::_('LIB_SELLACIOUS_ERROR_UPLOAD_SAVE_UPLOAD_FAILED'));
		}

		$name   = strlen($this->name) > 150 ? substr($this->name, 0, 124) . '--' . substr($this->name, -124) : $this->name;
		$record = array(
			'id'            => null,
			'table_name'    => $tableName,
			'record_id'     => $recordId,
			'context'       => $context,
			'path'          => $this->path,
			'original_name' => $name,
			'type'          => $this->type,
			'size'          => $this->size,
			'doc_type'      => null,
			'doc_reference' => null,
			'protected'     => 0,
			'state'         => 1,
		);

		$table = \SellaciousTable::getInstance('Media');
		$table->bind($record);
		$table->check();

		$saved = $table->store();

		$this->id    = $table->get('id');
		$this->state = $table->get('state');

		return $saved;
	}

	/**
	 * Run when writing data to inaccessible members. Used to prevent arbitrary property write
	 *
	 * @param   $name   string  Property name
	 * @param   $value  mixed   New property value
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	public function __set($name, $value)
	{
		trigger_error('Trying to set value of undefined property "' . $name . '" of ' . __CLASS__);
	}
}
