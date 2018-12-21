<?php

class N2SmartSliderSlideBuilderLayer extends N2SmartSliderSlideBuilderComponent {

    protected $defaultData = array(
        "type"                        => 'layer',
        "eye"                         => false,
        "lock"                        => false,
        "animations"                  => array(
            "specialZeroIn"       => 0,
            "transformOriginIn"   => "50|*|50|*|0",
            "inPlayEvent"         => "",
            "loopRepeatSelfOnly"  => 0,
            "repeatCount"         => 0,
            "repeatStartDelay"    => 0,
            "transformOriginLoop" => "50|*|50|*|0",
            "loopPlayEvent"       => "",
            "loopPauseEvent"      => "",
            "loopStopEvent"       => "",
            "transformOriginOut"  => "50|*|50|*|0",
            "outPlayEvent"        => "",
            "instantOut"          => 1,
            "in"                  => array(),
            "loop"                => array(),
            "out"                 => array()
        ),
        "id"                          => null,
        "parentid"                    => null,
        "name"                        => "Layer",
        "namesynced"                  => 1,
        "crop"                        => "visible",
        "inneralign"                  => "left",
        "parallax"                    => 0,
        "adaptivefont"                => 0,
        "desktopportrait"             => 1,
        "desktoplandscape"            => 1,
        "tabletportrait"              => 1,
        "tabletlandscape"             => 1,
        "mobileportrait"              => 1,
        "mobilelandscape"             => 1,
        "responsiveposition"          => 1,
        "responsivesize"              => 1,
        "desktopportraitleft"         => 0,
        "desktopportraittop"          => 0,
        "desktopportraitwidth"        => "auto",
        "desktopportraitheight"       => "auto",
        "desktopportraitalign"        => "center",
        "desktopportraitvalign"       => "middle",
        "desktopportraitparentalign"  => "center",
        "desktopportraitparentvalign" => "middle",
        "desktopportraitfontsize"     => 100

    );

    /** @var N2SmartSliderSlideBuilderItem */
    public $item;

    /**
     * N2SmartSliderSlideBuilderLayer constructor.
     *
     * @param N2SmartSliderSlideBuilderComponent $container
     * @param string                             $item
     */
    public function __construct($container, $item) {

        $container->add($this);

        new N2SmartSliderSlideBuilderItem($this, $item);
    }

    /**
     * @param $component N2SmartSliderSlideBuilderItem
     */
    public function add($component) {
        $this->item = $component;

        foreach ($this->item->getLayerProperties() AS $k => $v) {
            if ($k == 'width' || $k == 'height' || $k == 'top' || $k == 'left') {
                $this->defaultData['desktopportrait' . $k] = $v;
            } else {
                $this->defaultData[$k] = $v;
            }
        }
        $this->defaultData['name'] = $this->item->getLabel() . ' layer';
    }

    public function getData() {
        $this->data['item'] = $this->item->getData();


        return parent::getData();
    }
}