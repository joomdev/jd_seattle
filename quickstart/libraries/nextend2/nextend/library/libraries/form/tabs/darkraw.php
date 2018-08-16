<?php
N2Loader::import('libraries.form.tab');

class N2TabDarkRaw extends N2Tab {

    protected function decorateGroupStart() {

    }

    protected function decorateGroupEnd() {

        echo "</div>";
    }

    protected function decorateElement($el, $renderedElement) {

        echo $renderedElement[1];
    }

}