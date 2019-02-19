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

JFormHelper::loadFieldClass('list');

class JFormFieldFileTypes extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'FileTypes';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function getOptions()
	{
		$types = array(
			'jpg'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_JPG'),
			'png'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_PNG'),
			'gif'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_GIF'),
			'tif'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_TIF'),
			'tiff' => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_TIFF'),
			'pdf'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_PDF'),
			'doc'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_DOC'),
			'docx' => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_DOCX'),
			'xls'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_XLS'),
			'xlsx' => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_XLSX'),
			'rtf'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_RTF'),
			'txt'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_TXT'),
			'zip'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_ZIP'),
			'rar'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_RAR'),
			'7z'   => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_7Z'),
			'tar'  => JText::_('COM_SELLACIOUS_FILE_TYPE_EXT_TAR'),
		);

		$options = array();

		foreach ($types as $ext => $name)
		{
			$options[] = JHtml::_('select.option', $ext, $name);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
