<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Import\Processor\Products;

defined('_JEXEC') or die;

use Sellacious\Import\AbstractImporter;
use Sellacious\Import\Processor\AbstractProcessor;
use Sellacious\Media\MediaHelper;

class ImageProcessor extends AbstractProcessor
{
	protected $helper;

	/**
	 * Constructor
	 *
	 * @param   AbstractImporter  $importer  The parent importer instance object
	 *
	 * @since   1.6.1
	 */
	public function __construct(AbstractImporter $importer)
	{
		parent::__construct($importer);

		try
		{
			$this->helper = \SellaciousHelper::getInstance();
		}
		catch (\Exception $e)
		{
		}
	}

	/**
	 * The columns that will be the part of import CSV
	 *
	 * @return  string[]
	 *
	 * @see     getcolumns()
	 *
	 * @since   1.6.1
	 */
	protected function getCsvColumns()
	{
		return array(
			'image_url',
			'image_folder',
			'image_filename',
		);
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they are needed to be evaluated first by any other processors.
	 * Without these keys evaluated this processor cannot process.
	 *
	 * @return  string[]
	 *
	 * @see     getDependencies()
	 *
	 * @since   1.6.1
	 */
	protected function getRequiredColumns()
	{
		return array(
			'x__product_id',
			'x__variant_id',
			'x__seller_uid',
			'x__category_ids',
		);
	}

	/**
	 * The columns that will NOT be the part of import CSV,
	 * but they will be evaluated by this processors and are available to be used by any other processor.
	 *
	 * @return  string[]
	 *
	 * @see     getDependables()
	 *
	 * @since   1.6.1
	 */
	protected function getGeneratedColumns()
	{
		return array();
	}

	/**
	 * Method to perform the actual import tasks for individual record.
	 * Any write actions can be performed at this stage relevant to the passed record.
	 * If this is called then all dependency must've been already fulfilled by some other processors.
	 *
	 * @param   \stdClass  $obj  The record obtained from CSV, was pre-processed in <var>preProcessRecord()</var>
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function processRecord($obj)
	{
		if ($obj->image_filename)
		{
			$this->saveImage($obj);
		}

		if ($obj->image_url)
		{
			$this->saveImageUrl($obj);
		}
	}

	/**
	 * Load the image as specified in the record
	 *
	 * @param   \stdClass  $obj  The entire row from import
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	protected function saveImage($obj)
	{
		$imageUrl = rtrim($obj->image_folder . '/' . $obj->image_filename, '/ ');
		$imageUrl = $this->parseImageCode($obj, $imageUrl);

		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		if (!is_file(\JPath::clean(JPATH_SITE . '/' . $imageUrl)))
		{
			return true;
		}

		// Use this image for variant/product/category:
		// id, table_name, record_id, context, original_name, type, path, size, state
		if ($obj->x__variant_id)
		{
			$tableName = 'variants';
			$recordId  = $obj->x__variant_id;
		}
		elseif ($obj->x__product_id)
		{
			$tableName = 'products';
			$recordId  = $obj->x__product_id;
		}
		else
		{
			$cat_ids = json_decode($obj->x__category_ids, true) ?: array();
			$catid   = reset($cat_ids);

			if (!$catid)
			{
				return true;
			}

			$tableName = 'categories';
			$recordId  = $catid;
		}

		$filename  = basename($imageUrl);
		$directory = $this->helper->media->getBaseDir(sprintf('%s/images/%d', $tableName, $recordId));
		$directory = ltrim($directory, '/');

		if (\JFolder::create(JPATH_SITE . '/' . $directory) && \JFile::copy($imageUrl, $directory . '/' . $filename, JPATH_SITE))
		{
			// Todo: check for uniqueness
			$image = new \stdClass;

			$image->table_name    = $tableName;
			$image->context       = 'images';
			$image->record_id     = $recordId;
			$image->path          = $directory . '/' . $filename;
			$image->original_name = $imageUrl;
			$image->type          = MediaHelper::getMimeType(JPATH_SITE . '/' . $directory . '/' . $filename);
			$image->size          = filesize(JPATH_SITE . '/' . $directory . '/' . $filename);
			$image->state         = 1;
			$image->created       = \JFactory::getDate()->toSql();

			$db = $this->importer->getDb();

			return $db->insertObject('#__sellacious_media', $image, 'id');
		}

		return false;
	}

	/**
	 * Load the image as specified in the record from a remote URL
	 *
	 * @param   \stdClass  $obj  The entire row from import
	 *
	 * @return  bool
	 *
	 * @since   1.5.0
	 */
	protected function saveImageUrl($obj)
	{
		$imageUrl = $obj->image_url;
		$imageUrl = $this->parseImageCode($obj, $imageUrl);

		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// People often forget http(s), we can at most detect a 'www.'
		if (substr($imageUrl, 0, 4) == 'www.')
		{
			$imageUrl = 'http://' . $imageUrl;
		}

		if (substr($imageUrl, 0, 7) != 'http://' && substr($imageUrl, 0, 8) != 'https://')
		{
			// This may be a local file, no B/C
			return false;
		}

		// Use this image for variant/product/category: id, table_name, record_id, context, original_name, type, path, size, state
		if ($obj->x__variant_id)
		{
			$tableName = 'variants';
			$recordId  = $obj->x__variant_id;
		}
		elseif ($obj->x__product_id)
		{
			$tableName = 'products';
			$recordId  = $obj->x__product_id;
		}
		else
		{
			$cat_ids = json_decode($obj->x__category_ids, true) ?: array();
			$catid   = reset($cat_ids);

			if (!$catid)
			{
				return true;
			}

			$tableName = 'categories';
			$recordId  = $catid;
		}

		$filename  = basename($imageUrl);
		$directory = $this->helper->media->getBaseDir(sprintf('%s/images/%d', $tableName, $recordId));
		$directory = ltrim($directory, '/');

		// We'll download this image later in a separate batch. Put a placeholder for now
		$placeholder = \JHtml::_('image', 'com_importer/coming-soon-placeholder.png', '', null, true, 1);
		$placeholder = $placeholder ? substr($placeholder, strlen(rtrim(\JUri::root(true), '\\/'))) : null;

		if (is_file(JPATH_SITE . $placeholder) &&
			\JFolder::create(JPATH_SITE . '/' . $directory) &&
			\JFile::copy($placeholder, $directory . '/' . $filename, JPATH_SITE))
		{
			$params = array(
				'remote_download' => true,
				'download_url'    => $imageUrl,
			);

			// Todo: check for uniqueness
			$image = new \stdClass;

			$image->table_name    = $tableName;
			$image->context       = 'images';
			$image->record_id     = $recordId;
			$image->path          = $directory . '/' . $filename;
			$image->original_name = $filename;
			$image->type          = 'image/generic';
			$image->size          = 0;
			$image->state         = -1;
			$image->params        = json_encode($params);
			$image->created       = \JFactory::getDate()->toSql();

			$db = $this->importer->getDb();

			return $db->insertObject('#__sellacious_media', $image, 'id');
		}

		return false;
	}

	/**
	 * Process the embedded short codes in the image path/url
	 *
	 * @param   \stdClass  $obj       The import record
	 * @param   string     $imageUrl  The path/url to process
	 *
	 * @return  string
	 *
	 * @since   1.6.0
	 */
	protected function parseImageCode($obj, $imageUrl)
	{
		static $pattern;

		if (strpos($imageUrl, '%') !== false)
		{
			// Optimize! Build pattern only once (We use the headers as short-code)
			if (!$pattern)
			{
				$headers    = array();
				$headersAll = $this->importer->getHeaders();

				foreach ($headersAll as $header)
				{
					$headers[] = '%' . preg_quote($header, '/') . '%';
				}

				$pattern = '/(' . implode('|', $headers) . ')/i';
			}

			$matches = array();

			preg_match_all($pattern, $imageUrl, $matches, PREG_SET_ORDER);

			foreach ($matches as $match)
			{
				$key = strtolower($match[1]);

				$imageUrl = str_replace($match[0], isset($obj->$key) ? $obj->$key : '', $imageUrl);
			}
		}

		// If there is no image, do not proceed here
		if (strlen($imageUrl) < 5)
		{
			return null;
		}

		// Check for an allowed image file type
		$ext = substr($imageUrl, -4);

		return ($ext == '.jpg' || $ext == '.png' || $ext == '.gif') ? $imageUrl : null;
	}
}
