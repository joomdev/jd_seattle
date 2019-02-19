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
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Sellacious\Media\MediaHelper;
use Sellacious\Media\Upload\Uploader;

/**
 * Class SellaciousHelperMedia
 *
 * @since   1.0.0
 */
class SellaciousHelperMedia extends SellaciousHelperBase
{
	/**
	 * Upload base directory relative to site root.
	 * After upload the path will always be site root relative
	 * NOTE: It is recommended that this does not include a leading or trailing slash
	 *
	 * @var  string
	 *
	 * @since   1.2.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected $baseDir = 'images/com_sellacious';

	/**
	 * @var  array  Upload options
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected $options = array();

	/**
	 * @var int  count of successful uploads
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected $count = 0;

	/**
	 * Upload the files from user submitted form to selected context path
	 *
	 * @param   string $folder      (required) The folder where the uploaded files should be moved/saved.
	 *                              Remember that the path will be used as *relative* to site root.
	 * @param   mixed  $controls    (optional) variable name(s) from the HTML form. Processes all uploaded
	 *                              files if blank is given. The name "jform[picture]" should be given as
	 *                              "jform.picture"
	 *
	 * @param   array  $options     Valid options are:
	 *
	 *                 type:       (optional) Restrict types of file allowed to upload such as -
	 *                             image, video, audio, archive, script, document, presentation, binary etc.
	 *                 filename:   (optional) Destination file name. Auto-generated if not provided
	 *                 prefix:     (optional) Prefix to add to the filename
	 *                 ignore:     (optional) Whether to ignore errors and continue to next file if any (def = false)
	 *                 limit:      (optional) Maximum number of files to be allowed for upload
	 *                 table:      (required if saving in media table) Name of the table for which this media is
	 *                             , without table_prefix (e.g. - write 'jos_users' as 'users')
	 *                 record:     (required if table name given) Id of the record in the above table for which this media is associated
	 *                 context:    (optional) Additional context identifier for this media (optional)
	 *
	 * @return  mixed  List of file record objects.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @see     getAllowedTypes(), createPath()
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	public function upload($folder, $controls = null, $options = array())
	{
		$options['type']     = isset($options['type']) ? $options['type'] : null;
		$options['filename'] = isset($options['filename']) ? $options['filename'] : null;
		$options['prefix']   = isset($options['prefix']) ? $options['prefix'] : '';
		$options['ignore']   = isset($options['ignore']) ? $options['ignore'] : false;
		$options['limit']    = isset($options['limit']) ? $options['limit'] : false;
		$options['rename']   = isset($options['rename']) ? $options['rename'] : false;

		$options['table']   = isset($options['table']) ? $options['table'] : null;
		$options['record']  = isset($options['record']) ? $options['record'] : null;
		$options['context'] = isset($options['context']) ? $options['context'] : null;

		$options['type']   = $this->getAllowedTypes($options['type']);
		$options['folder'] = $this->createPath($folder);

		// Make sure of pre-requisites
		if (empty($options['type']['mimes']) || empty($options['type']['exts']) || empty($options['folder']))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_UPLOAD_NOT_ALLOWED_AT_ALL'));
		}

		$this->count   = 0;
		$this->options = $options;

		// Get files to upload and start uploading
		$vars    = $this->getUploads($controls);
		$uploads = $this->processUploads($vars);

		// Finally return the input control array
		return $uploads;
	}

	/**
	 * Handle the upload for the record when the response is from the Uploader form field
	 *
	 * @param   string    $control     The form control
	 * @param   string    $tableName   The table name for media reference
	 * @param   string    $context     The field context for media reference
	 * @param   int       $recordId    The record id for media reference
	 * @param   string[]  $extensions  The allowed file extensions
	 * @param   array     $options     The remove/rename options as an object array / 2d array
	 *
	 * @return  bool
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function handleUploader($control, $tableName, $context, $recordId, $extensions, $options)
	{
		$uploader = new Uploader($extensions);
		$uploader->select($control);

		$files = $uploader->getSelected();

		if (is_array($options) || is_object($options))
		{
			foreach ($options as $idx => $image)
			{
				$image = (object) $image;

				if (isset($image->id) && isset($image->remove))
				{
					// Todo: Match reference
					$this->helper->media->remove($image->id);
				}
				elseif (isset($image->title) && trim($image->title) !== '')
				{
					if (isset($image->id))
					{
						// Todo: Match reference, and preserve file extension
						$mo = (object) array('id' => $image->id, 'original_name' => $image->title);

						$this->db->updateObject('#__sellacious_media', $mo, array('id'));
					}
					elseif (isset($files[$idx . '.file']))
					{
						$files[$idx . '.file']->name = $image->title;
					}
				}
			}
		}

		$pathName = strpos($tableName, '/') === false ? 'com_sellacious/' . $tableName : $tableName;

		foreach ($files as $index => $file)
		{
			$file->moveTo('images/' . $pathName . '/' . $context . '/' . $recordId, '@@-*', true);
			$file->saveTo($tableName, $context, $recordId);
		}

		return true;
	}

	/**
	 * Method to set allowed file types directly using acronyms.
	 * Allowed MIMEs and Extensions list is automatically loaded from the media-mime table.
	 *
	 * @param   mixed $types   Array or comma delimited string of file type acronyms
	 *                         such as - image, video, audio, archive, script, document, presentation, binary etc.
	 *
	 * @return mixed
	 * @throws Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\MediaHelper
	 */
	protected function getAllowedTypes($types)
	{
		$types = is_array($types) ? $types : (empty($types) ? array() : explode(',', $types));

		$db    = $this->db;
		$query = $db->getQuery(true);

		$query->select('a.mime, a.extension')->from('#__sellacious_mimes a')->where('a.state = 1');

		if (count($types))
		{
			$query->where('a.category IN (' . implode(', ', $db->q($types)) . ')');
		}

		$db->setQuery($query);

		$result = $db->loadObjectList();
		$mimes  = ArrayHelper::getColumn($result, 'mime');
		$exts   = ArrayHelper::getColumn($result, 'extension');

		return compact('mimes', 'exts');
	}

	/**
	 * Get the base folder path for default media location for sellacious, includes a leading slash
	 *
	 * @param   string  $extension  A '/' separated path to be appended to the base dir (optional)
	 *
	 * @return  string
	 *
	 * @since   1.2.0
	 */
	public function getBaseDir($extension = '')
	{
		return $this->sanitize("/$this->baseDir/$extension");
	}

