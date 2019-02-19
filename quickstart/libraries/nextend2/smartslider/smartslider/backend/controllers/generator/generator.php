<?php

class N2SmartsliderBackendGeneratorController extends N2SmartSliderController {

    public $layoutName = 'default1c';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.generator',
            'models.Sliders',
            'models.Slides'
        ), 'smartslider');
    }

    public function actionCreate() {
        if ($this->validatePermission('smartslider_edit')) {

            $sliderID     = N2Request::getInt("sliderid", 0);
            $slidersModel = new N2SmartsliderSlidersModel();
            $slider       = $slidersModel->get($sliderID);
            if ($this->validateDatabase($slider)) {

                $xref   = new N2SmartsliderSlidersXrefModel();
                $groups = $xref->getGroups($slider['id']);
                if (!empty($groups)) {
                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slider/edit",
                            array('sliderid' => $groups[0]['group_id'])
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($groups[0]['title'])));
                }

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $slider['id'])
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => '#',
                    'class' => 'n2-h4 n2-active'
                ), n2_('Add dynamic slides')));

                $this->addView("create-step1-groups", array(
                    "slider" => $slider
                ));
                $this->render();
            }
        }
    }

    public function actionCreateStep2() {
        if ($this->validatePermission('smartslider_edit')) {

            $sliderID     = N2Request::getInt("sliderid", 0);
            $slidersModel = new N2SmartsliderSlidersModel();
            $slider       = $slidersModel->get($sliderID);
            if ($this->validateDatabase($slider)) {

                $generatorGroup = N2SSGeneratorFactory::getGenerator(N2Request::getCmd('group'));
                if (!$generatorGroup) {
                    $this->redirect(array(
                        "generator/create",
                        array(
                            "sliderid" => $sliderID
                        )
                    ), 302, true);
                }
                $sources = $generatorGroup->getSources();

                if (count($sources) == 1) {
                    reset($sources);
                    $this->redirect(array(
                        "generator/createSettings",
                        array(
                            "sliderid" => $sliderID,
                            "group"    => $generatorGroup->getName(),
                            "type"     => $sources[key($sources)]->getName()
                        )
                    ), 302, true);
                }

                $xref   = new N2SmartsliderSlidersXrefModel();
                $groups = $xref->getGroups($slider['id']);
                if (!empty($groups)) {
                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slider/edit",
                            array('sliderid' => $groups[0]['group_id'])
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($groups[0]['title'])));
                }

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $slider['id'])
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "generator/create",
                        array(
                            "sliderid" => $sliderID
                        )
                    )),
                    'class' => 'n2-h4'
                ), n2_('Add dynamic slides')));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => '#',
                    'class' => 'n2-h4 n2-active'
                ), $generatorGroup->getLabel()));

                $this->addView("create-step2-sources", array(
                    "slider"         => $slider,
                    'generatorGroup' => $generatorGroup,
                    'sources'        => $sources
                ));
                $this->render();
            }
        }
    }

    public function actionEdit() {
        if ($this->validatePermission('smartslider_edit')) {

            $generatorId = N2Request::getInt('generator_id');

            $generatorModel = new N2SmartsliderGeneratorModel();
            $generator      = $generatorModel->get($generatorId);
            if ($this->validateDatabase($generator)) {

                N2Request::set('group', $generator['group']);
                N2Request::set('type', $generator['type']);

                $slidesModel = new N2SmartsliderSlidesModel();
                $slides      = $slidesModel->getAll(-1, 'OR generator_id = ' . $generator['id'] . '');
                if (count($slides) > 0) {
                    $slide = $slides[0];

                    N2Request::set('sliderid', $slide['slider']);

                    $slidersModel = new N2SmartsliderSlidersModel();
                    $slider       = $slidersModel->get($slide['slider']);

                    $group = $generator['group'];
                    $type  = $generator['type'];

                    $generatorGroup  = $generatorModel->getGeneratorGroup($group);
                    $generatorSource = $generatorGroup->getSource($type);
                    if (!$generatorSource) {
                        $this->redirect($this->appType->router->createUrl(array(
                            "slides/edit",
                            array(
                                'sliderid' => $slider['id'],
                                'slideid'  => $slide['id']
                            )
                        )));
                    }

                    if (N2Request::getInt('save')) {
                        $request = new N2Data(N2Request::getVar('generator'));

                        $slideParams = new N2Data($slide['params'], true);
                        $slideParams->set('record-slides', $request->get('record-slides', 1));
                        $slidesModel->updateParams($slide['id'], $slideParams->toArray());

                        $request->un_set('record-slides');
                        $generatorModel->save($generatorId, $request->toArray());

                        N2SmartsliderSlidesModel::markChanged($slide['slider']);
                        N2Message::success(n2_('Generator updated and cache cleared.'));

                        $this->redirect(array(
                            "generator/edit",
                            array(
                                "generator_id" => $generatorId
                            )
                        ), 302, true);
                    }

                    $xref   = new N2SmartsliderSlidersXrefModel();
                    $groups = $xref->getGroups($slider['id']);
                    if (!empty($groups)) {
                        $this->layout->addBreadcrumb(N2Html::tag('a', array(
                            'href'  => $this->appType->router->createUrl(array(
                                "slider/edit",
                                array('sliderid' => $groups[0]['group_id'])
                            )),
                            'class' => 'n2-h4'
                        ), n2_esc_html($groups[0]['title'])));
                    }

                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slider/edit",
                            array('sliderid' => $slider['id'])
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($slider['title'])));

                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "slides/edit",
                            array(
                                'sliderid' => $slider['id'],
                                'slideid'  => $slide['id']
                            )
                        )),
                        'class' => 'n2-h4'
                    ), n2_esc_html($slide['title'])));

                    $this->layout->addBreadcrumb(N2Html::tag('a', array(
                        'href'  => $this->appType->router->createUrl(array(
                            "generator/edit",
                            array(
                                'generator_id' => $generatorId
                            )
                        )),
                        'class' => 'n2-h4 n2-active'
                    ), n2_('Edit generator')));

                    $this->addView("edit", array(
                        "generatorModel"  => $generatorModel,
                        "generatorGroup"  => $generatorGroup,
                        "generatorSource" => $generatorSource,
                        "generator"       => $generator,
                        "slide"           => $slide,
                        'sliderid'        => $slider['id'],
                        'slideid'         => $slide['id']
                    ));
                    $this->render();
                } else {
                    $this->redirect(array(
                        "sliders/index"
                    ), 302, true);
                }
            } else {
                $this->redirect(array(
                    "sliders/index"
                ), 302, true);

            }
        }
    }

    public function actionCreateSettings() {
        if ($this->validatePermission('smartslider_edit')) {
            $slidersModel = new N2SmartsliderSlidersModel();
            $sliderID     = N2Request::getInt('sliderid');
            if (!($slider = $slidersModel->get($sliderID))) {
                $this->redirectToSliders();
            }

            if (N2Request::getInt('save')) {

                $generatorModel = new N2SmartsliderGeneratorModel();
                $result         = $generatorModel->createGenerator($slider['id'], N2Request::getVar('generator'));

                N2Message::success(n2_('Generator created.'));

                $this->redirect(array(
                    "slides/edit",
                    array(
                        "sliderid" => $slider['id'],
                        "slideid"  => $result['slideId']
                    )
                ), 302, true);
            }

            $generatorGroup = N2SSGeneratorFactory::getGenerator(N2Request::getCmd('group'));
            $source         = $generatorGroup->getSource(N2Request::getVar('type'));
            if ($source) {

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $slider['id'])
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "generator/create",
                        array(
                            "sliderid" => $sliderID
                        )
                    )),
                    'class' => 'n2-h4'
                ), n2_('Add dynamic slides')));


                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "generator/createStep2",
                        array(
                            "sliderid" => $sliderID,
                            'group'    => $generatorGroup->getName()
                        )
                    )),
                    'class' => 'n2-h4'
                ), $generatorGroup->getLabel()));


                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => '#',
                    'class' => 'n2-h4 n2-active'
                ), $source->getLabel()));


                $this->addView("create-step3-settings", array(
                    'slider'         => $slider,
                    'generatorGroup' => $generatorGroup,
                    'source'         => $source
                ));
                $this->render();
            } else {
                $this->redirect($this->appType->router->createUrl(array(
                    "slider/edit",
                    array('sliderid' => $slider['id'])
                )));
            }
        }
    }

    public function actionCheckConfiguration() {
        if ($this->validatePermission('smartslider_edit')) {
            $this->actionConfigure(true);
        }
    }

    public function actionConfigure($create = false) {
        if ($this->validatePermission('smartslider_config')) {

            $generatorModel = new N2SmartsliderGeneratorModel();

            $group = N2Request::getVar('group');

            $generatorGroup = $generatorModel->getGeneratorGroup($group);

            $configuration = $generatorGroup->getConfiguration();

            if (N2Request::getInt('save')) {
                if ($this->validateToken()) {
                    $configuration->addData(N2Request::getVar('generator'));
                    $this->refresh();
                } else {
                    $this->refresh();
                }
            }

            if ($create) {
                $slidersModel = new N2SmartsliderSlidersModel();
                $sliderID     = N2Request::getInt('sliderid');
                if (!($slider = $slidersModel->get($sliderID))) {
                    $this->redirectToSliders();
                }

                $generatorGroup = N2SSGeneratorFactory::getGenerator(N2Request::getCmd('group'));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "slider/edit",
                        array('sliderid' => $sliderID)
                    )),
                    'class' => 'n2-h4'
                ), n2_esc_html($slider['title'])));

                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => $this->appType->router->createUrl(array(
                        "generator/create",
                        array(
                            "sliderid" => $sliderID
                        )
                    )),
                    'class' => 'n2-h4'
                ), n2_('Add dynamic slides')));


                $this->layout->addBreadcrumb(N2Html::tag('a', array(
                    'href'  => '#',
                    'class' => 'n2-h4 n2-active'
                ), n2_('Configure') . ' - ' . $generatorGroup->getLabel()));
            } else {
                $this->layoutName = 'default';
                $this->addViewFile($this->appType->path . '/fragments/', "sidebar-settings", array(), "sidebar");
            }

            if ($create == false || !$configuration->wellConfigured()) {

                $this->addView("check_configuration", array(
                    'configuration' => $configuration
                ));
                $this->render();


            } else {

                $this->redirect(array(
                    "generator/createStep2",
                    array(
                        "sliderid" => N2Request::getInt('sliderid'),
                        "group"    => $group
                    )
                ), 302, true);
            }
        }

    }

    public function actionFinishAuth() {
        if ($this->validatePermission('smartslider_config')) {

            $generatorModel = new N2SmartsliderGeneratorModel();

            $group = N2Request::getVar('group');

            $generatorGroup = $generatorModel->getGeneratorGroup($group);

            $configuration = $generatorGroup->getConfiguration();
            $result        = $configuration->finishAuth();
            if ($result === true) {
                N2Message::success(n2_('Authentication successful.'));
                echo '<script>window.opener.location.reload();self.close();</script>';
            } else {
                if ($result instanceof Exception) {
                    $message = $result->getMessage();
                } else {
                    $message = 'Something wrong with the credentials';
                }
                echo '<script>window.opener.N2Classes.Notification.error("' . htmlspecialchars($message) . '");self.close();</script>';
            }
            n2_exit(true);
        }
    }
}