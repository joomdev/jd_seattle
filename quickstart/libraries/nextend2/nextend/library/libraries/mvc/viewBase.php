<?php


class N2ViewBase {

    /** @var  N2ApplicationType */
    public $appType;

    /**
     * N2ViewBase constructor.
     *
     * @param N2ApplicationType $appType
     */
    public function __construct($appType) {
        $this->appType = $appType;
    }
}