	/**
	 * Method to create folder path where the uploaded file(s) are to be saved.
	 * Path given with a leading tilde (~) are relative to commons base dir
	 * Path given with a leading slash (/) are relative to site root
	 * Path given with leading text "tmp/" are saved in site's temporary folder
	 * All Other paths are assumed to be relative to images folder
	 *
	 * @param   string  $folder  Path name
	 *
	 * @return  mixed
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function createPath($folder)
	{
		// Handle absolute path
		if (substr($folder, 0, 1) == '/')
		{
			$path = $folder;
		}
		// Handle temporary path
		elseif (substr($folder, 0, 4) == 'tmp/')
		{
			$path = '/tmp/' . substr($folder, 3);
		}
		// Handle use base dir path (recommended)
		elseif (substr($folder, 0, 1) == '~')
		{
			$path = '/' . $this->baseDir . '/' . substr($folder, 1);
		}
		// Handle media destination path
		else
		{
			$path = '/images/' . $folder;
		}

		$path = $this->sanitize($path, true);

		// Attempt to create path if not already exists.
		if (!file_exists(JPATH_SITE . $path))
		{
			jimport('joomla.filesystem.folder');

			if (!JFolder::create(JPATH_SITE . $path, 0755))
			{
				throw new Exception(JText::sprintf('COM_SELLACIOUS_CREATE_DIRECTORY_FAILED', $path));
			}
		}

		return $path;
	}

	/**
	 * Method to get list of all uploaded files that are parts of given controls list
	 *
	 * @param   string $controls Form control name, process all uploaded files if blank
	 *
	 * @return  array  list of all files
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected function getUploads($controls)
	{
		$array = array();

		if (empty($controls))
		{
			$controls = array_keys($_FILES);
		}

		if (is_array($controls))
		{
			foreach ($controls as $control)
			{
				$item  = $this->getUploads($control);
				$array = array_merge($array, (array) $item);
			}
		}
		else
		{
			$split = explode('.', $controls);
			$app   = JFactory::getApplication();
			$files = $app->input->files->get(array_shift($split), null);

			$ref = &$array;

			// Until we get a specific key, we move on to it, discarding others.
			while (!is_null($key = array_shift($split)))
			{
				$files = isset($files[$key]) ? $files[$key] : null;

				//  WARNING: Don't break array levels. While moving upwards create new level but don't assign values until we finalize.
				$ref[$key] = null;
				$ref       = &$ref[$key];
			}

			$ref = $files;
		}

		return $array;
	}

	/**
	 * Method to loop through all selected form controls and try to call upload for any uploaded file found.
	 *
	 * @param   array  $uploads  Array of selected form controls from the files super-global
	 *
	 * @return  mixed  list of all file-control array that are uploaded (and saved to db if selected to do so) and
	 *                 false for failed ones in save hierarchy as in input control.
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected function processUploads($uploads)
	{
		$return = array();

		if (!is_array($uploads))
		{
			$return = false;
		}
		elseif (!isset($uploads['name'], $uploads['type'], $uploads['tmp_name'], $uploads['error'], $uploads['size']))
		{
			// Multiple file processing
			foreach ($uploads as $key => $upload)
			{
				$result = $this->processUploads($upload);

				$return[$key] = $result;

				// If current file upload failed and we can't ignore... quit!
				if (($result === false || (is_array($result) && in_array(false, $result, true))) && !$this->options['ignore'])
				{
					break;
				}
			}
		}
		else
		{
			// Single file processing
			$result = $this->uploadFile($uploads);
			$return = $result;

			if (is_array($result))
			{
				if ($this->options['table'] && $this->options['record'])
				{
					// Save to db
					$table = $this->getTable();

					$result['state']         = 1;
					$result['table_name']    = $this->options['table'];
					$result['record_id']     = $this->options['record'];
					$result['context']       = $this->options['context'];
					$result['original_name'] = substr($result['name'], -100);

					unset($result['name']);

					try
					{
						$table->save($result);

						$return = $table;
						$this->count += 1;
					}
					catch (Exception $e)
					{
						if ($this->options['ignore'])
						{
							JLog::add(JText::sprintf('COM_SELLACIOUS_ERROR_UPLOAD_SAVE_ERROR', $e->getMessage()), JLog::WARNING, 'jerror');
						}
						else
						{
							throw new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_UPLOAD_SAVE_ERROR', $e->getMessage()));
						}
					}
				}
				else
				{
					$this->count += 1;
				}
			}
		}

		return $return;
	}

	/**
	 * Method to store a single uploaded file to desired destination
	 *
	 * @param   array  $file  File input for single uploaded file
	 *
	 * @return  mixed  False on error, file array with destination path set.
	 *
	 * @since   1.0.0
	 *
	 * @deprecated  Use \Sellacious\Media\Upload\Uploader
	 */
	protected function uploadFile($file)
	{
		// Check for upload limit first
		if ($this->options['limit'] !== false && $this->count >= $this->options['limit'])
		{
			$result = new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_UPLOAD_MAX_UPLOAD_COUNT_EXCEEDED_N', (int) $this->options['limit']));
		}
		// Check for any uploaded error first
		elseif ($file['error'] > 0)
		{
			if ($file['error'] == 4)
			{
				$result = null;
			}
			elseif ($file['error'] == 3)
			{
				$result = new Exception('COM_SELLACIOUS_ERROR_UPLOAD_FILE_PARTIAL');
			}
			elseif ($file['error'] == 1 || $file['error'] == 2)
			{
				$result = new Exception('COM_SELLACIOUS_ERROR_UPLOAD_FILE_TOO_LARGE');
			}
			else
			{
				$result = true;
			}
		}
		// No error detected, continue with upload
		else
		{
			jimport('joomla.filesystem.file');

			$f_ext = strtolower(JFile::getExt($file['name']));

			// MIME ($file['type']) detection is not properly working, hence skip it and allow all that match extension only
			if (!in_array('.' . $f_ext, $this->options['type']['exts']))
			{
				$result = new Exception(JText::sprintf('COM_SELLACIOUS_ERROR_UPLOAD_NOT_ALLOWED_X', $f_ext));
			}
			else
			{
				if ($this->options['rename'] || $this->options['filename'])
				{
					$auto_name = sha1(microtime() . rand(1000, 9999)) . '-' . JFactory::getUser()->id;
					$filename  = $this->options['filename'] ? str_replace('*', $auto_name, $this->options['filename']) : $auto_name;
					$filename  = $filename . '.' . $f_ext;
				}
				else
				{
					$filename  = $file['name'];
				}

				$path = $this->options['folder'] . '/' . $this->options['prefix'] . $filename;

				// Save only Unix type DS without leading slash
				$file['path'] = ltrim($this->sanitize($path), '/ ');

				$uploaded = JFile::upload($file['tmp_name'], JPATH_SITE . '/' . $file['path']);

				if (!$uploaded)
				{
					$result = new Exception(JText::_('COM_SELLACIOUS_ERROR_UPLOAD_MOVE_FAILED'));
				}
				else
				{
					unset($file['error'], $file['tmp_name']);
					$result = $file;
				}
			}
		}

		return $result;
	}

