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

$data = array(
	'name'  => $this->getName(),
	'state' => $this->state,
	'item'  => $this->item,
	'form'  => $this->form,
);

$options = array(
	'client' => 2,
	'debug'  => 0,
);

JText::script('ERROR');
JText::script('JGLOBAL_VALIDATION_FORM_FAILED');

$assoc = JLanguageAssociations::isEnabled();

// Load Language of mod_smartymenu module
JFactory::getLanguage()->load('mod_smartymenu', JPATH_BASE . '/modules/mod_smartymenu', null, true);
JFactory::getLanguage()->load('mod_smartymenu', JPATH_BASE, null, true);

// Ajax for parent items
$script = <<<'JS'
jQuery(document).ready(function ($) {
	$('#jform_menutype').change(function() {
		var menutype = $(this).val();
		$.ajax({
			url: 'index.php?option=com_menus&task=item.getParentItem&menutype=' + menutype,
			dataType: 'json'
		}).done(function (data) {
			var jformParentId = $('#jform_parent_id');
			jformParentId.find('option').each(function() {
				if ($(this).val() != '1') $(this).remove();
			});
			$.each(data, function (i, val) {
				var option = $('<option>');
				option.text(val.title).val(val.id);
				jformParentId.append(option);
			});
			jformParentId.trigger('liszt:updated');
		});
	});
});

setMenuItemType = function (task, type)
{
	if (task == 'item.setType')
	{
		jQuery('#adminForm').find('input[name="jform[type]"]').val(type);		
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
	else if (task == 'item.setMenuType')
	{
		jQuery('#adminForm').find('input[name="jform[menutype]"]').val(type);	
		Joomla.submitform(task, document.getElementById('adminForm'));
	}
};
JS;

// TODO: Add this behaviour
/*
Joomla.submitbutton = function (task, form) {
	if (!(task == 'item.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))) {
		// Handle validation errors: special case for modal popups validation response
		jQuery('#adminForm').find('.modal-value.invalid').each(function() {
			var field = jQuery(this),
				idReversed = field.attr('id').split('').reverse().join(''),
				separatorLocation = idReversed.indexOf('_'),
				nameId = '#' + idReversed.substr(separatorLocation).split('').reverse().join('') + 'name';
			jQuery(nameId).addClass('invalid');
		});
		return false;
	}
	Joomla.submitform(task, form || document.getElementById('adminForm'));
};
*/

// Add the script to the document head.
JFactory::getDocument()->addScriptDeclaration($script);

echo JLayoutHelper::render('com_sellacious.view.edit', $data, '', $options);
