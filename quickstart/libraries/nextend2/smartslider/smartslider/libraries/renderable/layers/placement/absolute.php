<?php

class  N2SSSlidePlacementAbsolute extends N2SSSlidePlacement {

    public function attributes(&$attributes) {
        $data = $this->component->data;

        $attributes['style'] .= 'left:' . $data->get('desktopportraitleft', 0) . 'px;';
        $attributes['style'] .= 'top:' . $data->get('desktopportraittop', 0) . 'px;';
        $attributes['style'] .= 'width:' . self::WHUnit($data->get('desktopportraitwidth')) . ';';
        $attributes['style'] .= 'height:' . self::WHUnit($data->get('desktopportraitheight')) . ';';

        $attributes['data-pm'] = 'absolute';

        $this->component->createProperty('responsiveposition', 1);

        $this->component->createDeviceProperty('left', 0);
        $this->component->createDeviceProperty('top', 0);

        $this->component->createProperty('responsivesize', 1);

        $this->component->createDeviceProperty('width');
        $this->component->createDeviceProperty('height');

        $this->component->createDeviceProperty('align');
        $this->component->createDeviceProperty('valign');

        // Chain
        $attributes['data-parentid'] = $data->get('parentid');
        $this->component->createDeviceProperty('parentalign');
        $this->component->createDeviceProperty('parentvalign');

        //$attributes['style'] .= 'z-index:' . $this->index . ';';
    }

    public function adminAttributes(&$attributes) {

    }

    private static function WHUnit($value) {
        if ($value == 'auto' || substr($value, -1) == '%') {
            return $value;
        }

        return $value . 'px';
    }

    public static function sortOldZIndex($a, $b) {
        if ($a['zIndex'] == $b['zIndex']) {
            return 0;
        }

        return ($a['zIndex'] < $b['zIndex']) ? 1 : -1;
    }
}