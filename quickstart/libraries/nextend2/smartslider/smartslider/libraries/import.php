<?php
N2Loader::import('libraries.zip.reader');
N2Loader::import('libraries.backup', 'smartslider');

class N2SmartSliderImport {

    /**
     * @var N2SmartSliderBackup
     */
    private $backup;
    private $imageTranslation = array();
    private $sectionTranslation = array();

    private $sliderId = 0;

    private $restore = false;

    public function enableRestore() {
        $this->restore = true;
    }

    public function import($filePathOrData, $groupID = 0, $imageImportMode = 'clone', $linkedVisuals = 1, $isFilePath = true) {
        if (!$isFilePath) {
            $tmp = N2Filesystem::tempnam();
            file_put_contents($tmp, $filePathOrData);
            $filePathOrData = $tmp;
        }
        $importData = N2ZipReader::read($filePathOrData);
        if (!$isFilePath) {
            @unlink($tmp);
        }
        if (!is_array($importData)) {
            N2Message::error(n2_('The importing failed at the unzipping part.'));

            return false;
        } else if (!isset($importData['data'])) {
            if (array_key_exists("slider.ss2", $importData)) {
                N2Message::error(n2_('You can\'t import sliders from Smart Slider 2.'));
            } else if (empty($importData)) {
                N2Message::error(n2_('Export file corrupted! Slider data is missing.'));
            }

            return false;
        }

        $this->backup = unserialize($importData['data']);


        if (!empty($this->backup->slider['type']) && $this->backup->slider['type'] == 'group') {
            // Groups can not be imported into groups
            $groupID = 0;
        }

        $this->sectionTranslation = array();
        $this->importVisuals($this->backup->visuals, $linkedVisuals);


        $sliderModel = new N2SmartsliderSlidersModel();


        if ($this->restore) {
            $this->sliderId = $sliderModel->restore($this->backup->slider, $groupID);
        } else {
            $this->sliderId = $sliderModel->import($this->backup->slider, $groupID);
        }

        if (!$this->sliderId) {
            return false;
        }

        switch ($imageImportMode) {
            case 'clone':
                $images     = isset($importData['images']) ? $importData['images'] : array();
                $imageStore = new N2StoreImage('slider' . $this->sliderId, true);
                foreach ($images AS $file => $content) {
                    $localImage = $imageStore->makeCache($file, $content);
                    if ($localImage) {
                        $this->imageTranslation[$file] = N2ImageHelper::dynamic(N2Uri::pathToUri($localImage));
                    } else {
                        $this->imageTranslation[$file] = $file;
                    }
                    if (!$this->imageTranslation[$file]) {
                        $this->imageTranslation[$file] = array_search($file, $this->backup->imageTranslation);
                    }
                }
                break;
            case 'update':
                $keys   = array_keys($this->backup->NextendImageHelper_Export);
                $values = array_values($this->backup->NextendImageHelper_Export);
                foreach ($this->backup->imageTranslation AS $image => $value) {
                    $this->imageTranslation[$value] = str_replace($keys, $values, $image);
                }
                break;
            default:
                break;
        }
        if (!empty($this->backup->slider['thumbnail'])) {
            $sliderModel->setThumbnail($this->sliderId, $this->fixImage($this->backup->slider['thumbnail']));
        }

        foreach ($this->backup->NextendImageManager_ImageData AS $image => $data) {
            $data['tablet']['image'] = $this->fixImage($data['tablet']['image']);
            $data['mobile']['image'] = $this->fixImage($data['mobile']['image']);
            $fixedImage              = $this->fixImage($image);
            if (!N2ImageManager::hasImageData($fixedImage)) {
                N2ImageManager::addImageData($this->fixImage($image), $data);
            }
        }

        if (empty($this->backup->slider['type'])) {
            $this->backup->slider['type'] = 'simple';
        }


        if ($this->backup->slider['type'] == 'group') {
            /**
             * Import the sliders for the group!
             */
            foreach ($importData['sliders'] AS $k => $slider) {
                $import = new N2SmartSliderImport();
                if ($this->restore) {
                    $import->enableRestore();
                }
                $import->import($slider, $this->sliderId, $imageImportMode, $linkedVisuals, false);
            }
        } else {

            unset($importData);

            $sliderType = N2SSPluginSliderType::getSliderType($this->backup->slider['type']);
            $sliderType->import($this, $this->backup->slider);


            $enabledWidgets = array();
            $widgetGroups   = N2SmartSliderWidgets::getGroups();

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

                $widget->prepareImport($this, $params);
            }


            $sliderModel->importUpdate($this->sliderId, $params);

            $generatorTranslation = array();
            N2Loader::import("models.generator", "smartslider");
            $generatorModel = new N2SmartsliderGeneratorModel();
            foreach ($this->backup->generators as $generator) {
                $generatorTranslation[$generator['id']] = $generatorModel->import($generator);
            }


            $slidesModel = new N2SmartsliderSlidesModel();
            for ($i = 0; $i < count($this->backup->slides); $i++) {
                $slide              = $this->backup->slides[$i];
                $slide['params']    = new N2Data($slide['params'], true);
                $slide['thumbnail'] = $this->fixImage($slide['thumbnail']);
                $slide['params']->set('backgroundImage', $this->fixImage($slide['params']->get('backgroundImage')));
                $slide['params']->set('ligthboxImage', $this->fixImage($slide['params']->get('ligthboxImage')));

                if ($slide['params']->has('link')) {
                    // Compatibility fix for the old SS3 import files
                    $slide['params']->set('link', $this->fixLightbox($slide['params']->get('link')));
                }
                if ($slide['params']->has('href')) {
                    $slide['params']->set('href', $this->fixLightbox($slide['params']->get('href')));
                }

                $layers = json_decode($slide['slide'], true);

                self::prepareImportLayer($this, $layers);

                $slide['slide'] = json_encode($layers);

                if (isset($generatorTranslation[$slide['generator_id']])) {
                    $slide['generator_id'] = $generatorTranslation[$slide['generator_id']];
                }
                $slidesModel->import($slide, $this->sliderId);
            }
        }

