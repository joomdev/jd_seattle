<?php
N2Loader::import('libraries.form.elements.list');

class N2ElementSkin extends N2ElementList {

    protected $fixed = false;

    protected function fetchElement() {
        N2Localization::addJS('Done');

        $html = parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementSkin("' . $this->fieldID . '", "' . str_replace($this->name, '', $this->fieldID) . '", ' . json_encode($this->options) . ', ' . json_encode($this->fixed) . ');');

        return $html;
    }

    protected function renderOptions($options) {
        $html = '';
        if (!$this->fixed) {
            $html .= '<option value="0" selected="selected">' . n2_('Choose') . '</option>';
        }
        foreach ($options as $value => $option) {
            $html .= '<option ' . $this->isSelected($value) . ' value="' . $value . '">' . $option['label'] . '</option>';
        }

        return $html;
    }

    /**
     * @param bool $fixed
     */
    public function setFixed($fixed) {
        $this->fixed = $fixed;
    }


}
