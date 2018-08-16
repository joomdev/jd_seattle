<?php

class N2ElementUrlParams {

    public static function extend($params) {
        $params['labelButton']      = n2_('Joomla');
        $params['labelDescription'] = n2_('Select article or menu item from your site.');
        $params['image']            = '/element/link_platform.png';

        return $params;
    }
}