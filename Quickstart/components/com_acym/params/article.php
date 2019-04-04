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

class JFormFieldArticle extends JFormField
{
    var $type = 'article';

    function getInput()
    {
        $modalId = 'acym_article_'.$this->id;
        $callback = 'jSelectArticle_'.$this->id;

        $title = '';
        $value = intval($this->value);
        if (!empty($value)) {
            $title = acym_CMSArticleTitle($value);
        }

        acym_addScript(
            true,
            "
            function $callback(id, title, catid, object, url, language) {
                window.processModalSelect('Article', '".$this->id."', id, title, catid, object, url, language);
                toggle_$callback(id);
                jQuery('#".$modalId."').modal('hide');
            }
            
            function toggle_$callback(selection) {
                if (selection && selection > 0) {
                    jQuery('#button_$modalId').hide();
                    jQuery('#clear_$modalId').show();
                } else {
                    jQuery('#".$this->id."_name').val('');
                    jQuery('#".$this->id."_id').val('');
                    
                    jQuery('#button_$modalId').show();
                    jQuery('#clear_$modalId').hide();
                }
            }
            
            jQuery(function($){
                toggle_$callback($value);
            });"
        );

        $html = '<span class="input-append">';
        $html .= '<input class="input-medium" id="'.$this->id.'_name" type="text" value="'.acym_escape($title).'" disabled="disabled" size="35" />';
        $urlSelect = acym_articleSelectionPage().'&function='.$callback;
        $html .= acym_cmsModal(true, $urlSelect, 'ACYM_SELECT', true, $modalId);
        $html .= '<a id="clear_'.$modalId.'" class="btn hasTooltip" data-toggle="modal" role="button" onclick="toggle_'.$callback.'(0);">'.acym_translation('ACYM_CLEAR').'</a>';
        $html .= '</span>';

        $html .= '<input type="hidden" id="'.$this->id.'_id" name="'.$this->name.'" value="'.$value.'" />';

        return $html;
    }

    function getLabel()
    {
        return str_replace($this->id, $this->id.'_id', parent::getLabel());
    }
}
