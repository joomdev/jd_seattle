<?php

class N2SmartsliderBackendSlidersController extends N2SmartSliderController {

    public $layoutName = 'default1c';

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides',
            'models.generator'
        ), 'smartslider');
    }

    public function actionIndex() {
        N2Localization::addJS(array(
            'License key',
            '%sGet your license key here%s or %sbuy a new%s!',
            'Add license',
            'Authorize'
        ));

        $this->loadSliderManager();

        $this->addView(null);
        $this->render();
    }

    public function actionEmbed() {
        $this->layoutName = 'embed';
        $this->addView('embed', array(
            'mode' => 'embed'
        ));
        $this->render();
    }

    public function actionChoose() {
        $this->layoutName = 'embed';
        $this->addView('embed', array(
            'mode' => 'choose'
        ));
        $this->render();
    }

    public function actionOrderBy() {
        $ordering = N2Request::getCmd('ordering', null);
        if ($ordering == 'DESC' || $ordering == 'ASC') {
            N2SmartSliderSettings::set('slidersOrder2', 'ordering');
            N2SmartSliderSettings::set('slidersOrder2Direction', 'ASC');
        }

        $time = N2Request::getCmd('time', null);
        if ($time == 'DESC' || $time == 'ASC') {
            N2SmartSliderSettings::set('slidersOrder2', 'time');
            N2SmartSliderSettings::set('slidersOrder2Direction', $time);
        }
        $title = N2Request::getCmd('title', null);
        if ($title == 'DESC' || $title == 'ASC') {
            N2SmartSliderSettings::set('slidersOrder2', 'title');
            N2SmartSliderSettings::set('slidersOrder2Direction', $title);
        }
        $this->redirectToSliders();
    }

    public function actionExportAll() {
        N2Loader::import('libraries.export', 'smartslider');
        $slidersModel = new N2SmartsliderSlidersModel();
        $sliders      = $slidersModel->getAll(N2Request::getInt('currentGroupID', 0));

        $ids = N2Request::getVar('sliders');

        $files = array();
        foreach ($sliders AS $slider) {
            if (!empty($ids) && !in_array($slider['id'], $ids)) {
                continue;
            }
            $export = new N2SmartSliderExport($slider['id']);
            $files[] = $export->create(true);
        }

        $zip = new N2ZipCreator();
        foreach ($files AS $file) {
            $zip->addFile(file_get_contents($file), basename($file));
            unlink($file);
        }
        n2_ob_end_clean_all();
        header('Content-disposition: attachment; filename=sliders_unzip_to_import.zip');
        header('Content-type: application/zip');
        echo $zip->file();
        n2_exit(true);
    
    }

    public function actionImport() {
        if ($this->validatePermission('smartslider_edit')) {

            if (empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
                N2Message::error(sprintf(n2_('Your server has an upload file limit at %s, so if you have bigger export file, please use the local import file method.'), @ini_get('post_max_size')));

                $this->redirect(array(
                    "sliders/import"
                ));
            } else if (N2Request::getInt('save')) {
                $data = new N2Data(N2Request::getVar('slider'));

                if ($this->validateToken()) {

                    $restore = $data->get('restore', 0);

                    $file = '';

                    if (isset($_FILES['slider']) && isset($_FILES['slider']['tmp_name']['import-file'])) {

                        switch ($_FILES['slider']['error']['import-file']) {
                            case UPLOAD_ERR_OK:
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                break;
                            case UPLOAD_ERR_INI_SIZE:
                            case UPLOAD_ERR_FORM_SIZE:
                                throw new RuntimeException('Exceeded filesize limit.');
                            default:
                                throw new RuntimeException('Unknown errors.');
                        }

                        $file = $_FILES['slider']['tmp_name']['import-file'];
                    }

                    if (empty($file)) {
                        $_file = $data->get('local-import-file');
                        if (!empty($_file)) {
                            $file = N2Platform::getPublicDir() . '/' . $_file;
                        }
                    }

                    if (N2Filesystem::fileexists($file)) {

                        N2Loader::import('libraries.import', 'smartslider');
                        $import = new N2SmartSliderImport();
                        if ($restore) {
                            $import->enableRestore();
                        }
                        $sliderId = $import->import($file, 0, $data->get('image-mode', 'clone'), $data->get('linked-visuals', 0));

                        if ($sliderId !== false) {
                            N2Message::success(n2_('Slider imported.'));

                            if ($data->get('delete')) {
                                @unlink($file);
                            }

                            $this->redirect(array(
                                "slider/edit",
                                array("sliderid" => $sliderId)
                            ));
                        } else {
							$extension = pathinfo($_FILES['slider']['name']['import-file'], PATHINFO_EXTENSION);
							if(strpos($_FILES['slider']['name']['import-file'], 'sliders_unzip_to_import') !== false){
								N2Message::error(n2_('You have to unzip your ' . $_FILES['slider']['name']['import-file'] . ' file to find the importable files!'));
							}
							if ($extension != 'ss3') {
								N2Message::error(n2_('Only .ss3 files can be uploaded!'));
							}
							N2Message::error(n2_('Import error!'));
							$this->refresh();
                        }
                    } else {
                        N2Message::error(n2_('The imported file is not readable!'));
                        $this->refresh();
                    }
                } else {

                }
            }


            $this->layout->addBreadcrumb(N2Html::tag('a', array(
                'href'  => $this->appType->router->createUrl(array(
                    "sliders/import"
                )),
                'class' => 'n2-h4 n2-active'
            ), n2_('Import slider')));

            $this->addView('import');
            $this->render();
        }
    
    }

    public function actionHidePromoUpgrade() {
        if ($this->validateToken()) {
            $this->appType->app->storage->set('free', 'promoUpgrade', 1);
        }
    
        $this->redirectToSliders();
    }

    public function actionHideReview() {

        if ($this->validateToken()) {
            $this->appType->app->storage->set('free', 'review', 1);
        }

        $this->redirectToSliders();
    }
}