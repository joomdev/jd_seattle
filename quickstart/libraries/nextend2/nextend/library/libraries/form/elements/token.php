<?php

class N2ElementToken extends N2Element {

    protected function fetchTooltip() {
        return $this->fetchNoTooltip();
    }

    protected function fetchElement() {
        $this->rowClass = 'n2-hidden';

        return N2Form::tokenize();
    }
}
