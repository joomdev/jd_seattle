<?php

class N2SmartsliderBackendSliderControllerAjax extends N2SmartSliderControllerAjax {

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides',
            'models.generator'
        ), 'smartslider');
    }

    public function actionCreate() {
        $this->validateToken();
        $this->validatePermission('smartslider_edit');
        $slidersModel = new N2SmartsliderSlidersModel();

        $title = N2Request::getVar('sliderTitle');
        $this->validateVariable(!empty($title), 'slider name');

        $slider = array(
            'type'   => 'simple',
            'title'  => N2Request::getVar('sliderTitle'),
            'width'  => max(N2Request::getInt('sliderSizeWidth', 800), 50),
            'height' => max(N2Request::getInt('sliderSizeHeight', 500), 50)
        );

        $preset = N2Request::getVar('preset');
        switch ($preset) {
            case 'fullwidth':
                $slider['responsive-mode'] = 'fullwidth';
                $slider['widgetarrow']     = 'imageEmpty';
                break;
            case 'fullpage':
                $slider['responsive-mode'] = 'fullpage';
                $slider['widgetarrow']     = 'imageEmpty';
                break;
            case 'block':
                $slider['type']            = 'block';
                $slider['responsive-mode'] = 'fullwidth';
                break;
            case 'showcase':
                $slider['type']         = 'showcase';
                $slider['slide-width']  = intval($slider['width'] * 0.8);
                $slider['slide-height'] = intval($slider['height'] * 0.8);
                break;
            case 'carousel':
                $slider['responsiveScaleUp'] = 1;
                $slider['type']              = 'carousel';
                $slider['slide-width']       = intval(($slider['width'] - 40) * 0.32);
                $slider['slide-height']      = intval($slider['height'] * 0.8);
                $slider['widgetbullet']      = 'transitionRectangle';
                $slider['widgetarrow']       = 'disabled';
                break;
            case 'thumbnailhorizontal':
                $slider['widgetthumbnail'] = 'default';
                break;
            case 'thumbnailvertical':
                $slider['widgetthumbnail']                = 'default';
                $slider['widget-thumbnail-position-area'] = '8';
                break;
            case 'caption':
                $slider['widgetarrow'] = 'imageEmpty';
                $slider['widgetbar']   = 'horizontalFull';
                break;
            case 'horizontalaccordion':
                $slider['type']        = 'accordion';
                $slider['orientation'] = 'horizontal';
                break;
            case 'verticalaccordion':
                $slider['type']        = 'accordion';
                $slider['orientation'] = 'vertical';
                break;
            default:
                $slider['widgetarrow'] = 'imageEmpty';
        }

        $sliderid = $slidersModel->create($slider, N2Request::getVar('groupID', 0));

        N2Message::success(n2_('Slider created.'));

        $this->response->redirect(array(
            "slider/edit",
            array("sliderid" => $sliderid)
        ));
    }

    public function actionEdit() {
        $this->validateToken();
        $this->validatePermission('smartslider_edit');

        if (N2Request::getInt('save')) {

            $slidersModel = new N2SmartsliderSlidersModel();

            $slider = $slidersModel->get(N2Request::getInt('sliderid'));
            $this->validateDatabase($slider);
            
            $responseData = $slidersModel->save($slider['id'], N2Request::getVar('slider'));
            if ($responseData !== false) {
                N2Message::success(n2_('Slider saved.'));
                $this->response->respond($responseData);
            }
        }
    }

    public function actionImportDemo() {
        $this->validateToken();
        $this->validatePermission('smartslider_edit');

        $key = 'http:' . n2_base64_decode(N2Request::getVar('key'));
        if (strpos($key, 'http://smartslider3.com/') !== 0) {
            N2Message::error(sprintf(n2_('Import url is not valid: %s'), $key));
            $this->response->error();
        }
        N2Base::getApplication('smartslider')->storage->set('free', 'subscribeOnImport', 1);
    

        $posts  = array(
            'action'  => 'asset',
            'asset'   => $key,
            'version' => N2SS3::$version
        );
        $result = N2SS3::api($posts);

        if (!is_string($result)) {
            $hasError = N2SS3::hasApiError($result['status'], array(
                'key' => $key
            ));
            if (is_array($hasError)) {
                $this->redirect($hasError);
            } else if ($hasError !== false) {
                $this->response->error();
            }
        } else {

            N2Loader::import(array(
                'models.Sliders',
                'models.Slides'
            ), 'smartslider');

            N2Loader::import('libraries.import', 'smartslider');

            $import   = new N2SmartSliderImport();
            $sliderId = $import->import($result, N2Request::getVar('groupID', 0), 'clone', 1, false);

            if ($sliderId !== false) {
                N2Message::success(n2_('Slider imported.'));

                $this->response->redirect(array(
                    "slider/edit",
                    array("sliderid" => $sliderId)
                ));
            } else {
                N2Message::error(n2_('Import error!'));
                $this->response->error();
            }
        }

        $this->response->respond();
    }


    public function actionDuplicate() {
        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $sliderId = N2Request::getInt('sliderid');
        $this->validateVariable($sliderId > 0, 'Slider');

        $slidersModel = new N2SmartsliderSlidersModel();
        $newSliderId  = $slidersModel->duplicate($sliderId, true);
        $slider       = $slidersModel->getWithThumbnail($newSliderId);

        $this->validateDatabase($slider);

        N2Message::success(n2_('Slide duplicated.'));

        $this->addView('sliderbox', array(
            'slider' => $slider
        ));
        ob_start();
        $this->render();
        $box = ob_get_clean();
        $this->response->respond($box);
    }

    public function actionCreateGroup() {
    }

    public function actionRemoveFromGroup() {
    }

    public function actionAddToGroup() {
    }

    public function actionRenderSliderType() {
        $this->validateToken();


        $sliderType = N2Post::getVar('value');

        $values = N2Request::getVar('values', array());

        $form = new N2Form($this->appType);
        $form->loadArray($values);

        n2_ob_end_clean_all(); // To clear the output of the platform
        ob_start();

        try {
            N2SSPluginSliderType::getSliderType($sliderType)
                                ->renderFields($form);
        } catch (Exception $e) {
            $this->response->respond(array('error' => $e->getMessage()));
            exit;
        }

        $form->render('slider');

        $scripts = N2AssetsManager::generateAjaxJS();
        $html    = ob_get_clean();
        $this->response->respond(array(
            'html'    => $html,
            'scripts' => $scripts
        ));
    }

    public function actionRenderResponsiveType() {
        $this->validateToken();

        $responsiveType = N2SSPluginSliderResponsive::getType(N2Post::getVar('value'));
        if ($responsiveType) {
            $values = N2Request::getVar('values', array());

            $form = new N2Form($this->appType);
            $form->loadArray($values);

            n2_ob_end_clean_all(); // To clear the output of the platform
            ob_start();

            $responsiveType->renderFields($form);
            $form->render('slider');

            $scripts = N2AssetsManager::generateAjaxJS();
            $html    = ob_get_clean();
            $this->response->respond(array(
                'html'    => $html,
                'scripts' => $scripts
            ));
        } else {
            $this->response->respond(array('error' => 'Responsive type not found: ' . N2Post::getVar('value')));
        }
    }

    public function actionRenderWidgetArrow() {

        $this->renderWidgetForm('arrow');
    }

    public function actionRenderWidgetAutoplay() {

        $this->renderWidgetForm('autoplay');
    }

    public function actionRenderWidgetBar() {

        $this->renderWidgetForm('bar');
    }

    public function actionRenderWidgetBullet() {

        $this->renderWidgetForm('bullet');
    }

    public function actionRenderWidgetFullscreen() {

        $this->renderWidgetForm('fullscreen');
    }

    public function actionRenderWidgetHtml() {

        $this->renderWidgetForm('html');
    }

    public function actionRenderWidgetIndicator() {

        $this->renderWidgetForm('indicator');
    }

    public function actionRenderWidgetShadow() {

        $this->renderWidgetForm('shadow');
    }

    public function actionRenderWidgetThumbnail() {

        $this->renderWidgetForm('thumbnail');
    }

    private function renderWidgetForm($type) {
        $this->validateToken();

        $group = N2SmartSliderWidgets::getGroup($type);

        $value  = N2Post::getVar('value');
        $widget = $group->getWidget($value);
        if ($widget) {
            $values = N2Request::getVar('values', array());

            $form = new N2Form($this->appType);

            $values = array_merge($widget->getDefaults(), $values);
            $form->loadArray($values);

            n2_ob_end_clean_all(); // To clear the output of the platform
            ob_start();

            $widget->renderFields($form);
            $form->render('slider');

            $scripts = N2AssetsManager::generateAjaxJS();
            $html    = ob_get_clean();
            $this->response->respond(array(
                'html'    => $html,
                'scripts' => $scripts
            ));
        } else {
            $this->response->respond(array('error' => 'Not found: ' . $value));
        }
    }
} 