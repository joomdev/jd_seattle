<?php
N2Loader::import('libraries.form.elements.subformImage');

class N2ElementWidgetPluginMatrix extends N2ElementSubformImage {

    /** @var N2SSPluginSliderWidget */
    protected $widget;

    protected function loadOptions() {

        $this->plugins['disabled'] = new N2WidgetPluginMatrixDisabledOption();
        $this->plugins += $this->widget->getWidgets();

        uasort($this->plugins, 'N2ElementWidgetPluginMatrix::sortTypes');

        $options = array();
        foreach ($this->plugins AS $name => $type) {
            $options[$name] = '';
        }

        $this->setOptions($options);
    }

    public static function sortTypes($a, $b) {
        return $a->ordering - $b->ordering;
    }

    /**
     * @param N2SSPluginSliderWidget $widget
     */
    public function setWidget($widget) {
        $this->widget = $widget;
    }

    /**
     * @param N2SSPluginWidgetAbstract $plugin
     *
     * @return string
     */
    protected function getOptionHtml($plugin) {
        return N2Html::tag('div', array(
            'class' => 'n2-subform-image-option n2-subform-image-option-simple ' . $this->isActive($plugin->getName())
        ), N2Html::tag('div', array(
            'class' => 'n2-subform-image-element',
            'style' => 'background-image: URL(' . N2Uri::pathToUri(N2Filesystem::translate($plugin->getSubFormImagePath())) . ');'
        )));
    }

    protected function renderForm() {
        $parentForm = $this->getForm();
        $form       = new N2Form($parentForm->appType);

        $widget = $this->getCurrentPlugin($this->getValue());

        $values = array_merge($widget->getDefaults(), $parentForm->toArray());
        $form->loadArray($values);

        $widget->renderFields($form);

        ob_start();

        $form->render($this->control_name);

        return ob_get_clean();

    }
}

class N2WidgetPluginMatrixDisabledOption {

    public $ordering = 0;

    public function getName() {
        return 'disabled';
    }

    public function getSubFormImagePath() {
        return N2ImageHelper::fixed('$ss$/admin/images/widgetdisabled.png', true);
    }

    public function renderFields($form) {
    }

    public function getDefaults() {
        return array();
    }
}