<?php
jimport('joomla.form.formfield');

class JFormFieldEditSlider extends JFormField {

    protected $type = 'EditSlider';

    public function getInput() {
        $style = '<style>#jform_params_slider_chzn{width:100% !important;max-width:500px;}</style>';

        return $style . '<a href="#" onclick="window.open(\'' . JUri::root() . 'administrator/index.php?option=com_smartslider3&nextendcontroller=slider&nextendaction=edit&sliderid=\' + jQuery(\'#jform_params_slider\').val(), \'_blank\'); return false;" class="btn btn-small btn-success" target="_blank"><span class="icon-apply icon-white"></span>Edit selected slider</a>';
    }
}