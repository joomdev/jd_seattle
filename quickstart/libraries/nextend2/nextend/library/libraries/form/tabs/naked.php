<?php

N2Loader::import('libraries.form.tab');

class N2TabNaked extends N2Tab {

    protected function decorateGroupStart() {

    }

    protected function decorateGroupEnd() {

    }

    protected function decorateTitle() {

    }

    protected function decorateElement($el, $renderedElement) {

        echo $renderedElement[1];
    }

}