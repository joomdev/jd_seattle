<?php
N2Loader::import('libraries.form.tab');

class N2TabHorizontal extends N2Tab {

    protected function decorateTitle() {
        echo "<div class='n2-form-tab-horizontal'>";
    }

    protected function decorateGroupStart() {
        echo '<div>';
    }

    protected function decorateGroupEnd() {
        echo "</div>";
        echo "</div>";
    }

    protected function decorateElement($el, $renderedElement) {
        echo N2Html::tag('div', array(
            'class' => 'n2-inline-block ' . $el->getRowClass()
        ), N2Html::tag('div', array(
            'class' => 'n2-form-element-mixed'
        ), N2Html::tag('div', array(
                'class' => 'n2-mixed-label'
            ), $renderedElement[0]) . N2Html::tag('div', array(
                'class' => 'n2-mixed-element'
            ), $renderedElement[1])));


    }
}