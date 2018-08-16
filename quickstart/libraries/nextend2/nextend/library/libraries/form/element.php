<?php

abstract class N2Element {

    /** @var N2FormAbstract */
    protected $form;

    /** @var  N2FormElementContainer */
    protected $parent;

    protected $defaultValue;

    protected $name;

    protected $label = '';

    protected $fieldID;

    protected $fieldName;

    public $control_name = '';

    private $exposeName = true;

    protected $tip = '';

    protected $rowClass = '';

    protected $rowAttributes = array();

    protected $class = '';

    protected $style = '';

    protected $post = '';

    protected $relatedFields = array();

    /**
     * N2Element constructor.
     *
     * @param N2FormElementContainer $parent
     * @param string                 $name
     * @param string                 $label
     * @param string                 $default
     * @param array                  $parameters
     */
    public function __construct($parent, $name = '', $label = '', $default = '', $parameters = array()) {

        $this->parent       = $parent;
        $this->name         = $name;
        $this->label        = $label;
        $this->defaultValue = $default;

        foreach ($parameters AS $option => $value) {
            $option = 'set' . $option;
            $this->{$option}($value);
        }

        $parent->addElement($this);
    }

    /**
     * @param N2FormElementContainer $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * @return N2Form
     */
    public function getForm() {
        return $this->parent->getForm();
    }

    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->fieldID;
    }

    /**
     * @return bool
     */
    public function hasLabel() {
        return !empty($this->label);
    }

    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    public function setExposeName($exposeName) {
        $this->exposeName = $exposeName;
    }

    public function getPost() {
        return $this->post;
    }

    public function setPost($post) {
        $this->post = $post;
    }

    /**
     * @param string $tip
     */
    public function setTip($tip) {
        $this->tip = $tip;
    }

    public function setRowClass($rowClass) {
        $this->rowClass .= $rowClass;
    }

    public function getRowClass() {
        return $this->rowClass;
    }

    public function getClass() {
        return $this->class;
    }

    public function setClass($class) {
        $this->class = $class;
    }

    protected function getFieldName() {
        if ($this->exposeName) {
            return $this->control_name . '[' . $this->name . ']';
        }

        return '';
    }

    public function render($control_name = 'params') {
        $this->control_name = $control_name;
        $this->fieldID      = $this->generateId($control_name . $this->name);

        return array(
            $this->fetchTooltip(),
            $this->fetchElement()
        );
    }

    protected function fetchTooltip() {
        if ($this->label === false) return '';

        if ($this->label == '-') {
            $this->label = '';
        }
        $attributes = array(
            'for' => $this->fieldID
        );
        if (!empty($this->tip)) {
            $attributes['data-n2tip'] = $this->tip;
        }

        return N2Html::tag('label', $attributes, $this->label);
    }

    protected function fetchNoTooltip() {
        return "";
    }

    /**
     * @return string
     */
    abstract protected function fetchElement();

    public function getValue() {
        return $this->getForm()
                    ->get($this->name, $this->defaultValue);
    }

    public function setValue($value) {
        $this->parent->getForm()
                     ->set($this->name, $value);
    }

    protected function generateId($name) {

        return str_replace(array(
            '[',
            ']',
            ' '
        ), array(
            '',
            '',
            ''
        ), $name);
    }

    /**
     * @param array $rowAttributes
     */
    public function setRowAttributes($rowAttributes) {
        $this->rowAttributes = $rowAttributes;
    }

    /**
     * @return array
     */
    public function getRowAttributes() {
        return $this->rowAttributes;
    }

    public function setStyle($style) {
        $this->style = $style;
    }

    protected function getStyle() {
        return $this->style;
    }

    /**
     * @param string $relatedFields
     */
    public function setRelatedFields($relatedFields) {
        $this->relatedFields = $relatedFields;
    }

    protected function renderRelatedFields() {
        if (!empty($this->relatedFields)) {
            N2JS::addInline('new N2Classes.FormRelatedFields("' . $this->fieldID . '", ' . json_encode($this->relatedFields) . ');');
        }
    }
}