	/**
	 * Checks if the file is an image using simple extension match, not using mime filter
	 *
	 * @param   string  $fileName  The filename
	 *
	 * @return  bool
	 *
	 * @since   1.2.0
	 */
	public function isImage($fileName)
	{
		jimport('joomla.filesystem.file');

		$types = $this->getAllowedTypes('image');
		$ext   = JFile::getExt($fileName);

		return in_array('.' . $ext, $types['exts']);
	}

	/**
	 * Crop the given image by fetching from table using given id according to the coordinates specified
	 *
	 * @param   int    $id        Media id for the file to be cropped
	 * @param   array  $dim_dest  [x,y,w,h]- region to be extracted, the coordinates can be proportional if the original image is scaled first
	 * @param   bool   $replace   Whether to overwrite original image with cropped one
	 *
	 * @return  string  Path to the new file created
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function crop($id, $dim_dest, $replace = true)
	{
		$image = $this->getItem($id);
		$path  = JPath::clean(JPATH_SITE . '/' . $image->path, '/');

		if (!is_file($path))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_IMAGE_SOURCE_MISSING'));
		}

		$img_info = getimagesize($path);
		$w_ratio  = isset($dim_dest['sw']) ? $img_info[0] / $dim_dest['sw'] : 1;
		$h_ratio  = isset($dim_dest['sh']) ? $img_info[1] / $dim_dest['sh'] : 1;

		if (abs($w_ratio) < 0.01 || abs($h_ratio) < 0.01)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_INVALID_REGION'));
		}

		$src_x = $dim_dest['x'] * $w_ratio;
		$src_y = $dim_dest['y'] * $h_ratio;
		$src_w = $dim_dest['w'] * $w_ratio;
		$src_h = $dim_dest['h'] * $h_ratio;

		$src_im = imagecreatefromstring(file_get_contents($path));
		$dst_im = imagecreatetruecolor($src_w, $src_h);

		if (!(imagecopy($dst_im, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h)))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_EXTRACT_REGION_FAILED'));
		}

		// We always save as jpeg, so remove extension
		$f_name = explode('.', basename($path));

		if (count($f_name) > 1)
		{
			array_pop($f_name);
		}

		$f_name   = implode('.', $f_name);
		$filename = ($replace ? '' : 'tmp/') . dirname($image->path) . '/' . $f_name . '.jpg';

		if (!imagejpeg($dst_im, JPATH_SITE . '/' . $filename, 100))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_EXTRACT_REGION_SAVE_FAILED'));
		}

		if ($replace)
		{
			/** @var SellaciousTableMedia $table */
			$table = $this->getTable();
			$table->load($id);
			$table->set('path', $filename);

