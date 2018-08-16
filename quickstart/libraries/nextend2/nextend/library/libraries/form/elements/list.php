<?php
N2Loader::import('libraries.form.elements.hidden');

class N2ElementList extends N2ElementHidden {

    protected $hasTooltip = true;

    public $value;

    protected $values = array();
    protected $options = array();
    protected $optgroup = array();
    protected $isMultiple = false;
    protected $size = '';

    protected $relatedFields = array();

    protected function fetchElement() {

        $this->values = explode('||', $this->getValue());
        if (!is_array($this->values)) {
            $this->values = array();
        }

        $html = N2Html::openTag("div", array(
            "class" => "n2-form-element-list",
            "style" => $this->style
        ));

        $selectAttributes = array(
            'id'           => $this->fieldID . '_select',
            'name'         => 'select' . $this->getFieldName(),
            'autocomplete' => 'off'
        );

        if (!empty($this->size)) {
            $selectAttributes['size'] = $this->size;
        }

        if ($this->isMultiple) {
            $selectAttributes['multiple'] = 'multiple';
            $selectAttributes['class']    = 'nextend-element-hastip';
            $selectAttributes['title']    = n2_('Hold down the ctrl (Windows) or command (MAC) button to select multiple options.');
        }

        $html .= N2Html::tag('select', $selectAttributes, $this->renderOptions($this->options) . (!empty($this->optgroup) ? $this->renderOptgroup() : ''));

        $html .= N2Html::closeTag("div");

        $html .= parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementList("' . $this->fieldID . '", ' . intval($this->isMultiple) . ', ' . json_encode($this->relatedFields) . ');');

        return $html;
    }

    /**
     *
     * @return string
     */
    protected function renderOptgroup() {
        $html = '';
        foreach ($this->optgroup AS $label => $options) {
            $html .= "<optgroup label='" . $label . "'>";
            $html .= $this->renderOptions($options);
            $html .= "</optgroup>";
        }

        return $html;
    }

    /**
     * @param array $options
     *
     * @return string
     */
    protected function renderOptions($options) {
        $html = '';
        foreach ($options AS $value => $label) {
            $html .= '<option value="' . $value . '" ' . $this->isSelected($value) . '>' . $label . '</option>';
        }

        return $html;
    }

    protected function isSelected($value) {
        if (in_array($value, $this->values)) {
            return ' selected="selected"';
        }

        return '';
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        $this->options = $options;
    }

    /**
     * @param array $optgroup
     */
    public function setOptgroup($optgroup) {
        $this->optgroup = $optgroup;
    }

    /**
     * @param bool $isMultiple
     */
    public function setIsMultiple($isMultiple) {
        $this->isMultiple = $isMultiple;
        $this->size       = 10;
    }

    /**
     * @param string $size
     */
    public function setSize($size) {
        $this->size = $size;
    }

    protected function createTree(&$list, &$new, $parent, $cindent = '', $indent = '- ') {

        if (isset($new[$parent])) {
            for ($i = 0; $i < count($new[$parent]); $i++) {
                $new[$parent][$i]->treename = $cindent . $new[$parent][$i]->name;
                $list[]                     = $new[$parent][$i];
                $this->createTree($list, $new, $new[$parent][$i]->cat_ID, $cindent . $indent, $indent);
            }
        }

        return $list;
    }

    /**
     * @param string $relatedFields
     */
    public function setRelatedFields($relatedFields) {
        $this->relatedFields = $relatedFields;
    }
}