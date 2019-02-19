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

/** @var  SellaciousViewEmailTemplate $this */
/** @var  array $tplData */
list($counter, $fs_key, $fieldset) = $tplData;

$fields = $this->form->getFieldset($fieldset->name);
$sOpen  = 0;

echo JHtml::_('bootstrap.startAccordion', 'accordion_' . $fs_key, array('parent' => true, 'toggle' => false));

foreach ($fields as $field):

	if ($field->hidden):
		echo $field->input;
	elseif (strtolower($field->type) == 'fieldgroup'):
		$label = $this->form->getFieldAttribute($field->fieldname, 'label', '', $field->group);

		if ($sOpen):
			echo JHtml::_('bootstrap.endSlide');
			$sOpen--;
		endif;

		echo JHtml::_('bootstrap.addSlide', 'accordion_' . $fs_key, htmlspecialchars(strip_tags($field->input)), $field->id, 'panel');
		$sOpen++;
	else:
		echo '<div class="w100p input-row">';

		if ($field->label == '' || (isset($fieldset->width) && $fieldset->width == 12)):
			echo '	<div class="controls col-md-12">' . $field->input . '</div>';
		else:
			echo '	<div class="form-label col-sm-3 col-md-3 col-lg-2">' . $field->label . '</div>';
			echo '	<div class="controls col-sm-9 col-md-9 col-lg-10">' . $field->input . '</div>';
		endif;

		echo '</div>';
		echo '<div class="clearfix"></div>';
	endif;

endforeach;

if ($sOpen)
{
	echo JHtml::_('bootstrap.endSlide');
	$sOpen--;
}

echo JHtml::_('bootstrap.endAccordion');
