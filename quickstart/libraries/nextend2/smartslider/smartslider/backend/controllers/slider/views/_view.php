<?php


class N2SmartsliderBackendSliderView extends N2ViewBase {

    public function _renderSlider($sliderId, $responsive = 'auto') {

        $slider = new N2SmartSliderManager($sliderId, false, array(
            'disableResponsive'     => true,
            'addDummySlidesIfEmpty' => true
        ));
        echo $slider->render();
    }

    public function _renderSliderCached($sliderId, $responsive = 'auto') {

        $slider = new N2SmartSliderManager($sliderId, false, array(
            'disableResponsive' => true
        ));
        echo $slider->render(true);
    }

    public function renderForm($slider) {


        $values = N2SmartsliderSlidersModel::renderEditForm($slider);
        // Used by AJAX widget subforms
        N2JS::addFirstCode("
            new N2Classes.Form(
                'n2-ss-edit-slider-form',
                '" . $this->appType->router->createAjaxUrl(array(
                'slider/edit',
                array('sliderid' => $slider['id'])
            )) . "',
                " . json_encode($values) . "
            );
        ");

    }

    public function getDashboardButtons($slider) {
        $sliderid = $slider['id'];

        $app          = N2Base::getApplication('smartslider');
        $accessEdit   = N2Acl::canDo('smartslider_edit', $app->info);
        $accessDelete = N2Acl::canDo('smartslider_delete', $app->info);

        $buttons = '';

        if ($accessEdit) {

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Quick Edit - Slides'),
                'href'       => '#',
                'id'         => 'n2-quick-slides-edit'
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-slideedit')));

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Clear slider cache'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/clearcache',
                    array(
                        'sliderid' => $sliderid
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-clear')));
            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Export slider as HTML'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/exporthtml',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-html')));

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Export'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/export',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-export')));
        

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Duplicate slider'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/duplicate',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-duplicate')));

        }

        if ($accessDelete) {
            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Delete slider'),
                "onclick"    => "return N2Classes.NextendModal.deleteModalLink(this, 'slider-delete', " . json_encode($slider['title']) . ");",
                'href'       => $this->appType->router->createUrl(array(
                    'slider/delete',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-delete')));
        }

        return $buttons;
    }

    public function getGroupButtons($slider) {
        $sliderid = $slider['id'];

        $app          = N2Base::getApplication('smartslider');
        $accessEdit   = N2Acl::canDo('smartslider_edit', $app->info);
        $accessDelete = N2Acl::canDo('smartslider_delete', $app->info);

        $buttons = '';

        if ($accessEdit) {
            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Clear cache'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/clearcache',
                    array(
                        'sliderid' => $sliderid
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-clear')));
            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Export as HTML'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/exporthtml',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-html')));

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Export'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/export',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-export')));
        

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Duplicate group'),
                'href'       => $this->appType->router->createUrl(array(
                    'slider/duplicate',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-duplicate')));

        }

        if ($accessDelete) {
            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Delete group'),
                "onclick"    => "return N2Classes.NextendModal.deleteModalLink(this, 'slider-delete', " . json_encode($slider['title']) . ");",
                'href'       => $this->appType->router->createUrl(array(
                    'slider/delete',
                    array(
                        'sliderid' => $sliderid,
                    ) + N2Form::tokenizeUrl()
                ))
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-delete')));
        }

        return $buttons;
    }


    public function renderGroupForm($slider) {


        $values = N2SmartsliderSlidersModel::renderGroupEditForm($slider);

        // Used by AJAX widget subforms
        N2JS::addFirstCode("
            new N2Classes.Form(
                'n2-ss-edit-group-form',
                '" . $this->appType->router->createAjaxUrl(array(
                'slider/edit',
                array('sliderid' => $slider['id'])
            )) . "',
                " . json_encode($values) . "
            );
        ");

    }
} 