			if (!$table->store())
			{
				throw new Exception($table->getError());
			}
		}

		return $filename;
	}

	/**
	 * Remove the given image from db and filesystem.
	 *
	 * @param   int|int[]  $pks  Id(s) of the file(s) in media table
	 *
	 * @return  bool|bool[]  Success status for each file if it is an array, single valued if integer
	 *
	 * @since   1.0.0
	 */
	public function remove($pks)
	{
		if (is_array($pks))
		{
			return array_map(array($this, 'remove'), $pks);
		}

		$table = $this->getTable();
		$table->load($pks);

		if ($table->get('id'))
		{
			$file = $table->get('path');

			if (!$table->delete())
			{
				return false;
			}

			// Attempt to remove the "file" physically as well.
			if (strlen($file) && is_file(JPATH_SITE . '/' . $file))
			{
				@unlink(JPATH_SITE . '/' . $file);
			}
		}

		return true;
	}

	/**
	 * Send the file content to the browser for download
	 *
	 * @param   int  $id  Id of the files in media table
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function download($id)
	{
		$file = $this->getItem($id);

		$this->downloadFile($file->path, $file->original_name, $file->type);
	}

	/**
	 * Send the file content to the browser for download
	 *
	 * @param   string  $path      Relative path of the files in media table
	 * @param   string  $filename  Filename to send with content header
	 * @param   string  $mime      MIME type of the file if known
	 *
	 * @throws  Exception
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function downloadFile($path, $filename = '', $mime = 'application/octet-stream')
	{
		// Check if file really exists in the filesystem.
		if (!$path || !is_file(JPATH_SITE . '/' . $path))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_DOWNLOAD_FILE_NOT_FOUND'));
		}

		// Check for headers already sent.
		if (headers_sent($file, $line))
		{
			throw new Exception(JText::sprintf('COM_SELLACIOUS_HEADERS_ALREADY_SENT_AT', $file, $line));
		}

		// Send the file content and close the app.
		$filename = !empty($filename) ? $filename : basename($path);

		header('Content-Type: ' . $mime);
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: private');
		header('Pragma: private');
		header('Expires: Sat, 08 Mar 1986 00:03:00 GMT');

		readfile(JPATH_SITE . '/' . $path);

		// Ensure we don't allow more output
		JFactory::getApplication()->close();
	}

	/**
	 * Crop the given image according to the coordinates specified
	 *
	 * TODO: REMOVE/MERGE this method with above !!!wait!!!
	 *
	 * @param   string  $path        path to the image file
	 * @param   array   $dim_dest    x,y,w,h - region which is to be extracted, the coordinates may be proportional
	 *                               if the original image was scaled first
	 * @param   array   $dim_source  w,h - this is the scaled area if the image was scaled before selecting the
	 *                               crop region, ignore if not scaled
	 *
	 * @return  string  path to the new file created
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 * @deprecated
	 */
	public function cropImage($path, $dim_dest, $dim_source = null)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');

		$path_absolute = JPath::clean(JPATH_SITE . '/' . $path, '/');

		if (!is_file($path_absolute))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_IMAGE_FILE_MISSING'));
		}

		$image_info = getimagesize($path_absolute);

		if (empty($dim_source['w']))
		{
			$dim_source['w'] = $image_info[0];
		}
		if (empty($dim_source['h']))
		{
			$dim_source['h'] = $image_info[1];
		}

		$w_ratio = $image_info[0] / $dim_source['w'];
		$h_ratio = $image_info[1] / $dim_source['h'];

		if ($w_ratio == 0 || $h_ratio == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_INVALID_PROPORTION'));
		}

		$src_x = $dim_dest['x'] * $w_ratio;
		$src_y = $dim_dest['y'] * $h_ratio;
		$src_w = $dim_dest['w'] * $w_ratio;
		$src_h = $dim_dest['h'] * $h_ratio;

		switch ($image_info['mime'])
		{
			case 'image/jpeg':
				$src_im = imagecreatefromjpeg($path_absolute);
				break;
			case 'image/png':
				$src_im = imagecreatefrompng($path_absolute);
				break;
			default:
				$src_im = imagecreatefromstring(file_get_contents($path_absolute));
				break;
		}

		$dst_im = imagecreatetruecolor($src_w, $src_h);

		if (!imagecopy($dst_im, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_EXTRACT_SELECTED_REGION_FAILED'));
		}

		// We always save as jpeg
		$filename = JFile::stripExt(basename($path));
		$filename = $filename ? $filename . '.jpg' : sha1(microtime()) . '.jpg';

		// Save only Unix type DS
		$filename = ltrim($this->sanitize(dirname($path) . '/' . $filename), '/ ');

		if (!imagejpeg($dst_im, JPATH_SITE . '/' . $filename, 100))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_SAVE_SELECTED_REGION_FAILED'));
		}

		return $filename;
	}

	/**
	 * Resize the given image
	 *
	 * @param  string  $path
	 * @param  array   $dim_dest
	 * @param  string  $renameMask
	 *
	 * @return mixed
	 * @throws Exception
	 *
	 * @since   1.0.0
	 */
	public function resize($path, $dim_dest, $renameMask = '*')
	{
		$path = JPath::clean(JPATH_SITE . '/' . $path, '/');

		if (!is_file($path))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_IMAGE_FILE_MISSING'));
		}

		$dim_dest   = array_merge(array('w' => '', 'h' => ''), $dim_dest);
		$image_info = getimagesize($path);

		$dim_src['w'] = $image_info[0];
		$dim_src['h'] = $image_info[1];

		if ($dim_dest['h'] == 0 && $dim_dest['w'] == 0)
		{
			// no resize needed
			return true;
		}
		elseif ($dim_dest['h'] == 0 && $dim_dest['w'] > 0)
		{
			$dim_dest['h'] = intval(($dim_dest['w'] / $image_info[0]) * $image_info[1]);
		}
		elseif ($dim_dest['w'] == 0 && $dim_dest['h'] > 0)
		{
			$dim_dest['w'] = intval(($dim_dest['h'] / $image_info[1]) * $image_info[0]);
		}
		else
		{
			// all set
		}

		switch ($image_info['mime'])
		{
			case 'image/jpeg':
				$src_im = imagecreatefromjpeg($path);
				break;
			case 'image/png':
				$src_im = imagecreatefrompng($path);
				break;
			default:
				$src_im = imagecreatefromstring(file_get_contents($path));
				break;
		}

		$dst_im = imagecreatetruecolor($dim_dest['w'], $dim_dest['h']);

		if (!imagecopyresized($dst_im, $src_im, 0, 0, 0, 0, $dim_dest['w'], $dim_dest['h'], $image_info[0], $image_info[1]))
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_EXTRACT_SELECTED_REGION_FAILED'));
		}

		// we add mime extension later, so remove extension
		$f_name = explode('.', basename($path));

		if (count($f_name) > 1)
		{
			array_pop($f_name);
		}

		$f_name = implode('.', $f_name);

		// Rename mask would contain * for current basename
		$filename = dirname($path) . '/' . $f_name;

		if ($renameMask != '' && $renameMask != '*')
		{
			$filename = str_replace('*', $filename, $renameMask);
		}

		switch ($image_info['mime'])
		{
			case 'image/png':
				@unlink($filename . '.png');
				$done = imagepng($dst_im, $filename . '.png', 9);
				break;
			case 'image/jpeg':
			default:
				@unlink($filename . '.jpg');
				$done = imagejpeg($dst_im, $filename . '.jpg', 100);
				break;
		}

		if (!$done)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_ERROR_CROP_SAVE_RESIZED_REGION_FAILED'));
		}

		return $filename;
	}

	/**
	 * Get the CSS to size the <img> tag based on the actual image dimensions within the specified target sized container
	 *
	 * @param  $path
	 * @param  $target
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getSizeStyle($path, $target)
	{
		if (!is_file(JPATH_SITE . '/' . $path))
		{
			return '';
		}

		list($width, $height) = getimagesize(JPATH_SITE . '/' . $path);

		if ($width > $height)
		{
			$perc   = $target / $width;
			$width  = $target;
			$height = round($height * $perc);
			$margin = abs($target - $height) / 2;
			$margin = "{$margin}px 0 {$margin}px 0";
		}
		else
		{
			$perc   = $target / $height;
			$width  = round($width * $perc);
			$height = $target;
			$margin = abs($target - $width) / 2;
			$margin = "0 {$margin}px 0 {$margin}px";
		}

		return "width: {$width}px; height: {$height}px; margin: {$margin};";
	}

	/**
	 * Delete a file or folder
	 *
	 * @param  string  $base   Site root relative path to the target file(s)
	 * @param  mixed   $files  File or array of files to be deleted
	 *
	 * @return bool
	 *
	 * @since   1.0.0
	 */
	public function delete($base, $files)
	{
		$deleted = 0;
		$parent  = rtrim(JPATH_SITE . '/' . $this->sanitize($base), '/');

		foreach ($files as &$file)
		{
			$node = $parent . '/' . $this->sanitize($file);

			if (is_file($node) || is_link($node))
			{
				$deleted += JFile::delete($node) ? 1 : 0;
			}
			elseif (is_dir($node))
			{
				$deleted += JFolder::delete($node) ? 1 : 0;
			}
		}

		return $deleted;
	}

	/**
	 * Get a blank image placeholder reference, either the path to it or a full html <img> tag
	 *
	 * @param   bool  $path_only
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getBlankImage($path_only = false)
	{
		$url = JHtml::_('image', 'com_sellacious/no_image.jpg', null, null, true, true);

		return $path_only ? $url : array('url' => $url, 'title' => JText::_('COM_SELLACIOUS_NO_IMAGE_AVAILABLE'));
	}

	/**
	 * Return the relative url to the given site path relative url.
	 * If the $blank arg is true then path to blank image will be returned in case actual image does not exist
	 *
	 * @param   string  $relative
	 * @param   bool    $blank
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getURL($relative, $blank = false)
	{
		$relative = ltrim($this->sanitize($relative), '/');

		if ($relative && is_file(JPATH_SITE . '/' . $relative))
		{
			$link = JUri::root(true) . '/' . $relative;
		}
		elseif ($blank)
		{
			$link = $this->getBlankImage(true);
		}
		else
		{
			$link = '';
		}

		return $link;
	}

	/**
	 * Get List of URLs of valid images for a given entity.
	 * If no images are set an array containing one blank (placeholder) image may be returned.
	 *
	 * @param   string  $tableName  Name of the entity table in the database. A dot separated "table.context" format may
	 *                              be used to specify some other "context" that 'images'
	 * @param   int     $recordId   Record id of the row for which query is made.
	 * @param   bool    $blank      Whether to return a blank (placeholder) image in case no matching images are found.
	 * @param   bool    $url        Whether to convert the paths into urls routes.
	 *
	 * @return  string[]
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImages($tableName, $recordId, $blank = true, $url = true)
	{
		$context = 'images';

		if (strpos($tableName, '.'))
		{
			list ($tableName, $context) = explode('.', $tableName, 2);
		}

		$filter = array(
			'list.select' => 'a.path',
			'table_name'  => $tableName,
			'context'     => $context,
			'record_id'   => $recordId,
			'state'       => 1,
		);

		$paths = $this->helper->media->loadColumn($filter);

		// Only images should be returned
		$paths = array_filter((array) $paths, array($this, 'isImage'));

		if ($url)
		{
			$paths = array_map(array($this->helper->media, 'getURL'), $paths);
			$paths = array_filter($paths, 'strlen');

			if ($blank && count($paths) == 0)
			{
				$paths = array($this->helper->media->getBlankImage(true));
			}
		}
		else
		{
			$p = array();

			foreach ($paths as $path)
			{
				if (is_file(JPATH_SITE . '/' . $path))
				{
					$p[] = $path;
				}
			}

			$paths = $p;

			if ($blank && count($paths) == 0)
			{
				$paths = array('media/com_sellacious/images/no_image.jpg');
			}
		}

		return $paths;
	}

	/**
	 * Get a URL of valid a image for a given entity.
	 * If no image is found one blank (placeholder) image may be returned.
	 *
	 * @param   string  $tableField  Name of the entity table in the database. A dot separated "table.context" format may
	 *                               be used to specify some other "context" than 'images'
	 * @param   int     $recordId    Record id of the row for which query is made.
	 * @param   bool    $blank       Whether to return a blank (placeholder) image in case no matching images are found.
	 * @param   bool    $url         Whether to convert the paths into urls routes.
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getImage($tableField, $recordId, $blank = true, $url = true)
	{
		$context = 'images';

		if (strpos($tableField, '.'))
		{
			list ($tableField, $context) = explode('.', $tableField, 2);
		}

		$filter = array(
			'list.select' => 'a.path',
			'table_name'  => $tableField,
			'context'     => $context,
			'record_id'   => $recordId,
			'state'       => 1,
		);

		$path = $this->helper->media->loadResult($filter);

		// Only images should be returned
		if ($path && is_file(JPATH_SITE . '/' . $path) && $this->isImage($path))
		{
			$value = $url ? $this->helper->media->getURL($path, $blank) : $path;
		}
		elseif ($blank)
		{
			$value = $url ? $this->helper->media->getBlankImage(true) : 'media/com_sellacious/images/no_image.jpg';
		}
		else
		{
			$value = '';
		}

		return $value;
	}

	/**
	 * Load media files from filesystem
	 *
	 * @param   string  $pathMask
	 * @param   string  $tableName
	 * @param   string  $context
	 * @param   int     $recordId
	 * @param   string  $docType
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.5.1
	 */
	public function getFromFilesystem($pathMask, $tableName, $context, $recordId, $docType)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.path');

		$items   = array();
		$base    = JPath::clean(JPATH_SITE . '/');
		$path    = JPath::clean(JPATH_SITE . '/' . $pathMask);
		$matches = glob($path);

		$tFile = new stdClass;

		$tFile->id            = 0;
		$tFile->table_name    = $tableName;
		$tFile->context       = $context;
		$tFile->record_id     = $recordId;
		$tFile->doc_type      = $docType;
		$tFile->path          = null;
		$tFile->original_name = null;
		$tFile->doc_reference = null;

		foreach ($matches as $match)
		{
			if (is_dir($match))
			{
				$files = JFolder::files($match, '.', false, true);
			}
			elseif (is_file($match))
			{
				$files = array($match);
			}
			else
			{
				$files = array();
			}

			foreach ($files as $filePath)
			{
				$file = clone $tFile;

				$relPath = substr($filePath, strlen($base));

				$file->id            = 'B64:' . base64_encode($relPath);
				$file->path          = $relPath;
				$file->original_name = basename($filePath);
				$file->doc_reference = basename($filePath);

				$items[] = $file;
			}
		}

		return $items;
	}

	/**
	 * Method to load the media files linked to a record based on the file path pattern provided in the configurations
	 *
	 * @param   string    $tableName  The table name for the record
	 * @param   string    $context    The media file context
	 * @param   callable  $preParser  The function/callback that will handle the pattern and will convert it to real path
	 * @param   array     $arguments  The arguments to be passed to the callback. 1st argument will be the pattern and these will be 2nd, 3rd, so on
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.6.0
	 */
	public function getFilesFromPattern($tableName, $context, callable $preParser, array $arguments = array())
	{
		// Pattern settings for context: e.g. - media_products_image_path, media_categories_images_path
		$pattern = $this->helper->config->get("media_{$tableName}_{$context}_path");

		if (is_object($pattern))
		{
			$pattern = ArrayHelper::fromObject($pattern, false);
		}

		$files = array();

		if (is_string($pattern) && strlen($pattern))
		{
			$cbArgs = array_merge(array($pattern), $arguments);
			$path   = call_user_func_array($preParser, $cbArgs);
			$files  = $this->helper->media->getFromFilesystem($path, $tableName, $context, null, null);
		}
		elseif (is_array($pattern))
		{
			$filesT = array();

			foreach ($pattern as $mask)
			{
				$cbArgs   = array_merge(array($mask->value), $arguments);
				$path     = call_user_func_array($preParser, $cbArgs);
				$filesT[] = $this->helper->media->getFromFilesystem($path, $tableName, $context, null, $mask->text);
			}

			$files = array_reduce($filesT, 'array_merge', array());
		}

		return $files;
	}

	/**
	 * Create an image from the given text.
	 *
	 * @param   string  $text    The string to write
	 * @param   int     $size    Text size
	 * @param   bool    $base64  Whether to return base64 encoded string (true), or raw PNG stream (false)
	 *
	 * @return  string  The PNG format data that can be directly written to a file.
	 *
	 * @since   1.0.0
	 */
	public function writeText($text, $size = 2, $base64 = false)
	{
		$h       = imagefontheight($size);
		$w       = imagefontwidth($size) * strlen($text);
		$canvas  = imagecreate($w + 4, $h + 4);
		$b_color = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
		$t_color = imagecolorallocate($canvas, 0, 0, 0);

		imagefill($canvas, 0, 0, $b_color);
		imagestring($canvas, $size, $size, 1, $text, $t_color);

		ob_start();
		imagepng($canvas);
		$content = ob_get_clean();

		imagecolordeallocate($canvas, $b_color);
		imagecolordeallocate($canvas, $t_color);
		imagedestroy($canvas);

		return $base64 ? base64_encode($content) : $content;
	}

	/**
	 * Sanitize a path for special characters and extra path separators
	 *
	 * @param   string  $path   The path to sanitize
	 * @param   bool    $extra  Whether to make the path more safe, usable when building new path to write to, while reading this can be set to false.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	protected function sanitize($path, $extra = false)
	{
		$regex = $extra ? array(
			'ds'         => '#[/\\\\]+#',
			'ds-dot-dot' => '#[/\\\\](\.){2,}#',
			'special'    => '#[^A-Za-z0-9\.\_\-\/ ]+#',
			'spaces'     => '#[ ]+#',
		) : array(
			'ds'         => '#[/\\\\]+#',
			'ds-dot-dot' => '#[/\\\\](\.){2,}#',
		);

		return preg_replace($regex, array('/', '/', '-', ' '), $path);
	}

	/**
	 * Get the file type name based on the file extension or the mime provided
	 *
	 * @param   string  $value  File name or mime type, see second parameter
	 * @param   bool    $mime   Whether to check by mime type
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getFileType($value, $mime)
	{
		jimport('joomla.filesystem.file');

		$filters = array('list.select' => 'a.note', 'list.from' => '#__sellacious_mimes');

		if ($mime)
		{
			$filters['mime'] = $value;
		}
		else
		{
			$ext = JFile::getExt($value);

			$filters['extension'] = array($ext, ".$ext");
		}

		return $this->loadResult($filters);
	}

	/**
	 * Set the protected flag for the media files, so as to allow/disallow direct downloads
	 *
	 * @param   string  $table
	 * @param   int     $record_id
	 * @param   bool    $value
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function protect($table, $record_id, $value = true)
	{
		$query = $this->db->getQuery(true);

		try
		{
			$query->update($this->table)
				->set('protected = ' .(int) $value)
				->where('table_name = ' . $this->db->q($table))
				->where('record_id = ' . (int) $record_id);

			return (bool) $this->db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Tests whether the specified uploaded file is of given type or not
	 * NOTE: This assumes single file upload per control
	 *
	 * @param   string  $input
	 * @param   array   $fileTypes
	 *
	 * @return  bool|null  NULL if no file uploaded. T/F if upload is valid extension.
	 *
	 * @since   1.4.5
	 */
	public function validateType($input, $fileTypes)
	{
		$r = $this->getAllowedTypes($fileTypes);

		$file = $this->getUploads($input);

		if (strpos($input, '.') !== false)
		{
			$parts = array_slice(explode('.', $input), 1);

			foreach ($parts as $part)
			{
				$file = ArrayHelper::getValue($file, $part, array(), 'array');
			}
		}

		if (!is_array($file) || !isset($file['name']))
		{
			return null;
		}

		jimport('joomla.filesystem.file');

		$ext = JFile::getExt($file['name']);

		return in_array(strtolower('.' . $ext), $r['exts']);
	}

	/**
	 * Method to sync media information in database with filesystem to trash any record that points to a non-existing file.
	 *
	 * @return  int   Number of records trashed
	 * @throws  Exception
	 *
	 * @since   1.4.7
	 */
	public function purgeMissing()
	{
		$filter  = array('list.select' => 'a.id, a.path', 'state' => array(0, 1));
		$records = $this->loadObjectList($filter);
		$trash   = array();
		$count   = 0;

		foreach ($records as $record)
		{
			if (!is_file(JPATH_SITE . '/' . $record->path))
			{
				$trash[] = $record->id;
			}
		}

		if ($trash)
		{
			$query = $this->db->getQuery(true);
			$query->update($this->table)
				->set('state = -2')
				->where('id IN (' . implode(', ', $this->db->q($trash)). ')');

			$this->db->setQuery($query)->execute();

			$count = count($trash);
		}

		return $count;
	}

	/**
	 * Copy an existing media record and the file into another record
	 *
	 * @param   int     $mediaId    The media to be copied
	 * @param   int     $recordId   The new record id
	 * @param   string  $context    The new field context, null for no change
	 * @param   string  $tableName  The new table name, null for no change
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	public function copy($mediaId, $recordId, $context = null, $tableName = null)
	{
		$item = $this->loadObject(array('id' => $mediaId));

		if ($item)
		{
			$item->record_id  = $recordId;
			$item->context    = $context ?: $item->context;
			$item->table_name = $tableName ?: $item->table_name;

			$base = $this->baseDir . '/' . $item->table_name . '/' . str_replace('.', '/', $item->context);
			$path = $base . '/' . $item->record_id . '/' . basename($item->path);

			if (is_file(JPATH_SITE . '/' . $item->path))
			{
				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');

				JFolder::create(dirname(JPATH_SITE . '/' . $path));
				JFile::copy($item->path, $path, JPATH_SITE . '/');
			}

			$item->id    = null;
			$item->path  = $path;
			$item->state = is_file(JPATH_SITE . '/' . $path);

			return $this->db->insertObject($this->table, $item, 'id');
		}

		return false;
	}

	/**
	 * Method to sync media information in database with filesystem
	 *
	 * @return  int  Number of new files discovered
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.7
	 */
	public function syncFromFilesystem()
	{
		$contexts = array(
			'categories.images',
			'categories.banners',
			'clients.org_certificate',
			'config.backoffice_logo',
			'config.eproduct_image_watermark',
			'config.purchase_exchange_icon',
			'config.purchase_return_icon',
			'config.shop_logo',
			'eproduct_media.media',
			'eproduct_media.sample',
			'license.logo',
			'manufacturers.logo',
			'paymentmethod.logo',
			'product_sellers.attachments',
			'products.attachments',
			'products.images',
			'sellers.logo',
			'splcategories.badge',
			'splcategories.images',
			'variants.images',
		);

		$count = 0;

		foreach ($contexts as $context)
		{
			list($tableName, $context) = explode('.', $context, 2);

			$this->syncFromBase($tableName, $context, $count);
		}

		return $count;
	}

	/**
	 * Method to get the maximum allowed file size for the HTTP uploads based on the active PHP configuration
	 *
	 * @param   mixed  $custom  A custom upper limit, if the PHP settings are all above this then this will be used
	 *
	 * @return  int  Size in number of bytes
	 *
	 * @since   1.5.0
	 */
	public function getMaxUploadSize($custom = null)
	{
		if ($custom)
		{
			$custom = JHtml::_('number.bytes', $custom, '');

			if ($custom > 0)
			{
				$sizes[] = $custom;
			}
		}

		/*
		 * Read INI settings which affects upload size limits
		 * and Convert each into number of bytes so that we can compare
		 */
		$sizes[] = JHtml::_('number.bytes', ini_get('post_max_size'), '');
		$sizes[] = JHtml::_('number.bytes', ini_get('upload_max_filesize'), '');

		// The minimum of these is the limiting factor
		return min($sizes);
	}

	/**
	 * Additional functions for directory browsing purposes.
	 * We'd be allowing strictly only '/images' folder for this purpose
	 * at least for current plan
	 */

	/**
	 * Build folder hierarchy navigation list for all parent folders for selected path
	 *
	 * @param   string  $base  The directory to display
	 *
	 * @return  stdClass[]
	 *
	 * @since   1.0.0
	 */
	public function getNav($base = null)
	{
		$baseDir  = $this->getBaseDir();
		$basePath = $this->getBaseDir($base);

		$lockedLen = strlen(trim($baseDir, '/'));
		$parts     = array_filter(explode('/', $basePath), 'strlen');

		$nav  = array();
		$name = '/';
		$path = '';

		do
		{
			$path = trim($path . '/' . $name, '/');

			$step          = new stdClass;
			$step->name    = $name;
			$step->path    = $path;
			$step->relpath = substr($path, $lockedLen);
			$step->lock    = strlen($path) < $lockedLen;
			$step->exists  = is_dir(JPATH_SITE . '/' . $path);

			$nav[] = $step;
		}
		while (null !== ($name = array_shift($parts)));

		return $nav;
	}

	/**
	 * Build files list in a folder grouped by type: folder, images, docs
	 *
	 * @param   string  $base  The image directory to display
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getFileList($base = null)
	{
		static $list;

		// Only process the list once per request
		if (is_array($list))
		{
			return $list;
		}

		$basePath  = JPATH_SITE . '/' . $this->getBaseDir($base);
		$mediaBase = JPath::clean($basePath, '/');

		$images  = array();
		$folders = array();
		$docs    = array();

		$fileList   = false;
		$folderList = false;

		if (file_exists($basePath))
		{
			// Get the list of files and folders from the given folder
			$fileList   = JFolder::files($basePath);
			$folderList = JFolder::folders($basePath);
		}

		$mediaHelper = new JHelperMedia;

		// Iterate over the files if they exist
		if ($fileList !== false)
		{
			foreach ($fileList as $file)
			{
				if (is_file($basePath . '/' . $file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
				{
					$tmp                = new stdClass;
					$tmp->name          = $file;
					$tmp->title         = $file;
					$tmp->path          = JPath::clean($basePath . '/' . $file, '/');
					$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
					$tmp->size          = filesize($tmp->path);

					$ext = strtolower(JFile::getExt($file));

					if (in_array($ext, array('jpg', 'png', 'gif', 'xcf', 'odg', 'bmp', 'jpeg', 'ico')))
					{
						// Image
						$info        = @getimagesize($tmp->path);
						$tmp->width  = @$info[0];
						$tmp->height = @$info[1];
						$tmp->type   = @$info[2];
						$tmp->mime   = @$info['mime'];

						if (($tmp->width > 60) || ($tmp->height > 60))
						{
							$dimensions     = $mediaHelper->imageResize($tmp->width, $tmp->height, 60);
							$tmp->width_60  = $dimensions[0];
							$tmp->height_60 = $dimensions[1];
						}
						else
						{
							$tmp->width_60  = $tmp->width;
							$tmp->height_60 = $tmp->height;
						}

						if (($tmp->width > 16) || ($tmp->height > 16))
						{
							$dimensions     = $mediaHelper->imageResize($tmp->width, $tmp->height, 16);
							$tmp->width_16  = $dimensions[0];
							$tmp->height_16 = $dimensions[1];
						}
						else
						{
							$tmp->width_16  = $tmp->width;
							$tmp->height_16 = $tmp->height;
						}

						$images[] = $tmp;
					}
					else
					{
						// Non-image document
						$tmp->icon_32 = "media/mime-icon-32/" . $ext . ".png";
						$tmp->icon_16 = "media/mime-icon-16/" . $ext . ".png";
						$docs[]       = $tmp;
					}
				}
			}
		}

		// Iterate over the folders if they exist
		if ($folderList !== false)
		{
			foreach ($folderList as $folder)
			{
				$tmp                = new stdClass;
				$tmp->name          = basename($folder);
				$tmp->path          = JPath::clean($basePath . '/' . $folder, '/');
				$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
				$count              = $mediaHelper->countFiles($tmp->path);
				$tmp->files         = $count[0];
				$tmp->folders       = $count[1];

				$folders[] = $tmp;
			}
		}

		$list = compact('folders', 'images', 'docs');

		return $list;
	}

	/**
	 * create new folder in the given destination
	 *
	 * @param   string  $base
	 * @param   string  $folder
	 *
	 * @return  bool
	 *
	 * @since   1.0.0
	 */
	public function newFolder($base, $folder)
	{
		// Are we sure we should allow create folder inside baseDir only?
		$path = $this->getBaseDir($base . '/' . $folder);
		$path = rtrim(JPATH_SITE . '/' . $path, '/');

		$created = JFolder::create($path);

		if ($created)
		{
			$html = '<html></html>';
			JFile::write($path . '/index.html', $html);
		}

		return $created;
	}

	/**
	 * Method to watermark given image with the given watermark overlay image covering said region of the original image.
	 * Currently we support watermark aligned to the left only with tiles pattern and center aligned
	 *
	 * @param   string  $image      The image to be watermarked
	 * @param   string  $watermark  The image to be used as watermark overlay
	 * @param   int     $region     The region of the original image to be covered by the watermark in percentage 1-100 (for center single watermark)
	 *                              NULL causes to fill entire image will tiles pattern
	 * @param   string  $filename   The fully qualified filename for the output file. If omitted then the source file will be overwritten.
	 *
	 * @return  bool  The operation result. True on success, false otherwise.
	 *
	 * @since   1.0.0
	 * @deprecated
	 */
	public function watermark($image, $watermark, $region = 100, $filename = null)
	{
		if (!is_file(JPATH_ROOT . '/' . $image) || !is_file(JPATH_ROOT . '/' . $watermark))
		{
			JLog::add(JText::_('COM_SELLACIOUS_NO_FILES_FOR_WATERMARK'), JLog::WARNING, 'jerror');

			return false;
		}

		$okay = true;

		// Configure image dimensions
		list($iw, $ih) = getimagesize(JPATH_ROOT . '/' . $image);
		list($ww, $wh) = getimagesize(JPATH_ROOT . '/' . $watermark);

		// Load the watermark and the original image to apply the watermark to
		$target  = imagecreatefromstring(file_get_contents(JPATH_ROOT . '/' . $image));
		$source  = imagecreatefromstring(file_get_contents(JPATH_ROOT . '/' . $watermark));

		if (isset($region))
		{
			$region = (int) $region;
			$region = ($region >= 1 && $region <= 100 ? $region / 100 : 1);
			$scale  = min(($iw * $region) / $ww, ($ih * $region) / $wh);
			$ow     = $ww * $scale;
			$oh     = $wh * $scale;

			// Resize overlay
			$overlay = imagecreatetruecolor($ow, $oh);
			$okay    = imagecopyresized($overlay, $source, 0, 0, 0, 0, $ow, $oh, $ww, $wh);

			$off_x = floor(($iw - $ow) / 2);
			$off_y = floor(($ih - $oh) / 2);

			$okay  = imagecopymerge($target, $overlay, $off_x, $off_y, 0, 0, $ow, $oh, 50);

			imagedestroy($overlay);
		}
		else
		{
			$off_y = 0;

			while ($off_y < $ih)
			{
				$off_x = 0;

				while ($off_x < $iw)
				{
					// Merge overlay with 50% opacity
					$okay = imagecopymerge($target, $source, $off_x, $off_y, 0, 0, $ww, $wh, 50);

					if (!$okay)
					{
						break 2;
					}

					$off_x += $ww + 0;
				}

				$off_y += $wh + 0;
			}
		}

		if ($okay)
		{
			// Save the image to a destination file
			$filename = $filename ?: $image;
			$okay     = imagejpeg($target, JPATH_SITE . '/' . $filename);

			if (!$okay)
			{
				JLog::add(JText::_('COM_SELLACIOUS_ERROR_WATERMARK_SAVE'), JLog::WARNING, 'jerror');
			}
		}
		else
		{
			JLog::add(JText::_('COM_SELLACIOUS_ERROR_WATERMARK_MERGE_OVERLAY'), JLog::WARNING, 'jerror');
		}

		// Cleanup memory
		imagedestroy($target);
		imagedestroy($source);

		return $okay;
	}

	/**
	 * Generating an embed link of an FB/Vimeo/Youtube Video.
	 *
	 * @param   string  $url
	 *
	 * @return  mixed  The full embed link of video, or boolean false if the url is empty
	 *
	 * @since   1.6.0
	 */
	public function generateVideoEmbedUrl($url)
	{

		if ($url == '')
		{
			return false;
		}

		$finalUrl = '';

		if (strpos($url, 'facebook.com/') !== false)
		{
			//It is FB video
			$finalUrl .= 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($url) . '&show_text=1&width=200';
		}
		else if (strpos($url, 'vimeo.com/') !== false)
		{
			//It is Vimeo video
			$videoId = explode("vimeo.com/", $url)[1];
			if (strpos($videoId, '&') !== false)
			{
				$videoId = explode("&", $videoId)[0];
			}
			$finalUrl .= 'https://player.vimeo.com/video/' . $videoId;
		}
		else if (strpos($url, 'youtube.com/') !== false)
		{
			// It is Youtube video
			$videoId = explode("v=", $url)[1];
			if (strpos($videoId, '&') !== false)
			{
				$videoId = explode("&", $videoId)[0];
			}
			$finalUrl .= 'https://www.youtube.com/embed/' . $videoId;
		}
		else if (strpos($url, 'youtu.be/') !== false)
		{
			// It is Youtube video
			$videoId = explode("youtu.be/", $url)[1];
			if (strpos($videoId, '&') !== false)
			{
				$videoId = explode("&", $videoId)[0];
			}
			$finalUrl .= 'https://www.youtube.com/embed/' . $videoId;
		}

		return $finalUrl;
	}

	/**
	 * Generating an img thumb of an Vimeo/Youtube Video.
	 *
	 * @param   string  $url
	 * @param   string  $size
	 *
	 * @return  string  Img src
	 *
	 * @since   1.6.0
	 */
	public function generateVideoThumb($url, $size = 'large')
	{
		if ($size == 'thumb')
		{
			$size  = 1;
			$sizeV = 'thumbnail_small';
		}
		else
		{
			$size  = 0;
			$sizeV = 'thumbnail_large';
		}

		$image_url = parse_url($url);

		if (strpos($image_url['host'], 'youtube.com') !== false)
		{
			$array = explode('&', $image_url['query']);

			return 'http://img.youtube.com/vi/' . substr($array[0], 2) . '/' . $size . '.jpg';
		}
		elseif (strpos($image_url['host'], 'vimeo.com') !== false)
		{
			$hash = unserialize(file_get_contents('http://vimeo.com/api/v2/video/' . substr($image_url['path'], 1) . '.php'));

			return $hash[0][$sizeV];
		}

		return null;
	}

	/**
	 * Method to sync the media files from its base directory where it is supposed to be stored
	 *
	 * @param   string  $tableName  The table name for the record
	 * @param   string  $context    The media context for the record
	 * @param   int     $count      Number of matches files found during the sync
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	protected function syncFromBase($tableName, $context, &$count)
	{
		$base = $this->baseDir . '/' . $tableName . '/' . str_replace('.', '/', $context);

		if (is_dir(JPATH_SITE . '/' . $base))
		{
			$iterator = new DirectoryIterator(JPATH_SITE . '/' . $base);

			foreach ($iterator as $record)
			{
				if ($record->isDir() && !$record->isDot())
				{
					$recordId    = $record->getFilename();
					$subIterator = new DirectoryIterator(JPATH_SITE . '/' . $base . '/' . $recordId);

					foreach ($subIterator as $file)
					{
						if ($file->isFile() && !$file->isDot() && !$file->isDot())
						{
							$fileName = $file->getFilename();
							$char     = substr($fileName, 0, 1);

							if ($char != '.' && $char != '_' && $char != '~')
							{
								$path = $base . '/' . $recordId . '/' . $fileName;
								$mime = MediaHelper::getMimeType(JPATH_SITE . '/' . $path);
								$size = $file->getSize();

								$row = array(
									'table_name' => $tableName,
									'record_id'  => $recordId,
									'context'    => $context,
									'path'       => $path,
								);

								$table = $this->getTable();
								$table->load($row);

								if (!$table->get('id'))
								{
									$row = array(
										'table_name'    => $tableName,
										'record_id'     => $recordId,
										'context'       => $context,
										'path'          => $path,
										'original_name' => $fileName,
										'type'          => $mime,
										'size'          => $size,
										'doc_type'      => null,
										'doc_reference' => null,
										'protected'     => 0,
										'state'         => 1,
										'ordering'      => 1,
									);

									if ($table->save($row))
									{
										$count++;
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
