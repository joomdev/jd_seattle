<?php

namespace Nextend\SmartSlider3\Slider\SliderType\Block;

use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\Grouping;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Text\FieldImage;
use Nextend\Framework\Form\Element\Text\TextAutoComplete;
use Nextend\Framework\Form\Element\Text\Video;
use Nextend\Framework\Form\Element\Textarea;
use Nextend\Framework\Form\Fieldset\FieldsetRow;
use Nextend\Framework\Form\Fieldset\LayerWindow\FieldsetLayerWindow;
use Nextend\Framework\Form\Insert\InsertAfter;
use Nextend\Framework\Form\Insert\InsertBefore;
use Nextend\SmartSlider3\Slider\SliderType\AbstractSliderTypeAdmin;
use Nextend\SmartSlider3Pro\Form\Element\PostBackgroundAnimation;
use Nextend\SmartSlider3Pro\PostBackgroundAnimation\PostBackgroundAnimationManager;

class SliderTypeBlockAdmin extends AbstractSliderTypeAdmin {

    protected $ordering = 2;

    public function getLabel() {
        return n2_('Block');
    }

    public function getIcon() {
        return 'ssi_64 ssi_64--block';
    }

    public function prepareForm($form) {
        $form->getElement('/animations')
             ->remove();
    


        $form->getElement('/autoplay')
             ->remove();

        /**
         * Removing slider settings which are unnecessary for Block slider type.
         */
        $form->getElement('/controls/general')
             ->remove();
        $form->getElement('/general/alias/alias-1/alias-slideswitch')
             ->remove();
        $form->getElement('/controls/widget-arrow')
             ->remove();
        $form->getElement('/controls/widget-bullet')
             ->remove();
        $form->getElement('/controls/widget-bar')
             ->remove();
        $form->getElement('/controls/widget-thumbnail')
             ->remove();

    }

    public function renderSlideFields($container) {

    }

    public function registerSlideAdminProperties($component) {
    }
}