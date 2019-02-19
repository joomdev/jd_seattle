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

class JFormFieldCropImage extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'CropImage';

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
		if ($this->hidden)
		{
			return '<input type="hidden" id="' . $this->id . '" />';
		}

		$record_id = (int)$this->element['record_id'];

		if (empty($record_id))
		{
			return '<div class="alert adjusted alert-warning fade in"><i class="fa fa-fw fa-lg fa-exclamation"></i>' .
						JText::_('COM_SELLACIOUS_ADD_IMAGES_SAVE_ITEM_FIRST') . '</div>';
		}

		JHtml::_('jquery.framework');

		JHtml::_('stylesheet', 'media/com_sellacious/js/plugin/imageareaselect/css/imgareaselect-animated.css');
		JHtml::_('stylesheet', 'com_sellacious/field.cropimage.css', array('version' => S_VERSION_CORE, 'relative' => true));

		JHtml::_('script', 'media/com_sellacious/js/plugin/imageareaselect/jquery.imgareaselect.pack.js');
		JHtml::_('script', 'media/com_sellacious/js/plugin/superbox/superbox.js');
		JHtml::_('script', 'com_sellacious/field.cropimage.js', array('version' => S_VERSION_CORE, 'relative' => true));

		$helper  = SellaciousHelper::getInstance();
		$rename  = (int)$this->element['rename'];
		$context = (string)$this->element['context'];
		$context = explode('.', $context);

		// Load value automatically, don't depend on model
		$filter  = array(
			'list.select' => 'a.id, a.path, a.state, a.original_name',
			'table_name'  => $context[0],
			'context'     => $context[1],
			'record_id'   => $record_id
		);
		$this->value = $helper->media->loadObjectList($filter);

		$token = JSession::getFormToken();
		$doc   = JFactory::getDocument();
		$doc->addScriptDeclaration("
			(function ($) {
				$(document).ready(function () {
					var o = new JFormFieldCropimage;
					o.setup({
						wrapper : '{$this->id}_wrapper',
						insertAt: '.superbox-list-add',
						siteRoot: '" . JUri::root(true) . "',
						target: 'table={$context[0]}&record_id={$record_id}&context={$context[1]}&rename={$rename}&{$token}=1',
					});
				});
			})(jQuery);
		");

		$properties = get_object_vars($this);
		$options    = array('client' => 2, 'debug' => 0);
		$html       = JLayoutHelper::render('com_sellacious.formfield.cropimage', $properties, '', $options);

		return $html;
	}

}
