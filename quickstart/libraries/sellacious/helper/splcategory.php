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
use Joomla\Utilities\ArrayHelper;

defined('_JEXEC') or die;

/**
 * Sellacious Special category helper.
 *
 * @since  1.0.0
 */
class SellaciousHelperSplCategory extends SellaciousHelperBase
{
	/**
	 * Get List of images for a given product, if no images are set an array containing one blank image is returned
	 *
	 * @param   int   $pk     Category id of the item
	 * @param   bool  $blank  Whether to return a blank (placeholder) image in case no matching images are found
	 *
	 * @return  string[]
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function getImages($pk, $blank = true)
	{
		if (empty($images))
		{
			$images = $this->helper->media->getImages('splcategories', $pk, false, false);
		}

		$pFiles = $this->helper->media->getFilesFromPattern('splcategories', 'images', array($this, 'replaceKeys'), array($pk));
		$images = array_merge($images, ArrayHelper::getColumn($pFiles, 'path'));

		if ($images)
		{
			foreach ($images as &$image)
			{
				$image = $this->helper->media->getURL($image);
			}
		}
		elseif ($blank)
		{
			$images[] = $this->helper->media->getBlankImage(true);
		}

		return $images;
	}

	/**
	 * Replace short-code from path
	 *
	 * @param   string  $path   File path
	 * @param   int     $catid  Category id
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function replaceKeys($path, $catid)
	{
		// %category_id% | %category_title% | %category_alias%
		$p = explode(',', 'category_id,category_title,category_alias');

		preg_match_all('#%(.*?)%#i', strtolower($path), $matches, PREG_SET_ORDER);

		$keys  = ArrayHelper::getColumn($matches, 1);
		$pKeys = array_intersect($p, $keys);

		if (count($pKeys))
		{
			$filter = array(
				'list.select' => 'a.id AS category_id, a.title AS category_title, a.path AS category_alias',
				'id'          => $catid,
			);

			$obj = $this->loadObject($filter);

			foreach ($pKeys as $key)
			{
				$path = str_ireplace("%$key%", $obj ? $obj->$key : '', $path);
			}
		}

		if (strtoupper(JFile::stripExt(basename($path))) != '%RANDOM%')
		{
			$path = str_ireplace("%RANDOM%", '*', $path);
		}

		return $path;
	}
}
