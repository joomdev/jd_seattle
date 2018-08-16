<?php
N2Loader::import('libraries.form.elements.list');

abstract class N2ElementSubform extends N2ElementList {

    protected $plugins = array();

    protected $file = '';

    protected $ajaxUrl = '';

    public function __construct($parent, $name = '', $label = '', $default = '', $ajaxUrl = '', $parameters = array()) {

        $this->ajaxUrl = $ajaxUrl;

        parent::__construct($parent, $name, $label, $default, $parameters);

        $this->loadOptions();
    }

    protected function fetchElement() {

        if (count($this->options) === 0) return 'No sub form exists...';
        if (!isset($this->options[$this->getValue()])) {
            reset($this->options);
            $this->setValue(key($this->options));
        }

        $html = $this->renderSelector();

        return N2Html::tag("div", array(
            "class" => "n2-subform " . $this->class,
            "style" => $this->style,
        ), $html);
    }

    protected function renderSelector() {
        return parent::fetchElement();
    }


    protected abstract function loadOptions();

    protected function renderForm() {

        $form = new N2Form($this->getForm()->appType);

        $form->loadArray($this->getForm()
                              ->toArray());

        $this->getCurrentPlugin($this->getValue())
             ->renderFields($form);

        ob_start();

        $form->render($this->control_name);

        return ob_get_clean();

    }

    protected function getCurrentPlugin($value) {

        if (!isset($this->plugins[$value])) list($value) = array_keys($this->plugins);

        return $this->plugins[$value];
    }


    /**
     * @param string $file
     */
    public function setFile($file) {
        $this->file = $file;
    }
}