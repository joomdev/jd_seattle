<?php

N2Loader::import('libraries.form.elements.subform');

abstract class N2ElementSubformImage extends N2ElementSubform {

    protected $labels = array();
    var $hasTooltip = false;

    protected $isSmall = false;

    function renderSelector() {
        $html = '<div style="display: none;">';
        $html .= parent::renderSelector();
        $html .= '</div>';
        foreach ($this->plugins AS $plugin) {
            $html .= $this->getOptionHtml($plugin);
        }

        N2JS::addInline('
            new N2Classes.FormElementSubform(
               "' . $this->fieldID . '",
              "' . $this->ajaxUrl . '",
               "nextend-' . $this->name . '-panel",
               "' . $this->parent->getName() . '",
               "' . $this->getValue() . '"
            );
        ');
        N2JS::addInline('
            new N2Classes.FormElementSubformImage(
              "' . $this->fieldID . '",
              "' . $this->fieldID . '_options"
            );
        ');

        $GLOBALS['nextend-' . $this->name . '-panel'] = $this->renderForm();

        if (count($this->options) <= 1) {
            $this->class = 'n2-hidden';
            $this->parent->hide();
        }

        return N2Html::tag('div', array(
            'class' => 'n2-subform-image' . ($this->isSmall ? ' n2-small' : ''),
            'id'    => $this->fieldID . '_options'
        ), $html);
    }

    protected function getOptionHtml($plugin) {
        return N2Html::tag('div', array(
            'class' => 'n2-subform-image-option ' . $this->isActive($plugin->getName())
        ), N2Html::tag('div', array(
                'class' => 'n2-subform-image-element',
                'style' => 'background-image: URL(' . N2Uri::pathToUri(N2Filesystem::translate($plugin->getSubFormImagePath())) . ');'
            )) . N2Html::tag('div', array(
                'class' => 'n2-subform-image-title n2-h4'
            ), $plugin->getLabel()));
    }


    protected function isActive($value) {
        if ($this->getValue() == $value) {
            return 'n2-active';
        }

        return '';
    }
}