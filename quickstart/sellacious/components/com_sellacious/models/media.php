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
 * Sellacious Media model
 */
class SellaciousModelMedia extends SellaciousModelList
{
	protected $_items;

	/**
	 * Constructor.
	 *
	 * @param    array  $config  An optional associative array of configuration settings.
	 *
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'type', 'a.type',
				'access', 'a.access',
				'ordering', 'a.ordering',
			);
		}

		parent::__construct($config);
	}

	public function getItems()
	{
		if (empty($this->_items))
		{
			// Get current path from request
			$folder = $this->getState('filter.path', '');
			$items  = $this->helper->media->getFileList($folder);

			$this->_items = $items;
		}

		return $this->_items;
	}

	public function getTotal()
	{
		$total = 0;

		$items = $this->getItems();

		if (is_array($items))
		{
			foreach ($items as $item)
			{
				$total += count($item);
			}
		}

		return $total;
	}

	public function getNav()
	{
		$folder = $this->getState('filter.path', '');
		$nav    = $this->helper->media->getNav($folder);

		return $nav;
	}

	public function addFolder($name)
	{
		$newName = JApplicationHelper::stringURLSafe(trim($name));

		if ($newName == '')
		{
			throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FOLDER_NAME_INVALID'));
		}
		elseif ($newName != $name)
		{
			$this->app->enqueueMessage(JText::sprintf('COM_SELLACIOUS_MEDIA_FOLDER_NAME_INVALID_CHANGED', $name, $newName));
		}

		// Get current path from request
		$dest = $this->getState('filter.path', '');

		$done = $this->helper->media->newFolder($dest, $newName);

		if ($done === false)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_FOLDER_CREATE_FAILED'));
		}
	}

	public function delete($files)
	{
		// Get current path from request
		$basedir = $this->getState('filter.path', '');
		$deleted = $this->helper->media->delete($basedir, $files);

		if ($deleted == 0)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_DELETE_FAILED'));
		}

		return $deleted;
	}

	/**
	 * Upload given media and create thumbnails if required
	 *
	 * @param   array  $data
	 * @param   bool   $thumbnail
	 *
	 * @return  int
	 * @throws  Exception
	 */
	public function upload($data, $thumbnail = false)
	{
		// Get current path from request
		$basedir = $this->getState('filter.path', '');

		// add ~ to use media base dir
		$options = array(
			'type'    => null,
			'ignore'  => false,
			'table'   => null,
			'record'  => null,
			'context' => null,
			'rename'  => false
		);

		try
		{
			$result = $this->helper->media->upload('~' . $basedir, '', $options);
		}
		catch (Exception $e)
		{
			throw new Exception($e->getMessage());
		}

		if ($result['upload'] === false)
		{
			throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_UPLOAD_NO_DATA'));
		}

		// create thumb first, then resize original if asked
		if ($thumbnail)
		{
			foreach ($result['upload'] as $file)
			{
				try
				{
					$this->helper->media->resize($file['path'], array('w' => 80, 'h' => 80, 'x' => 0, 'y' => 0), '*-thumb');
				}
				catch (Exception $e)
				{
					throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_IMAGE_THUMBNAIL_CREATED_FAILED') . $e->getMessage());
				}

				if (isset($data['w']) || isset($data['h']))
				{
					try
					{
						$dim = array('w' => $data['w'], 'h' => $data['h'], 'x' => 0, 'y' => 0);
						$this->helper->media->resize($file['path'], $dim, '*');
					}
					catch (Exception $e)
					{
						throw new Exception(JText::_('COM_SELLACIOUS_MEDIA_IMAGE_RESIZE_FAILED') . $e->getMessage());
					}
				}
			}
		}

		return count($result['upload']);
	}
}
