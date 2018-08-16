<?php
/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

class JFormFieldResetthumbs extends JFormField {

	protected $type = 'Resetthumbs';

	protected function getInput() {

		Jhtml::_('jquery.framework');
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('jQuery(function($) {
			$("#btn-reset-thumbs").on("click", function() {
				$(this).attr("disabled","disabled").text($(this).data("generating"));
			});
		});');

		$url = 'index.php?option=com_spsimpleportfolio&task=resetThumbs';

		return '<a id="btn-reset-thumbs" class="btn btn-primary" data-generating="'. JText::_('COM_SPPORTFOLIO_RESET_THUMBNAIL_TEXT_LOADING') .'" href="'. $url .'">'. JText::_('COM_SPPORTFOLIO_RESET_THUMBNAIL_TEXT') .'</a>';
	}
}
