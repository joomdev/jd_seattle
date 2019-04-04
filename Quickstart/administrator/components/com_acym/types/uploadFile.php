<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class uploadfileType extends acymClass
{
    function display($map, $value)
    {
        $result = '<input type="hidden" name="'.$map.'[]" id="'.$map.$value.'" />';

        $buttonLoad = acym_translation('ACYM_SELECT');
        $result .= acym_modal(
            $buttonLoad,
            '',
            'acym__campaign__email__'.$map.$value,
            'width="800" style="width:800px;" data-reveal-larger',
            'class="hollow button acym__campaign__attach__button margin-top-0 margin-bottom-0 cell medium-shrink" data-iframe="'.acym_completeLink('file&task=select&id='.$map.$value, true).'" data-ajax="false"'
        );

        $result .= '<span id="'.$map.$value.'selection" class="acy_selected_attachment cell medium-shrink"></span>';

        return $result;
    }
}
