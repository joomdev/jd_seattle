<?php

class N2SmartsliderBackendGeneratorView extends N2ViewBase {

    public function renderGeneratorBox($group, $button) {
        ob_start();
        N2Html::box(array(
            'attributes'         => array(
                'class' => 'n2-box-generator',
                'style' => 'background-image: URL(' . N2ImageHelper::fixed(N2Uri::pathToUri(N2Filesystem::translate($group->getPath() . '/dynamic.png'))) . ');',

            ),
            'placeholderContent' => N2Html::tag('div', array(
                'class' => 'n2-box-placeholder-button'
            ), $button)
        ));

        return ob_get_clean();
    }
} 