<?php
N2Loader::import('libraries.zip.creator');
N2Loader::import('libraries.backup', 'smartslider');

class N2SmartSliderExport {

    private $uniqueCounter = 1;

    /**
     * @var N2SmartSliderBackup
     */
    private $backup;
    private $sliderId = 0;

    public $images = array(), $visuals = array();

    private $files, $usedNames = array(), $imageTranslation = array();

    public function __construct($sliderId) {
        $this->sliderId = $sliderId;
    }

    public function create($saveAsFile = false) {
        $this->backup = new N2SmartSliderBackup();
        $slidersModel = new N2SmartsliderSlidersModel();
        if ($this->backup->slider = $slidersModel->get($this->sliderId)) {

            $zip = new N2ZipCreator();

            if (empty($this->backup->slider['type'])) {
                $this->backup->slider['type'] = 'simple';
            }
            self::addImage($this->backup->slider['thumbnail']);

            $this->backup->slider['params'] = new N2Data($this->backup->slider['params'], true);

            if ($this->backup->slider['type'] == 'group') {
                $xref = new N2SmartsliderSlidersXrefModel();

                $sliders = $xref->getSliders($this->backup->slider['id']);
                foreach ($sliders AS $k => $slider) {
                    $export = new N2SmartSliderExport($slider['slider_id']);

                    $fileName = $export->create(true);

                    $zip->addFile(file_get_contents($fileName), 'sliders/' . $k . '.ss3');
                    unlink($fileName);
                }
            } else {
                $slidesModel          = new N2SmartsliderSlidesModel();
                $this->backup->slides = $slidesModel->getAll($this->backup->slider['id']);


                $sliderType = N2SSPluginSliderType::getSliderType($this->backup->slider['type']);
                $sliderType->export($this, $this->backup->slider);

                /** @var N2SSPluginWidgetAbstract[] $enabledWidgets */
                $enabledWidgets = array();

                $widgetGroups = N2SmartSliderWidgets::getGroups();

                $params = $this->backup->slider['params'];
                foreach ($widgetGroups AS $groupName => $group) {
                    $widgetName = $params->get('widget' . $groupName);
                    if ($widgetName && $widgetName != 'disabled') {
                        $widget = $group->getWidget($widgetName);
                        if ($widget) {
                            $enabledWidgets[$groupName] = $widget;
                        }
                    }
                }

                foreach ($enabledWidgets AS $k => $widget) {
                    $params->fillDefault($widget->getDefaults());

                    $widget->prepareExport($this, $params);
                }

                for ($i = 0; $i < count($this->backup->slides); $i++) {
                    $slide = $this->backup->slides[$i];
                    self::addImage($slide['thumbnail']);
                    $slide['params'] = new N2Data($slide['params'], true);

                    self::addImage($slide['params']->get('backgroundImage'));
                    self::addImage($slide['params']->get('ligthboxImage'));

                    if ($slide['params']->has('link')) {
                        // Compatibility fix for the old SS3 import files
                        self::addLightbox($slide['params']->get('link'));
                    }
                    if ($slide['params']->has('href')) {
                        self::addLightbox($slide['params']->get('href'));
                    }

                    $layers = json_decode($slide['slide'], true);

                    self::prepareExportLayer($this, $layers);


                    if (!empty($slide['generator_id'])) {
                        N2Loader::import("models.generator", "smartslider");
                        $generatorModel             = new N2SmartsliderGeneratorModel();
                        $this->backup->generators[] = $generatorModel->get($slide['generator_id']);
                    }
                }

            }

            $this->images  = array_unique($this->images);
            $this->visuals = array_unique($this->visuals);

            foreach ($this->images AS $image) {
                $this->backup->NextendImageManager_ImageData[$image] = N2ImageManager::getImageData($image, true);
                if ($this->backup->NextendImageManager_ImageData[$image]) {
                    self::addImage($this->backup->NextendImageManager_ImageData[$image]['tablet']['image']);
                    self::addImage($this->backup->NextendImageManager_ImageData[$image]['mobile']['image']);
                } else {
                    unset($this->backup->NextendImageManager_ImageData[$image]);
                }
            }

            $this->images = array_unique($this->images);

            $usedNames = array();
            foreach ($this->images AS $image) {
                $file = N2ImageHelper::fixed($image, true);
                if (N2Filesystem::fileexists($file)) {
                    $fileName = strtolower(basename($file));
                    while (in_array($fileName, $usedNames)) {
                        $fileName = $this->uniqueCounter . $fileName;
                        $this->uniqueCounter++;
                    }
                    $usedNames[] = $fileName;

                    $this->backup->imageTranslation[$image] = $fileName;
                    $zip->addFile(file_get_contents($file), 'images/' . $fileName);
                }
            }

            foreach ($this->visuals AS $visual) {
                $this->backup->visuals[] = N2StorageSectionAdmin::getById($visual);
            }

            $zip->addFile(serialize($this->backup), 'data');

            if (!$saveAsFile) {
                n2_ob_end_clean_all();
                header('Content-disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($this->backup->slider['title'] . '.ss3'));
                header('Content-type: application/zip');
                echo $zip->file();
                n2_exit(true);
            } else {
                $file   = $this->sliderId . '-' . preg_replace('/[^a-zA-Z0-9_-]/', '', $this->backup->slider['title']) . '.ss3';
                $folder = N2Platform::getPublicDir();
                $folder .= '/export/';
                if (!N2Filesystem::existsFolder($folder)) {
                    N2Filesystem::createFolder($folder);
                }
                N2Filesystem::createFile($folder . $file, $zip->file());

                return $folder . $file;
            }
        }
    }

    /**
     * @param N2SmartSliderExport $export
     * @param array               $layers
     */
    public static function prepareExportLayer($export, $layers) {
        foreach ($layers AS $layer) {

            if (isset($layer['type'])) {
                switch ($layer['type']) {
                    case 'content':
                        N2SSSlideComponentContent::prepareExport($export, $layer);
                        break;
                    case 'row':
                        N2SSSlideComponentRow::prepareExport($export, $layer);
                        break;
                    case 'col':
                        N2SSSlideComponentCol::prepareExport($export, $layer);
                        break;
                    case 'group':
                        N2SSSlideComponentGroup::prepareExport($export, $layer);
                        break;
                    default:
                        N2SSSlideComponentLayer::prepareExport($export, $layer);
                }
            } else {
                N2SSSlideComponentLayer::prepareExport($export, $layer);
            }
        }
    }

    public function createHTML($isZIP = true) {
        $this->files = array();
        n2_ob_end_clean_all();
        N2AssetsManager::createStack();

        N2AssetsPredefined::frontend(true);
        N2Base::getApplication('smartslider')->info->assetsFrontend(true);

        ob_start();
        N2Base::getApplication("smartslider")
              ->getApplicationType('frontend')
              ->render(array(
                  "controller" => 'home',
                  "action"     => N2Platform::getPlatform(),
                  "useRequest" => false
              ), array(
                  $this->sliderId,
                  'Export as HTML'
              ));

        $slidersModel = new N2SmartsliderSlidersModel();
        $slider       = $slidersModel->get($this->sliderId);
        $sliderHTML   = ob_get_clean();
        $headHTML     = '';

        $css = N2AssetsManager::getCSS(true);
        foreach ($css['url'] AS $url) {
            $headHTML .= N2Html::style($url, true, array(
                    'media' => 'screen, print'
                )) . "\n";
        }
        array_unshift($css['files'], N2LIBRARYASSETS . '/normalize.min.css');
    

        foreach ($css['files'] AS $file) {
            $headHTML .= $this->addCSSFile($file);
        }

        if ($css['inline'] != '') {
            $headHTML .= N2Html::style($css['inline']) . "\n";
        }

        $js = N2AssetsManager::getJs(true);

        if ($js['globalInline'] != '') {
            $headHTML .= N2Html::script($js['globalInline']) . "\n";
        }

        foreach ($js['url'] AS $url) {
            $headHTML .= N2Html::script($url, true) . "\n";
        }
        foreach ($js['files'] AS $file) {
            $path               = 'js/' . basename($file);
            $this->files[$path] = file_get_contents($file);
            $headHTML           .= N2Html::script($path, true) . "\n";
        }

        if ($js['inline'] != '') {
            $headHTML .= N2Html::script($js['inline']) . "\n";
        }

        $sliderHTML = preg_replace_callback('/(src|data-thumbnail|data-desktop|data-tablet|data-mobile)=["|\'](.*?)["|\']/i', array(
            $this,
            'replaceHTMLImage'
        ), $sliderHTML);

        $sliderHTML = preg_replace_callback('/url\(\s*([\'"]|(&#039;))?(\S*\.(?:jpe?g|gif|png))([\'"]|(&#039;))?\s*\)[^;}]*?/i', array(
            $this,
            'replaceHTMLBGImage'
        ), $sliderHTML);

        $sliderHTML = preg_replace_callback('/(data-href)=["|\'](.*?)["|\']/i', array(
            $this,
            'replaceHTMLImage'
        ), $sliderHTML);

        $sliderHTML = preg_replace_callback('/(n2-lightbox-urls)=["|\'](.*?)["|\']/i', array(
            $this,
            'replaceLightboxImages'
        ), $sliderHTML);

        $sliderHTML = preg_replace_callback('/n2\-lightbox=[^<>]*?(href)=["|\'](.*?)["|\']/i', array(
            $this,
            'replaceHTMLImageHrefLightbox'
        ), $sliderHTML);

        $headHTML = preg_replace_callback('/"([^"]*?\.(jpg|png|gif|jpeg|webp|svg))"/i', array(
            $this,
            'replaceJSON'
        ), $headHTML);

        $this->files['index.html'] = "<!doctype html>\n<html lang=\"en\">\n<head>\n<meta charset=\"utf-8\">\n<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge, chrome=1\">\n<title>" . $slider['title'] . "</title>\n" . $headHTML . "</head>\n<body>\n" . $sliderHTML . "</body>\n</html>";

        if (!$isZIP) {
            return $this->files;
        }

        $zip = new N2ZipCreator();
        foreach ($this->files AS $path => $content) {
            $zip->addFile($content, $path);
        }
        n2_ob_end_clean_all();
        header('Content-disposition: attachment; filename*=UTF-8\'\'' . rawurlencode($slider['title'] . '.zip'));
        header('Content-type: application/zip');
        echo $zip->file();
        n2_exit(true);
    }

    private static function addProtocol($image) {
        if (substr($image, 0, 2) == '//') {
            return N2Uri::$scheme . ':' . $image;
        }

        return $image;
    }

    public function replaceHTMLImage($found) {
        $path = N2Filesystem::absoluteURLToPath(self::addProtocol($found[2]));
        if (strpos($path, N2Filesystem::getBasePath()) !== 0) {
            $imageUrl = N2Uri::relativetoabsolute($path);
            $path     = N2Filesystem::absoluteURLToPath($imageUrl);
        }

        if ($path == $found[2]) {
            return $found[0];
        }
        if (N2Filesystem::fileexists($path)) {
            if (!isset($this->imageTranslation[$path])) {
                $fileName = strtolower(basename($path));
                while (in_array($fileName, $this->usedNames)) {
                    $fileName = $this->uniqueCounter . $fileName;
                    $this->uniqueCounter++;
                }
                $this->usedNames[]                  = $fileName;
                $this->files['images/' . $fileName] = file_get_contents($path);
                $this->imageTranslation[$path]      = $fileName;
            } else {
                $fileName = $this->imageTranslation[$path];
            }

            return str_replace($found[2], 'images/' . $fileName, $found[0]);
        } else {
            return $found[0];
        }
    }

    public function replaceHTMLImageHrefLightbox($found) {
        return $this->replaceHTMLImage($found);
    }

    public function replaceLightboxImages($found) {
        $images = explode(',', $found[2]);
        foreach ($images AS $k => $image) {
            $images[$k] = $this->replaceHTMLImage(array(
                $image,
                '',
                $image
            ));
        }

        return 'n2-lightbox-urls="' . implode(',', $images) . '"';
    }

    public function replaceHTMLBGImage($found) {
        $path = $this->replaceHTMLImage(array(
            $found[3],
            '',
            $found[3]
        ));

        return str_replace($found[3], $path, $found[0]);
    }

    public function replaceJSON($found) {
        $image = str_replace('\\/', '/', $found[1]);
        $path  = $this->replaceHTMLImage(array(
            $image,
            '',
            $image
        ));

        return str_replace($found[1], str_replace('/', '\\/', $path), $found[0]);
    }

    public function addImage($image) {
        if (!empty($image)) {
            $this->images[] = $image;
        }
    }

    public function addLightbox($url) {
        preg_match('/^([a-zA-Z]+)\[(.*)]/', $url, $matches);
        if (!empty($matches)) {
            if ($matches[1] == 'lightbox') {
                $data = json_decode($matches[2]);
                if ($data) {
                    foreach ($data->urls AS $image) {
                        $this->addImage($image);
                    }
                }
            }
        }
    }

    public function addVisual($id) {
        if (is_numeric($id) && $id > 10000) {
            $this->visuals[] = $id;
        }
    }

    private function addCSSFile($file) {
        $path = 'css/' . basename($file);

        $this->basePath = dirname($file);
        $this->baseUrl  = N2Filesystem::pathToAbsoluteURL($this->basePath);

        $fileContent = file_get_contents($file);

        $fileContent = preg_replace_callback('#url\([\'"]?([^"\'\)]+)[\'"]?\)#', array(
            $this,
            'replaceCSSImage'
        ), $fileContent);

        $this->files[$path] = $fileContent;

        return N2Html::style($path, true, array(
                'media' => 'screen, print'
            )) . "\n";
    }

    public function replaceCSSImage($matches) {
        if (substr($matches[1], 0, 5) == 'data:') return $matches[0];
        if (substr($matches[1], 0, 4) == 'http') return $matches[0];
        if (substr($matches[1], 0, 2) == '//') return $matches[0];

        $exploded = explode('?', $matches[1]);

        $path = realpath($this->basePath . '/' . $exploded[0]);
        if ($path === false) {
            return 'url(' . str_replace(array(
                    'http://',
                    'https://'
                ), '//', $this->baseUrl) . '/' . $matches[1] . ')';
        }

        $path = N2Filesystem::fixPathSeparator($path);

        if (!isset($this->imageTranslation[$path])) {
            $fileName = strtolower(basename($path));
            while (in_array($fileName, $this->usedNames)) {
                $fileName = $this->uniqueCounter . $fileName;
                $this->uniqueCounter++;
            }
            $this->usedNames[]                      = $fileName;
            $this->files['css/assets/' . $fileName] = file_get_contents($path);
            $this->imageTranslation[$path]          = $fileName;
        } else {
            $fileName = $this->imageTranslation[$path];
        }

        return str_replace($matches[1], 'assets/' . $fileName, $matches[0]);
    }
}