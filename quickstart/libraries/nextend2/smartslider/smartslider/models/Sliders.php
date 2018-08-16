<?php

N2Loader::import("libraries.slider.abstract", "smartslider");
N2Loader::import("models.SlidersXref", "smartslider");
N2Loader::import("models.Slides", "smartslider");

class N2SmartsliderSlidersModel extends N2Model {

    /**
     * @var N2SmartsliderSlidersXrefModel
     */
    private $xref;

    public function __construct() {
        parent::__construct("nextend2_smartslider3_sliders");

        $this->xref = new N2SmartsliderSlidersXrefModel();
    }

    public function get($id) {
        return $this->db->queryRow("SELECT * FROM " . $this->getTable() . " WHERE id = :id", array(
            ":id" => $id
        ));
    }

    public function getByAlias($alias) {
        return $this->db->queryRow("SELECT id FROM " . $this->getTable() . " WHERE alias = :alias", array(
            ":alias" => $alias
        ));
    }

    public function getWithThumbnail($id) {
        $slidesModel = new N2SmartsliderSlidesModel();

        return $this->db->queryRow("SELECT sliders.*, IF(sliders.thumbnail != '',sliders.thumbnail,(SELECT slides.thumbnail from " . $slidesModel->getTable() . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1)) AS thumbnail,
         IF(sliders.type != 'group', 
                        (SELECT count(*) FROM " . $slidesModel->getTable() . " AS slides2 WHERE slides2.slider = sliders.id GROUP BY slides2.slider),
                        (SELECT count(*) FROM " . $this->xref->getTable() . " AS xref2 WHERE xref2.group_id = sliders.id GROUP BY xref2.group_id)
                  ) AS slides
        FROM " . $this->getTable() . " AS sliders
        WHERE sliders.id = :id", array(
            ":id" => $id
        ));
    }

    public function invalidateCache() {

        return $this->db->query("UPDATE `" . $this->db->parsePrefix('#__nextend2_section_storage') . "` SET `value` = 1 WHERE `application` LIKE 'smartslider' AND `section` LIKE 'sliderChanged';");
    }

    public function refreshCache($sliderid) {
        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($sliderid));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($sliderid));
        self::markChanged($sliderid);
    }


    /**
     * @return mixed
     */
    public function getAll($groupID, $orderBy = 'ordering', $orderByDirection = 'ASC') {
        $slidesModel = new N2SmartsliderSlidesModel();

        $_orderby = $orderBy . ' ' . $orderByDirection;
        if ($groupID != 0 && $orderBy == 'ordering') {
            $_orderby = 'xref.' . $orderBy . ' ' . $orderByDirection;
        }

        $sliders = $this->db->queryAll("
            SELECT sliders.*, 
                  IF(sliders.thumbnail != '',
                      sliders.thumbnail,
                          IF(sliders.type != 'group',
                              (SELECT slides.thumbnail FROM " . $slidesModel->getTable() . " AS slides WHERE slides.slider = sliders.id AND slides.published = 1 AND slides.generator_id = 0 AND slides.thumbnail NOT LIKE '' ORDER BY  slides.first DESC, slides.ordering ASC LIMIT 1),
                              ''
                          )
                  ) AS thumbnail,
                  
                  IF(sliders.type != 'group', 
                        (SELECT count(*) FROM " . $slidesModel->getTable() . " AS slides2 WHERE slides2.slider = sliders.id GROUP BY slides2.slider),
                        (SELECT count(*) FROM " . $this->xref->getTable() . " AS xref2 WHERE xref2.group_id = sliders.id GROUP BY xref2.group_id)
                  ) AS slides
            FROM " . $this->getTable() . " AS sliders
            LEFT JOIN " . $this->xref->getTable() . " AS xref ON xref.slider_id = sliders.id
            WHERE " . ($groupID == 0 ? "xref.group_id IS NULL OR xref.group_id = 0" : "xref.group_id = '" . $groupID . "'") . "
            ORDER BY " . $_orderby);

        return $sliders;
    }

    public function _getAll() {
        return $this->db->queryAll("SELECT sliders.* FROM " . $this->getTable() . " AS sliders");
    }

    public function getGroups() {
        return $this->db->queryAll("SELECT id, title FROM " . $this->getTable() . " WHERE type LIKE 'group' ORDER BY title ASC");
    }

    public static function renderAddForm($data = array()) {
        return self::editForm($data);
    }

    public static function renderEditForm($slider) {

        $data = json_decode($slider['params'], true);
        if ($data == null) $data = array();
        $data['title']     = $slider['title'];
        $data['type']      = $slider['type'];
        $data['thumbnail'] = $slider['thumbnail'];
        $data['alias']     = isset($slider['alias']) ? $slider['alias'] : '';

        return self::editForm($data);
    }

    private static function editForm($data = array()) {

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->set('class', 'nextend-smart-slider-admin');

        $form->loadArray($data);

        $sliderSettings = new N2TabTabbed($form, 'slider-settings', false, array(
            'active'     => 1,
            'underlined' => true
        ));

        $publishTab = new N2TabGroupped($sliderSettings, 'publish', n2_('Publish'));

        $publishTab2 = new N2Tab($publishTab, 'publish', false);

        new N2ElementPublishSlider($publishTab2);


        $generalTab  = new N2TabGroupped($sliderSettings, 'general', n2_('General'));
        $generalTab2 = new N2Tab($generalTab, 'slider', false);
        new N2ElementText($generalTab2, 'title', n2_('Name'), n2_('Slider'), array(
            'style' => 'width:400px;'
        ));

        $aliasGroup = new N2ElementGroup($generalTab2, 'aliasgroup', n2_('Alias'));

        new N2ElementText($aliasGroup, 'alias', n2_('Alias'), '', array(
            'style' => 'width:200px;'
        ));

        new N2ElementOnOff($aliasGroup, 'alias-id', n2_('Use as ID on element before slider'), '', array(
            'tip'           => 'You can have an empty div element before our slider, which would use this alias as its id. This can be useful, if you would want to use #your-alias as the url in your menu to jump to that element.',
            'relatedFields' => array(
                'alias-smoothscroll'
            )
        ));

        new N2ElementOnOff($aliasGroup, 'alias-smoothscroll', n2_('Smooth scroll to this element'), '', array(
            'tip' => 'The #your-alias urls in links would be forced to smooth scroll to our element.'
        ));

        $controls = new N2ElementGroup($generalTab2, 'controls', n2_('Controls'));
        new N2ElementOnOff($controls, 'controlsScroll', n2_('Mouse scroll'), 0);
        new N2ElementOnOff($controls, 'controlsDrag', n2_('Mouse drag'), 1);
        new N2ElementRadio($controls, 'controlsTouch', n2_('Touch'), 'horizontal', array(
            'options' => array(
                '0'          => n2_('Disabled'),
                'horizontal' => n2_('Horizontal'),
                'vertical'   => n2_('Vertical')
            )
        ));
        new N2ElementOnOff($controls, 'controlsKeyboard', n2_('Keyboard'), 1);


        new N2ElementImage($generalTab2, 'thumbnail', n2_('Thumbnail'), '');
        new N2ElementRadio($generalTab2, 'align', n2_('Align'), 'normal', array(
            'options' => array(
                'normal' => n2_('Normal'),
                'left'   => n2_('Left'),
                'center' => n2_('Center'),
                'right'  => n2_('Right')
            )
        ));

        $backgroundModeOptions = array(
            'fill'    => array(
                'image' => '$ss$/admin/images/fillmode/fill.png',
                'label' => n2_('Fill')
            ),
            'blurfit' => array(
                'image' => '$ss$/admin/images/fillmode/fit.png',
                'label' => n2_('Blur fit')
            ),
            'fit'     => array(
                'image' => '$ss$/admin/images/fillmode/fit.png',
                'label' => n2_('Fit')
            ),
            'stretch' => array(
                'image' => '$ss$/admin/images/fillmode/stretch.png',
                'label' => n2_('Stretch')
            ),
            'center'  => array(
                'image' => '$ss$/admin/images/fillmode/center.png',
                'label' => n2_('Center')
            ),
            'tile'    => array(
                'image' => '$ss$/admin/images/fillmode/tile.png',
                'label' => n2_('Tile')
            )
        );
        new N2ElementImageListLabel($generalTab2, 'backgroundMode', n2_('Slide background image fill'), 'fill', array(
            'tip'     => n2_('If the size of your image is not the same as your slide\'s, you can improve the result with the filling modes.'),
            'options' => $backgroundModeOptions
        ));

        $sliderTypeTab = new N2Tab($generalTab, 'slidertype', n2_('Slider Type'), array(
            'class' => 'n2-expert'
        ));

        new N2ElementSliderType($sliderTypeTab, 'type', false, 'simple', N2Base::getApplication('smartslider')
                                                                               ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderslidertype")));

        new N2TabPlaceholder($generalTab, 'slidertypeplaceholder', 'Slider Type placeholder', array(
            'id' => 'nextend-type-panel'
        ));


        $sizeTab  = new N2TabGroupped($sliderSettings, 'size', n2_('Size'));
        $sizeTab2 = new N2Tab($sizeTab, 'slider-responsive', false);

        $size = new N2ElementGroup($sizeTab2, 'slider-size', n2_('Slider size'));
        new N2ElementNumberAutocomplete($size, 'width', n2_('Width'), 900, array(
            'style'  => 'width:35px',
            'values' => array(
                1920,
                1400,
                1000,
                800,
                600,
                400
            ),
            'unit'   => 'px'
        ));
        new N2ElementNumberAutocomplete($size, 'height', n2_('Height'), 500, array(
            'style'  => 'width:35px',
            'values' => array(
                800,
                600,
                500,
                400,
                300,
                200
            ),
            'unit'   => 'px'
        ));

        $margin = new N2ElementMixed($sizeTab2, 'margin', n2_('Margin'), '0|*|0|*|0|*|0');
        new N2ElementNumber($margin, 'margin-top', n2_('Top'), '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($margin, 'margin-right', n2_('Right'), '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($margin, 'margin-bottom', n2_('Bottom'), '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));
        new N2ElementNumber($margin, 'margin-left', n2_('Left'), '', array(
            'style' => 'width:22px;',
            'unit'  => 'px'
        ));


        $responsiveMode = new N2Tab($sizeTab, 'slider-responsive-types', n2_('Responsive mode'));
        new N2ElementSliderResponsive($responsiveMode, 'responsive-mode', false, 'auto', N2Base::getApplication('smartslider')
                                                                                               ->getApplicationType('backend')->router->createAjaxUrl(array("slider/renderresponsivetype")));

        new N2TabPlaceholder($sizeTab, 'slider-responsive-placeholder', 'Slider Type placeholder', array(
            'id' => 'nextend-responsive-mode-panel'
        ));


        $autoplayTab  = new N2TabGroupped($sliderSettings, 'autoplay', n2_('Autoplay'));
        $autoplayTab2 = new N2Tab($autoplayTab, 'autoplay', false);

        $autoplayGroup = new N2ElementGroup($autoplayTab2, 'autoplay', n2_('Autoplay'));
        new N2ElementOnOff($autoplayGroup, 'autoplay', n2_('Enable'), 0, array(
            'relatedAttribute' => 'autoplay',
            'relatedFields'    => array(
                'autoplayDuration',
                'autoplayStart',
                'autoplayfinish',
                'autoplayAllowReStart',
                'autoplay-stop-on',
                'autoplay-resume-on'
            )
        ));
        new N2ElementNumber($autoplayGroup, 'autoplayDuration', n2_('Interval'), 8000, array(
            'style' => 'width:35px;',
            'unit'  => 'ms'
        ));

        $stopAutoplayOn = new N2ElementGroup($autoplayTab2, 'autoplay-stop-on', n2_('Stop autoplay on'));
        new N2ElementOnOff($stopAutoplayOn, 'autoplayStopClick', n2_('Click'), 1);
        new N2ElementList($stopAutoplayOn, 'autoplayStopMouse', n2_('Mouse'), 0, array(
            'options' => array(
                '0'     => n2_('Off'),
                'enter' => n2_('Enter'),
                'leave' => n2_('Leave')
            )
        ));
        new N2ElementOnOff($stopAutoplayOn, 'autoplayStopMedia', n2_('Media started'), 1);

        $optimize  = new N2TabGroupped($sliderSettings, 'optimize', n2_('Optimize'));
        $optimize2 = new N2Tab($optimize, 'optimize-images', false);

        $optimizeImages = new N2ElementGroup($optimize2, 'optimize-images', n2_('Optimize images'));
        new N2ElementOnOff($optimizeImages, 'optimize', n2_('Enable'), 0, array(
            'relatedFields' => array(
                'optimize-quality'
            )
        ));
        new N2ElementNumber($optimizeImages, 'optimize-quality', n2_('Quality'), 70, array(
            'min'   => 0,
            'max'   => 100,
            'unit'  => '%',
            'style' => 'width:40px;'
        ));

        $backgroundImage = new N2ElementGroup($optimize2, 'background-image-resize', n2_('Background image resize'), array('tip' => n2_('Only works if the \'Optimize images\' option is turned on too!')));
        new N2ElementOnOff($backgroundImage, 'optimize-background-image-custom', n2_('Enable'), '0', array(
            'relatedFields' => array(
                'optimize-background-image-width',
                'optimize-background-image-height'
            )
        ));
        new N2ElementNumber($backgroundImage, 'optimize-background-image-width', n2_('Width'), 800, array(
            'min'   => 0,
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));
        new N2ElementNumber($backgroundImage, 'optimize-background-image-height', n2_('Height'), 600, array(
            'min'   => 0,
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));

        $thumbnailImage = new N2ElementGroup($optimize2, 'thumbnail-image-size', n2_('Thumbnail image resize'));
        new N2ElementNumber($thumbnailImage, 'optimizeThumbnailWidth', n2_('Width'), 100, array(
            'min'   => 0,
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));
        new N2ElementNumber($thumbnailImage, 'optimizeThumbnailHeight', n2_('Height'), 60, array(
            'min'   => 0,
            'unit'  => 'px',
            'style' => 'width:40px;'
        ));


        $loading     = new N2TabGroupped($sliderSettings, 'loading', n2_('Loading'));
        $loadingCore = new N2Tab($loading, 'loading-core', false);

        $playWhenVisible = new N2ElementGroup($loadingCore, 'play-when-visible', n2_('Play when visible'));
        new N2ElementOnOff($playWhenVisible, 'playWhenVisible', n2_('Enable'), 1, array(
            'relatedFields' => array(
                'playWhenVisibleAt'
            )
        ));
        new N2ElementNumber($playWhenVisible, 'playWhenVisibleAt', n2_('At'), 50, array(
            'unit'  => '%',
            'style' => 'width:30px;'
        ));

        new N2ElementNumber($loadingCore, 'dependency', n2_('Load this slider after'), '', array(
            'style'    => 'width:40px;',
            'sublabel' => n2_('Slider ID'),
            'tip'      => n2_('The current slider will not start loading until the set slider is loaded completely.')
        ));

        new N2ElementNumber($loadingCore, 'delay', n2_('Delay'), 0, array(
            'style' => 'width:30px;',
            'unit'  => 'ms'
        ));
        new N2ElementOnOff($loadingCore, 'is-delayed', n2_('Delayed (for lightbox/tabs)'), 0);


        $developer        = new N2TabGroupped($sliderSettings, 'developer', n2_('Developer'));
        $developerOptions = new N2Tab($developer, 'developer-options', false);

        new N2ElementOnOff($developerOptions, 'overflow-hidden-page', n2_('Hide website\'s scrollbar'), 0, array(
            'tip' => n2_('You won\'t be able to scroll your website anymore.')
        ));

        $clearGroup = new N2ElementGroup($developerOptions, 'cleargroup', n2_('Clear both'));
        new N2ElementOnOff($clearGroup, 'clear-both', n2_('Before slider'), 0, array(
            'tip' => n2_('If your slider does not resize correctly, turn this option on.')
        ));
        new N2ElementOnOff($clearGroup, 'clear-both-after', n2_('After slider'), 1, array(
            'tip' => n2_('Turn this off to allow contents following the slider get into the same row where the slider is.')
        ));

        new N2ElementTextarea($developerOptions, 'custom-css-codes', 'CSS', '', array(
            'fieldStyle' => 'width:600px;height:300px;'
        ));
        new N2ElementTextarea($developerOptions, 'callbacks', 'JavaScript callbacks', '', array(
            'fieldStyle' => 'width:600px;height:300px;'
        ));


        $widgets = new N2TabRaw($form, 'widgets', false);
        new N2ElementWidgetGroupMatrix($widgets, 'widgets', '', 'arrow');

        echo $form->render('slider');

        N2Loader::import('libraries.form.elements.url');
        N2JS::addFirstCode('nextend.NextendElementUrlParams=' . N2ElementUrl::getNextendElementUrlParameters() . ';');

        return $data;
    }

    public static function renderImportByUploadForm() {


        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));

        $settings = new N2Tab($form, 'import-slider');
        new N2ElementToken($settings);

        new N2ElementUpload($settings, 'import-file', n2_('Import file upload'));

        $localImport = new N2ElementGroup($settings, 'local-import', n2_('Local import'));
        new N2ElementTmpList($localImport, 'local-import-file', n2_('File'), '', 'ss3');
        new N2ElementOnOff($localImport, 'delete', n2_('Delete file after import'), 0);

        new N2ElementOnOff($settings, 'restore', n2_('Restore slider'), 0, array(
            'tip' => n2_('Delete the slider with the same ID')
        ));


        echo $form->render('slider');
    }

    function import($slider, $groupID = 0) {
        try {
            $this->db->insert(array(
                'title'     => $slider['title'],
                'type'      => $slider['type'],
                'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                'params'    => $slider['params']->toJSON(),
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
            ));

            $sliderID = $this->db->insertId();

            if (isset($slider['alias'])) {
                $this->updateAlias($sliderID, $slider['alias']);
            }

            $this->xref->add($groupID, $sliderID);

            return $sliderID;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function restore($slider, $groupID) {

        if (isset($slider['id']) && $slider['id'] > 0) {

            $groups = $this->xref->getGroups($slider['id']);

            $this->delete($slider['id']);

            try {
                $this->db->insert(array(
                    'id'        => $slider['id'],
                    'title'     => $slider['title'],
                    'type'      => $slider['type'],
                    'thumbnail' => empty($slider['thumbnail']) ? '' : $slider['thumbnail'],
                    'params'    => $slider['params']->toJSON(),
                    'time'      => date('Y-m-d H:i:s', N2Platform::getTime())
                ));

                $sliderID = $this->db->insertId();

                if (isset($slider['alias'])) {
                    $this->updateAlias($sliderID, $slider['alias']);
                }

                if ($groupID) {
                    $this->xref->add($groupID, $sliderID);
                }

                if (!empty($groups)) {
                    foreach ($groups AS $group) {
                        $this->xref->add($group['group_id'], $sliderID);
                    }
                }

                return $sliderID;
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $this->import($slider);
    }

    /**
     * @param $sliderId
     * @param $params N2Data
     */
    function importUpdate($sliderId, $params) {

        $this->db->update(array(
            'params' => $params->toJson()
        ), array(
            "id" => $sliderId
        ));
    }

    function create($slider, $groupID = 0) {
        if (!isset($slider['title'])) return false;
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        try {
            $this->db->insert(array(
                'title'     => $title,
                'type'      => $type,
                'params'    => json_encode($slider),
                'thumbnail' => $thumbnail,
                'time'      => date('Y-m-d H:i:s', N2Platform::getTime()),
                'ordering'  => $this->getMaximalOrderValue()
            ));

            $sliderID = $this->db->insertId();

            $this->xref->add($groupID, $sliderID);

            return $sliderID;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    function save($id, $slider) {
        if (!isset($slider['title']) || $id <= 0) return false;
        $response = array(
            'changedFields' => array()
        );
        if ($slider['title'] == '') $slider['title'] = n2_('New slider');

        $title = $slider['title'];
        unset($slider['title']);
        $alias = $slider['alias'];
        unset($slider['alias']);
        $type = $slider['type'];
        unset($slider['type']);

        $thumbnail = '';
        if (!empty($slider['thumbnail'])) {
            $thumbnail = $slider['thumbnail'];
            unset($slider['thumbnail']);
        }

        $this->db->update(array(
            'title'     => $title,
            'type'      => $type,
            'params'    => json_encode($slider),
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        $aliasResult = $this->updateAlias($id, $alias);
        if ($aliasResult !== false) {
            if ($aliasResult['oldAlias'] !== $aliasResult['newAlias']) {
                if ($aliasResult['newAlias'] === null) {
                    N2Message::notice(n2_('Alias removed'));
                    $response['changedFields']['slideralias'] = '';
                } else if ($aliasResult['newAlias'] === '') {
                    N2Message::error(n2_('Alias must contain one or more letters'));
                    $response['changedFields']['slideralias'] = '';
                } else {
                    N2Message::notice(sprintf(n2_('Alias updated to: %s'), $aliasResult['newAlias']));
                    $response['changedFields']['slideralias'] = $aliasResult['newAlias'];
                }
            }
        }

        self::markChanged($id);

        return $response;
    }

    function updateAlias($sliderID, $alias) {
        $isNull = false;
        if (empty($alias)) {
            $isNull = true;
        } else {

            $alias = strtolower($alias);
            $alias = preg_replace('/&.+?;/', '', $alias); // kill entities
            $alias = str_replace('.', '-', $alias);

            $alias = preg_replace('/[^%a-z0-9 _-]/', '', $alias);
            $alias = preg_replace('/\s+/', '-', $alias);
            $alias = preg_replace('|-+|', '-', $alias);
            $alias = preg_replace('|^-*|', '', $alias);

            if (empty($alias)) {
                $isNull = true;
            }
        }

        $slider = $this->get($sliderID);
        if ($isNull) {
            if ($slider['alias'] == 'null') {
            } else {
                $this->db->query('UPDATE ' . $this->db->tableName . ' SET `alias` = NULL WHERE id = ' . intval($sliderID));

                return array(
                    'oldAlias' => $slider['alias'],
                    'newAlias' => null
                );
            }
        } else {
            if (!is_numeric($alias)) {
                if ($slider['alias'] == $alias) {
                    return array(
                        'oldAlias' => $slider['alias'],
                        'newAlias' => $alias
                    );
                } else {
                    $_alias = $alias;
                    for ($i = 2; $i < 12; $i++) {
                        $sliderWithAlias = $this->getByAlias($_alias);
                        if (!$sliderWithAlias) {
                            $this->db->update(array(
                                'alias' => $_alias
                            ), array(
                                "id" => $sliderID
                            ));

                            return array(
                                'oldAlias' => $slider['alias'],
                                'newAlias' => $_alias
                            );
                            break;
                        } else {
                            $_alias = $alias . $i;
                        }
                    }
                }
            }

            return array(
                'oldAlias' => $slider['alias'],
                'newAlias' => ''
            );
        }

        return false;
    }

    function setThumbnail($id, $thumbnail) {

        $this->db->update(array(
            'thumbnail' => $thumbnail
        ), array(
            "id" => $id
        ));

        self::markChanged($id);

        return $id;
    }

    function delete($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);

        $this->xref->deleteGroup($id);

        $this->xref->deleteSlider($id);
        $this->db->deleteByPk($id);

        N2Cache::clearGroup(N2SmartSliderAbstract::getCacheId($id));
        N2Cache::clearGroup(N2SmartSliderAbstract::getAdminCacheId($id));

        self::markChanged($id);
    }

    function deleteSlides($id) {
        $slidesModel = new N2SmartsliderSlidesModel();
        $slidesModel->deleteBySlider($id);
        self::markChanged($id);
    }

    function duplicate($id, $withGroup = true) {

        $slider = $this->get($id);

        unset($slider['id']);

        $slider['title'] .= n2_(' - copy');
        $slider['time']  = date('Y-m-d H:i:s', N2Platform::getTime());

        try {
            $this->db->insert($slider);
            $newSliderId = $this->db->insertId();
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (!$newSliderId) {
            return false;
        }

        if ($slider['type'] == 'group') {
            $subSliders = $this->xref->getSliders($id);

            foreach ($subSliders AS $subSlider) {
                $newSubSliderID = $this->duplicate($subSlider['slider_id'], false);
                $this->xref->add($newSliderId, $newSubSliderID);
            }

        } else {

            $slidesModel = new N2SmartsliderSlidesModel();

            foreach ($slidesModel->getAll($id) AS $slide) {
                $slidesModel->copy($slide['id'], $newSliderId);
            }

            if ($withGroup) {
                $groups = $this->xref->getGroups($id);
                foreach ($groups AS $group) {
                    $this->xref->add($group['group_id'], $newSliderId);
                }
            }
        }

        return $newSliderId;
    }

    function exportSlider($id) {

    }

    function exportSliderAsHTML($id) {

    }

    public static function markChanged($sliderid) {
        N2SmartSliderHelper::getInstance()
                           ->setSliderChanged($sliderid, 1);
    }

    public static function box($slider, $appType) {
        $lt   = array();
        $lt[] = N2Html::tag('div', array(
            'class' => 'n2-ss-box-select',
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-tick2'), ''));

        $rt = array();

        $rb = array();

        $thumbnail = $slider['thumbnail'];
        if (empty($thumbnail)) {
            if ($slider['type'] == 'group') {
                $thumbnail = '$ss$/admin/images/group.png';
            } else {
                $thumbnail = '$system$/images/placeholder/image.png';
            }
        }

        $editUrl = $appType->router->createUrl(array(
            'slider/edit',
            array(
                'sliderid' => $slider['id']
            )
        ));

        $lb = array(
            N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
            ), '#' . $slider['id'])
        );
        if (!empty($slider['alias'])) {
            $lb[] = N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
                'style' => 'margin: 0 5px;'
            ), $slider['alias']);
        }


        $attributes = array(
            'style'         => 'background-image: URL("' . N2ImageHelper::fixed($thumbnail) . '");',
            'class'         => 'n2-ss-box-slider n2-box-selectable ' . ($slider['type'] == 'group' ? 'n2-ss-box-slider-group' : 'n2-ss-box-slider-slider'),
            'data-title'    => $slider['title'],
            'data-editUrl'  => $editUrl,
            'data-sliderid' => $slider['id']
        );
        N2Html::box(array(
            'attributes'         => $attributes,
            'lt'                 => implode('', $lt),
            'lb'                 => implode('', $lb),
            'rt'                 => implode('', $rt),
            'rtAttributes'       => array('class' => 'n2-on-hover'),
            'rb'                 => implode('', $rb),
            'overlay'            => N2Html::tag('div', array(
                'class' => 'n2-box-overlay n2-on-hover-flex'
            ), N2Html::link(n2_('Edit'), $editUrl, array('class' => 'n2-button n2-button-normal n2-button-s n2-button-green n2-radius-s n2-uc n2-h5'))),
            'placeholderContent' => N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-title'
                ), N2Html::link($slider['title'], $editUrl, array('class' => 'n2-h4'))) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('div', array(
                    'class' => 'n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-h4 n2-right',
                ), $slider['slides'] | 0))
        ));
    }

    public static function embedBox($mode, $slider, $appType) {
        $lt = array();

        $rt = array();

        $rb = array();

        $thumbnail = $slider['thumbnail'];
        if (empty($thumbnail)) {
            if ($slider['type'] == 'group') {
                $thumbnail = '$ss$/admin/images/group.png';
            } else {
                $thumbnail = '$system$/images/placeholder/image.png';
            }
        }

        $lb = array(
            N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
            ), '#' . $slider['id'])
        );
        if (!empty($slider['alias'])) {
            $lb[] = N2Html::tag('div', array(
                'class' => 'n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5',
                'style' => 'margin: 0 5px;'
            ), $slider['alias']);
        }


        $attributes = array(
            'style' => 'background-image: URL(' . N2ImageHelper::fixed($thumbnail) . ');',
            'class' => 'n2-ss-box-slider n2-box-selectable ' . ($slider['type'] == 'group' ? 'n2-ss-box-slider-group' : 'n2-ss-box-slider-slider')
        );

        if ($slider['type'] == 'group') {
            $attributes['onclick'] = 'window.location="' . $appType->router->createUrl(array(
                    'sliders/' . $mode,
                    array(
                        'groupID' => $slider['id']
                    )
                )) . '";';
        } else {
            if (empty($slider['alias'])) {
                $attributes['onclick'] = 'selectSlider(this, "id", "' . $slider['id'] . '", "' . $slider['id'] . '");';
            } else {
                $attributes['onclick'] = 'selectSlider(this, "alias", "' . $slider['alias'] . '", "' . $slider['id'] . '");';
            }
        }

        N2Html::box(array(
            'attributes'         => $attributes,
            'lt'                 => implode('', $lt),
            'lb'                 => implode('', $lb),
            'rt'                 => implode('', $rt),
            'rtAttributes'       => array('class' => 'n2-on-hover'),
            'rb'                 => implode('', $rb),
            'placeholderContent' => N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-title n2-h4'
                ), $slider['title']) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('div', array(
                    'class' => 'n2-button n2-button-normal n2-button-s n2-radius-s n2-button-grey n2-h4 n2-right',
                ), $slider['slides'] | 0))
        ));
    }

    public function order($groupID, $ids, $isReverse = false) {
        if (is_array($ids) && count($ids) > 0) {
            if ($isReverse) {
                $ids = array_reverse($ids);
            }
            $groupID = intval($groupID);
            if ($groupID <= 0) {
                $groupID = false;
            }
            $i = 0;
            foreach ($ids AS $id) {
                $id = intval($id);
                if ($id > 0) {
                    if (!$groupID) {
                        $this->db->update(array(
                            'ordering' => $i,
                        ), array(
                            "id" => $id
                        ));
                    } else {
                        $this->xref->db->update(array(
                            'ordering' => $i,
                        ), array(
                            "slider_id" => $id,
                            "group_id"  => $groupID
                        ));
                    }

                    $i++;
                }
            }

            return $i;
        }

        return false;
    }

    protected function getMaximalOrderValue() {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->getTable() . "";
        $result = $this->db->queryRow($query);

        if (isset($result['ordering'])) return $result['ordering'] + 1;

        return 0;
    }

    public static function renderGroupEditForm($slider) {

        $data = json_decode($slider['params'], true);
        if ($data == null) $data = array();
        $data['title']     = $slider['title'];
        $data['type']      = $slider['type'];
        $data['thumbnail'] = $slider['thumbnail'];
        $data['alias']     = isset($slider['alias']) ? $slider['alias'] : '';

        return self::editGroupForm($data);
    }

    private static function editGroupForm($data = array()) {

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
                                 ->getApplicationType('backend'));
        $form->set('class', 'nextend-smart-slider-admin');

        $form->loadArray($data);

        $groupSettings = new N2TabTabbed($form, 'slidergroup-settings', '', array(
            'active'     => 1,
            'underlined' => true
        ));

        $publishTab = new N2TabGroupped($groupSettings, 'publish', n2_('Publish'));

        $publishTab2 = new N2Tab($publishTab, 'publish', false);

        new N2ElementPublishSlider($publishTab2);


        $generalTab  = new N2TabGroupped($groupSettings, 'general', n2_('General'));
        $generalTab2 = new N2Tab($generalTab, 'slider-group');

        new N2ElementText($generalTab2, 'title', n2_('Name'), n2_('Group'), array(
            'style' => 'width:400px;'
        ));

        new N2ElementText($generalTab2, 'alias', n2_('Alias'), '', array(
            'style' => 'width:200px;'
        ));

        new N2ElementImage($generalTab2, 'thumbnail', n2_('Thumbnail'));

        new N2ElementHidden($generalTab2, 'type', '', 'group', array(
            'rowClass' => 'n2-hidden'
        ));


        echo $form->render('slider');

        N2Loader::import('libraries.form.elements.url');
        N2JS::addFirstCode('nextend.NextendElementUrlParams=' . N2ElementUrl::getNextendElementUrlParameters() . ';');

        return $data;
    }

    public static function renderShapeDividerForm() {
    }

    public static function renderParticleForm() {
    }
} 