        return $this->sliderId;
    }

    /**
     * @param N2SmartSliderImport $import
     * @param array               $layers
     */
    public static function prepareImportLayer($import, &$layers) {
        for ($i = 0; $i < count($layers); $i++) {

            if (isset($layers[$i]['type'])) {
                switch ($layers[$i]['type']) {
                    case 'content':
                        N2SSSlideComponentContent::prepareImport($import, $layers[$i]);
                        break;
                    case 'row':
                        N2SSSlideComponentRow::prepareImport($import, $layers[$i]);
                        break;
                    case 'col':
                        N2SSSlideComponentCol::prepareImport($import, $layers[$i]);
                        break;
                    case 'group':
                        N2SSSlideComponentGroup::prepareImport($import, $layers[$i]);
                        break;
                    default:
                        N2SSSlideComponentLayer::prepareImport($import, $layers[$i]);
                }
            } else {
                N2SSSlideComponentLayer::prepareImport($import, $layers[$i]);
            }
        }
    }

    public function fixImage($image) {
        if (isset($this->backup->imageTranslation[$image]) && isset($this->imageTranslation[$this->backup->imageTranslation[$image]])) {
            return $this->imageTranslation[$this->backup->imageTranslation[$image]];
        }

        return $image;
    }

    public function fixSection($idOrRaw) {
        if (isset($this->sectionTranslation[$idOrRaw])) {
            return $this->sectionTranslation[$idOrRaw];
        }

        return $idOrRaw;
    }

    public function fixLightbox($url) {
        preg_match('/^([a-zA-Z]+)\[(.*)]/', $url, $matches);
        if (!empty($matches) && $matches[1] == 'lightbox') {
            $data = json_decode($matches[2]);
            if ($data) {
                $newImages = array();
                foreach ($data->urls AS $image) {
                    $newImages[] = $this->fixImage($image);
                }
                $data->urls = $newImages;
                $url        = 'lightbox[' . json_encode($data) . ']';
            }
        }

        return $url;
    }

    private function importVisuals($records, $linkedVisuals) {
        if (count($records)) {
            if (!$linkedVisuals) {
                foreach ($records AS $record) {
                    $this->sectionTranslation[$record['id']] = $record['value'];
                }
            } else {
                $sets = array();
                foreach ($records AS $record) {
                    $storage = N2Base::getApplication($record['application'])->storage;
                    if (!isset($sets[$record['application'] . '_' . $record['section']])) {
                        $sets[$record['application'] . '_' . $record['section']] = $storage->add($record['section'] . 'set', '', $this->backup->slider['title']);
                    }
                    $this->sectionTranslation[$record['id']] = $storage->add($record['section'], $sets[$record['application'] . '_' . $record['section']], $record['value']);
                }
            }
        }
    }
}