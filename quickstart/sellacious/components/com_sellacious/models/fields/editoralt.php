<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('editor');

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for wysiwyg editor only when user clicks on edit button within
 */
class JFormFieldEditorAlt extends JFormFieldEditor
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'editorAlt';

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
		$editor = parent::getInput();
		$value  = $this->value;
		$text   = JText::_('COM_SELLACIOUS_BUTTON_WYSIWYG_LABEL');

		$html = <<<HTML
			<div id="{$this->id}_viewer" class="bg-color-white padding-5" style="border: 1px solid #dedede;">
				<button type="button" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i> {$text} </button>
				<div class="margin-top-5"><div class="clearfix"></div>{$value}</div>
			</div>
			<div id="{$this->id}_editor" class="hidden">{$editor}</div>
HTML;

		JHtml::_('jquery.framework');

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration("
			(function ($) {
				$(document).ready(function () {
					$('#{$this->id}_viewer').find('button').click(function() {
						$('#{$this->id}_viewer').addClass('hidden');
						$('#{$this->id}_editor').removeClass('hidden');
					});
				});
			})(jQuery);
		");

		return $html;
	}
}
