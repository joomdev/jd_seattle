<?php

class N2ElementContainer extends N2Element {

    protected function fetchElement() {

        return N2Html::tag('div', array(
            'id' => $this->fieldID
        ));
    }
}
