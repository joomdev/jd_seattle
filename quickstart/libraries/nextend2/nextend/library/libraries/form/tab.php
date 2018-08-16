<?php
N2Loader::import('libraries.form.element');

/**
 * Class N2Tab
 */
class N2Tab implements N2FormTabContainer, N2FormElementContainer {

    /** @var N2FormTabContainer */
    protected $parent;

    /** @var string */
    protected $name;

    /** @var string */
    protected $label;

    /** @var N2Tab[] */
    protected $tabs = array();

    protected $hideTitle = false;

    /** @var N2Element[] */
    private $elements = array();

    /** @var bool */
    protected $isVisible = true;

    protected $class = '';

    /**
     * N2Tab constructor.
     *
     * @param N2FormTabContainer $parent
     * @param                    $name
     * @param boolean|string     $label
     * @param array              $parameters
     */
    public function __construct($parent, $name, $label = false, $parameters = array()) {
        $this->parent    = $parent;
        $this->name      = $name;
        $this->hideTitle = $label === false;
        $this->label     = $label;

        foreach ($parameters AS $option => $value) {
            $option = 'set' . $option;
            $this->{$option}($value);
        }

        $this->parent->addTab($this);
    }


    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param N2Form|N2Tab $parent
     */
    public function setParent($parent) {
        $this->parent = $parent;
    }

    /**
     * @return N2FormAbstract
     */
    public function getForm() {
        return $this->parent->getForm();
    }

    /**
     * @param string $tab
     *
     * @return N2Tab
     */
    public function getTab($tab) {
        return $this->tabs[$tab];
    }

    /**
     * @param N2Element $element
     */
    public function addElement($element) {
        $name = $element->getName();
        if ($name) {
            $this->elements[$name] = $element;
        } else {
            $this->elements[] = $element;
        }
    }

    /**
     * @param N2Tab $tab
     */
    public function addTab($tab) {
        $this->tabs[$tab->getName()] = $tab;
    }

    public function getElement($name) {
        if (isset($this->elements[$name])) {
            return $this->elements[$name];
        }

        return false;
    }

    /**
     * @param $control_name
     */
    public function render($control_name) {

        ob_start();
        $this->decorateTitle();
        $this->decorateGroupStart();
        $keys = array_keys($this->elements);
        for ($i = 0; $i < count($keys); $i++) {
            $this->decorateElement($this->elements[$keys[$i]], $this->elements[$keys[$i]]->render($control_name));
        }
        $this->decorateGroupEnd();

        if ($this->isVisible) {
            echo ob_get_clean();
        } else {
            echo N2Html::tag('div', array('style' => 'display: none;'), ob_get_clean());
        }

    }

    public function hide() {
        $this->isVisible = false;
    }

    protected function decorateTitle() {
        echo "<div id='n2-tab-" . $this->name . "' class='n2-form-tab " . $this->class . "'>";
        $this->renderTitle();
    }

    protected function renderTitle() {
        if (!$this->hideTitle) {
            echo N2Html::tag('div', array(
                'class' => 'n2-h2 n2-content-box-title-bg'
            ), $this->label);
        }
    }

    protected function decorateGroupStart() {
        echo "<table>";
        echo N2Html::tag('colgroup', array(), N2Html::tag('col', array('class' => 'n2-label-col'), '', false) . N2Html::tag('col', array('class' => 'n2-element-col'), '', false));
    }

    protected function decorateGroupEnd() {
        echo "</table>";
        echo "</div>";
    }

    /**
     * @param N2Element $el
     * @param           $renderedElement
     */
    protected function decorateElement($el, $renderedElement) {

        echo N2Html::openTag('tr', array(
                'class'      => $el->getRowClass(),
                'data-field' => $el->getName()
            ) + $el->getRowAttributes());
        $colSpan = '';
        if ($renderedElement[0] != '') {
            echo "<td class='n2-label" . ($el->hasLabel() ? '' : ' n2-empty-label') . "'>" . $renderedElement[0] . "</td>";
        } else {
            $colSpan = 'colspan="2"';
        }
        echo "<td class='n2-element' {$colSpan}>" . $renderedElement[1] . "</td>";
        echo "</tr>";
    }

    public function removeTab($name) {
        if (isset($this->tabs[$name])) {
            unset($this->tabs[$name]);
        }
    }

    private static function array_insert($array, $values, $offset) {
        return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param string $class
     */
    public function setClass($class) {
        $this->class = $class;
    }


}

class N2TabDark extends N2Tab {

    protected function decorateTitle() {
        echo "<div id='n2-tab-" . $this->name . "' class='n2-form-tab " . $this->class . "'>";
        if ($this->hideTitle != 1) {
            echo N2Html::tag('div', array(
                'class' => 'n2-h3 n2-sidebar-header-bg n2-uc'
            ), $this->label);
        }
    }
}
