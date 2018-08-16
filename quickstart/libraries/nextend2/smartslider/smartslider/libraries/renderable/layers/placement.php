<?php

abstract class  N2SSSlidePlacement {

    /** @var  N2SSSlideComponent */
    protected $component;

    protected $index = 1;

    protected $style = '';
    protected $attributes = '';

    /**
     * N2SSSlidePlacementAbsolute constructor.
     *
     * @param N2SSSlideComponent $component
     * @param int                $index
     */
    public function __construct($component, $index) {
        $this->component = $component;
        $this->index     = $index;
    }

    /**
     * @param array $attributes
     */
    public function attributes(&$attributes) {

    }

    /**
     * @param array $attributes
     */
    public function adminAttributes(&$attributes) {
    }
}

N2Loader::importAll("libraries.renderable.layers.placement", "smartslider");