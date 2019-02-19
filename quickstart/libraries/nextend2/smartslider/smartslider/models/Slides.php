<?php

class N2SmartsliderSlidesModel extends N2Model {

    private $currentData;

    /** @var  N2SmartSlider */
    private $slider;

    public function __construct() {
        parent::__construct("nextend2_smartslider3_slides");
    }

    public function get($id) {
        return $this->db->findByPk($id);
    }

    public function getAll($sliderid = 0, $where = '') {
        return $this->db->queryAll('SELECT * FROM ' . $this->getTable() . ' WHERE slider = ' . $sliderid . ' ' . $where . ' ORDER BY ordering', false, "assoc", null);
    }

    public function getRowFromPost($sliderId, $slide, $base64 = true) {

        if (!isset($slide['title'])) return false;

        if (isset($slide['publishdates'])) {
            $date = explode('|*|', $slide['publishdates']);
        } else {
            $date[0] = isset($slide['publish_up']) ? $slide['publish_up'] : null;
            $date[1] = isset($slide['publish_down']) ? $slide['publish_down'] : null;
            unset($slide['publish_up']);
            unset($slide['publish_down']);
        }
        $up   = strtotime(isset($date[0]) ? $date[0] : '');
        $down = strtotime(isset($date[1]) ? $date[1] : '');

        $generator_id = isset($slide['generator_id']) ? intval($slide['generator_id']) : 0;

        $slide['version'] = N2SS3::$version;

        $params = $slide;
        unset($params['title']);
        unset($params['slide']);
        unset($params['description']);
        unset($params['thumbnail']);
        unset($params['published']);
        unset($params['first']);
        unset($params['publishdates']);
        unset($params['generator_id']);

        return array(
            'title'        => $slide['title'],
            'slide'        => ($base64 ? n2_base64_decode($slide['slide']) : $slide['slide']),
            'description'  => $slide['description'],
            'thumbnail'    => $slide['thumbnail'],
            'published'    => (isset($slide['published']) ? $slide['published'] : 0),
            'publish_up'   => date('Y-m-d H:i:s', ($up && $up > 0 ? $up : strtotime('-1 day'))),
            'publish_down' => date('Y-m-d H:i:s', ($down && $down > 0 ? $down : strtotime('+10 years'))),
            'first'        => (isset($slide['first']) ? $slide['first'] : 0),
            'params'       => json_encode($params),
            'slider'       => $sliderId,
            'ordering'     => $this->getMaximalOrderValue($sliderId) + 1,
            'generator_id' => $generator_id
        );
    }

    /**
     * @param      $sliderId
     * @param      $slide
     * @param bool $base64
     *
     * @return bool
     */
    public function create($sliderId, $slide, $base64 = true) {

        $row = $this->getRowFromPost($sliderId, $slide, $base64);

        $slideId = $this->_create($row['title'], $row['slide'], $row['description'], $row['thumbnail'], $row['published'], $row['publish_up'], $row['publish_down'], 0, $row['params'], $row['slider'], $row['ordering'], $row['generator_id']);

        self::markChanged($sliderId);

        return $slideId;
    }

    protected function getMaximalOrderValue($sliderid = 0) {

        $query  = "SELECT MAX(ordering) AS ordering FROM " . $this->getTable() . " WHERE slider = :id";
        $result = $this->db->queryRow($query, array(
            ":id" => $sliderid
        ));

        if (isset($result['ordering'])) return $result['ordering'] + 1;

        return 0;
    }

    /**
     * @param N2SmartSlider $slider
     * @param               $slide
     *
     * @return N2Data
     */
    public function renderEditForm($slider, $slide) {
        $this->slider = $slider;
        if ($slide) {
            $params = json_decode($slide['params'], true);
            if ($params == null) $params = array();
            $params                 += $slide;
            $params['sliderid']     = $slide['slider'];
            $params['generator_id'] = $slide['generator_id'];
            echo '<input name="slide[generator_id]" value="' . $slide['generator_id'] . '" type="hidden" />';
        } else {
            $params = array(
                'static-slide' => N2Request::getInt('static')
            );
        }

        $data = new N2Data($params);

        if ($data->get('background-type') == '') {
            $params['background-type'] = 'color';
            if ($data->get('backgroundVideoMp4')) {
                $params['background-type'] = 'video';
            } else if ($data->get('backgroundImage')) {
                $params['background-type'] = 'image';
            }
        }

        $params['first'] = isset($slide['first']) ? $slide['first'] : 0;
        $this->editForm($params);

        return $data;
    }

    public function simpleEditForm($data = array()) {
        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('smartslider')
            ->getApplicationType('backend'));

        $data['publishdates'] = isset($data['publishdates']) ? $data['publishdates'] : ((isset($data['publish_up']) ? $data['publish_up'] : '') . '|*|' . (isset($data['publish_down']) ? $data['publish_down'] : ''));

        if (isset($data['slide'])) {
            $data['slide'] = n2_base64_encode($data['slide']);
        }

        $form->loadArray($data);

        $tab = new N2TabTabbedWithHide($form, 'slide-settings', false, array(
            'external'   => true,
            'active'     => true,
            'underlined' => true
        ));

        // Static slide does not need the background tab!
        if (!$this->slider->isStaticEdited || (!isset($data['static-slide']) || $data['static-slide'] != 1)) {


            $_slideBackground = new N2TabGroupped($tab, 'slide-settings-background', false);
            $slideBackground  = new N2Tab($_slideBackground, '');

            new N2ElementBackground($slideBackground, 'background-type', 'image');

            $slideImageBackground = new N2ElementGroup($slideBackground, 'background-image', n2_('Background'), array(
                'rowClass' => 'n2-ss-slide-background-image-param'
            ));
            new N2ElementImageManager($slideImageBackground, 'backgroundImage', n2_('Image'));
            new N2ElementNumber($slideImageBackground, 'backgroundFocusX', n2_('Focus'), 50, array(
                'subLabel' => 'X',
                'unit'     => '%',
                'min'      => 0,
                'max'      => 100,
                'style'    => 'width:22px;'
            ));
            new N2ElementNumber($slideImageBackground, 'backgroundFocusY', ' ', 50, array(
                'subLabel' => 'Y',
                'unit'     => '%',
                'min'      => 0,
                'max'      => 100,
                'style'    => 'width:22px;'
            ));

            new N2ElementNumberSlider($slideImageBackground, 'backgroundImageOpacity', n2_('Opacity'), 100, array(
                'unit'  => '%',
                'min'   => 0,
                'max'   => 100,
                'style' => 'width:22px;'
            ));

            new N2ElementNumberSlider($slideImageBackground, 'backgroundImageBlur', n2_('Blur'), 0, array(
                'unit'  => 'px',
                'min'   => 0,
                'max'   => 50,
                'style' => 'width:22px;'
            ));

            new N2ElementText($slideImageBackground, 'backgroundAlt', 'SEO - ' . n2_('Alt tag'));
            new N2ElementText($slideImageBackground, 'backgroundTitle', 'SEO - ' . n2_('Title'));

            $slideColorBackground = new N2ElementGroup($slideBackground, 'background-color', n2_('Background color'));

            new N2ElementColor($slideColorBackground, 'backgroundColor', n2_('Color'), 'ffffff00', array(
                'alpha' => true
            ));

            new N2ElementList($slideColorBackground, 'backgroundGradient', n2_('Gradient'), 'off', array(
                'options'       => array(
                    'off'        => n2_('Off'),
                    'vertical'   => '&darr;',
                    'horizontal' => '&rarr;',
                    'diagonal1'  => '&#8599;',
                    'diagonal2'  => '&#8600;'
                ),
                'relatedFields' => array(
                    'slidebackgroundColorEnd'
                )
            ));

            new N2ElementColor($slideColorBackground, 'backgroundColorEnd', n2_('Color end'), 'ffffff00', array(
                'alpha' => true
            ));

            new N2ElementImageListLabel($slideBackground, 'backgroundMode', n2_('Fill mode'), 'default', array(
                'options'  => array(
                    'default' => array(
                        'image' => '$ss$/admin/images/fillmode/default.png',
                        'label' => n2_('Slider\'s default')
                    ),
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
                ),
                'rowClass' => 'n2-ss-slide-background-image-param n2-ss-background-video-param'
            ));

            if ($this->slider->params->get('global-lightbox', 0)) {
                new N2ElementImageManager($slideBackground, 'ligthboxImage', n2_('Custom lightbox image'), '');
            }

            N2SSPluginSliderType::getSliderType($this->slider->data->get('type'))
                ->renderSlideFields($tab);

        }

        $_settings = new N2TabGroupped($tab, 'slide-settings', false);
        $settings  = new N2Tab($_settings, '');

        new N2ElementText($settings, 'title', n2_('Slide title'), n2_('Slide') . ' 1', array(
            'style' => 'width:400px;'
        ));
        new N2ElementTextarea($settings, 'description', n2_('Description'), '', array(
            'style'      => 'display: block;',
            'fieldStyle' => 'width:100%; resize: vertical; height: 50px;'
        ));

        $thumbnail = new N2ElementGroup($settings, 'thumbnail', n2_('Thumbnail'));
        new N2ElementImage($thumbnail, 'thumbnail', n2_('Image'));
        new N2ElementList($thumbnail, 'thumbnailType', n2_('Type'), 'default', array(
            'options' => array(
                'default'   => n2_('Default'),
                'videoDark' => n2_('Video')
            )
        ));

        $linkV1 = $form->getIfEmpty('link', '');
        if (!empty($linkV1)) {
            list($link, $target) = array_pad((array)N2Parse::parse($linkV1), 2, '');
            $form->un_set('link');
            $form->set('href', $link);
            $form->set('href-target', $target);
        }

        if (!$this->slider->isStaticEdited || (!isset($data['static-slide']) || $data['static-slide'] != 1)) {
            $link = new N2ElementGroup($settings, 'link', n2_('Link'));
            new N2ElementUrl($link, 'href', n2_('Link'), '', array(
                'style' => 'width:236px;'
            ));
            new N2ElementLinkTarget($link, 'href-target', n2_('Target window'));
        }

        new N2ElementHidden($settings, 'slide', n2_('Slide'), 'W10=', array(
            'rowClass' => 'n2-hidden'
        ));

        new N2ElementHidden($settings, 'guides', n2_('Guides'), 'e30=', array(
            'rowClass' => 'n2-hidden'
        ));

        $properties = new N2ElementGroup($settings, 'properties', n2_('Properties'), array(
            'rowClass' => 'n2-expert'
        ));
        new N2ElementOnOff($properties, 'published', n2_('Published'), 1);
        new N2ElementOnOff($properties, 'first', n2_('First slide'), 0, array(
            'rowClass' => 'n2-hidden'
        ));
        new N2ElementOnOff($properties, 'static-slide', n2_('Static overlay'), 0, array(
            'rowClass' => 'n2-expert'
        ));

        $publishDates = new N2ElementMixed($settings, 'publishdates', n2_('Published between'), '0000-00-00 00:00:00|*|0000-00-00 00:00:00', array(
            'rowClass' => 'n2-expert'
        ));
        new N2ElementDate($publishDates, 'publishdates-1', n2_('Publish up'));
        new N2ElementDate($publishDates, 'publishdates-2', n2_('Publish down'));

        new N2ElementNumber($settings, 'slide-duration', n2_('Slide duration'), 0, array(
            'unit'  => 'ms',
            'style' => 'width:40px;'
        ));


        if ($form->get('generator_id') > 0) {
            $_generatorTab = new N2TabGroupped($tab, 'generator', n2_('Generator'));
            $generatorTab  = new N2Tab($_generatorTab, '');
            new N2ElementNumber($generatorTab, 'record-slides', n2_('Slides'), 1, array(
                'unit' => n2_('slides'),
                'wide' => 3,
            ));

            new N2ElementButton($generatorTab, 'button', '', n2_('Edit generator'), array(
                'url' => N2Base::getApplication('smartslider')
                    ->getApplicationType('backend')->router->createUrl(array(
                        "generator/edit",
                        array(
                            'generator_id' => $this->currentData['generator_id']
                        )
                    ))
            ));
        }

        echo $form->render('slide');
    }

    /**
     * @param $tab N2TabTabbedWithHide
     */
    public function removeSlideSettingsBackground($tab) {

        $tab->removeTab('background');

    }


    /**
     * @param array $data
     */
    private function editForm($data = array()) {

        $this->currentData = $data;

        $this->simpleEditForm($data);

        N2JS::addFirstCode("new N2Classes.Form('smartslider-form','', {});");
    }

    /**
     * @param int  $id
     * @param      $slide
     * @param bool $base64
     *
     * @return bool
     */
    public function save($id, $slide, $base64 = true) {
        if (!isset($slide['title']) || $id <= 0) return false;

        if (isset($slide['publishdates'])) {
            $date = explode('|*|', $slide['publishdates']);
        } else {
            $date[0] = $slide['publish_up'];
            $date[1] = $slide['publish_down'];
            unset($slide['publish_up']);
            unset($slide['publish_down']);
        }
        $up   = strtotime(isset($date[0]) ? $date[0] : '');
        $down = strtotime(isset($date[1]) ? $date[1] : '');

        $slide['version'] = N2SS3::$version;

        $tmpslide = $slide;
        unset($tmpslide['title']);
        unset($tmpslide['slide']);
        unset($tmpslide['description']);
        unset($tmpslide['thumbnail']);
        unset($tmpslide['published']);
        unset($tmpslide['publishdates']);
        unset($tmpslide['generator_id']);

        $this->db->update(array(
            'title'        => $slide['title'],
            'slide'        => ($base64 ? n2_base64_decode($slide['slide']) : $slide['slide']),
            'description'  => $slide['description'],
            'thumbnail'    => $slide['thumbnail'],
            'published'    => (isset($slide['published']) ? $slide['published'] : 0),
            'publish_up'   => date('Y-m-d H:i:s', ($up && $up > 0 ? $up : strtotime('-1 day'))),
            'publish_down' => date('Y-m-d H:i:s', ($down && $down > 0 ? $down : strtotime('+10 years'))),
            'params'       => json_encode($tmpslide)
        ), array('id' => $id));

        self::markChanged(N2Request::getInt('sliderid'));

        return $id;
    }

    public function updateParams($id, $params) {

        $this->db->update(array(
            'params' => json_encode($params)
        ), array('id' => $id));

        return $id;
    }

    public function quickSlideUpdate($slide, $title, $description, $link) {

        $params         = json_decode($slide['params'], true);
        $params['link'] = $link;

        return $this->db->update(array(
            'title'       => $title,
            'description' => $description,
            'params'      => json_encode($params)
        ), array('id' => $slide['id']));
    }

    public function delete($id) {

        $slide = $this->get($id);

        if ($slide['generator_id'] > 0) {
            $slidesWithSameGenerator = $this->getAll($slide['slider'], 'AND generator_id = ' . intval($slide['generator_id']));
            if (count($slidesWithSameGenerator) == 1) {
                $generatorModel = new N2SmartsliderGeneratorModel();
                $generatorModel->delete($slide['generator_id']);
            }
        }

        $this->db->deleteByAttributes(array(
            "id" => intval($id)
        ));

        self::markChanged($slide['slider']);

    }

    public function removeFourByteChars($text){
        return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xEF\xBF\xBD", $text);
    }

    public function createQuickImage($image, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $parameters = array(
            'backgroundImage' => $image['image']
        );

        if (!empty($image['alt'])) {
            $parameters['backgroundAlt'] = $image['alt'];
        }

        $parameters['version'] = N2SS3::$version;

        $slideID = $this->_create($image['title'], json_encode(array()), $image['description'], $image['image'], 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
        self::markChanged($sliderId);

        return $slideID;
    }

    public function createQuickVideo($video, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $parameters = array(
            'thumbnailType' => 'videoDark'
        );

        N2Loader::import('libraries.slidebuilder.component', 'smartslider');
        N2Loader::importAll('libraries.slidebuilder', 'smartslider');

        $slideBuilder = new N2SmartSliderSlideBuilder();

        switch ($video['type']) {
            case 'youtube':
                $youtube = new N2SmartSliderSlideBuilderLayer($slideBuilder, 'youtube');
                $youtube->set(array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ));
                $youtube->item->set(array(
                    "code"       => $video['video'],
                    "youtubeurl" => $video['video'],
                    "image"      => $video['image']
                ));
                break;
            case 'vimeo':
                $vimeo = new N2SmartSliderSlideBuilderLayer($slideBuilder, 'vimeo');
                $vimeo->set(array(
                    'desktopportraitwidth'  => '100%',
                    'desktopportraitheight' => '100%',
                    'desktopportraitalign'  => 'left',
                    'desktopportraitvalign' => 'top'
                ));
                $vimeo->item->set(array(
                    "vimeourl" => $video['video'],
                    "image"    => ''
                ));
                break;
            case 'video':
            default:
                return false;
        }

        $parameters['version'] = N2SS3::$version;

        $video['description'] = $this->removeFourByteChars($video['description']);
        $video['title']       = $this->removeFourByteChars($video['title']);

        $slideID = $this->_create($video['title'], json_encode($slideBuilder->getLayersData()), $video['description'], $video['image'], 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
        self::markChanged($sliderId);

        return $slideID;
    }

    public function createQuickPost($post, $sliderId) {
        $publish_up   = date('Y-m-d H:i:s', strtotime('-1 day'));
        $publish_down = date('Y-m-d H:i:s', strtotime('+10 years'));

        $data = new N2Data($post);

        $parameters = array(
            'backgroundImage'        => $data->get('image'),
            'backgroundImageOpacity' => 20,
            'backgroundColor'        => '000000FF'
        );

        $title       = $data->get('title');
        $description = $data->get('description');


        $parameters['version'] = N2SS3::$version;

        $slideID = $this->_create($title, json_encode($this->getSlideLayers($title, $description, $data->get('link'))), $description, $data->get('image'), 1, $publish_up, $publish_down, 0, json_encode($parameters), $sliderId, $this->getMaximalOrderValue($sliderId), '');
        self::markChanged($sliderId);

        return $slideID;
    }

    private function getSlideLayers($hasTitle = false, $hasDescription = false, $button = false) {

        N2Loader::import('libraries.slidebuilder.component', 'smartslider');
        N2Loader::importAll('libraries.slidebuilder', 'smartslider');

        $slideBuilder = new N2SmartSliderSlideBuilder();

        $slideBuilder->content->set(array(
            'desktopportraitpadding' => '10|*|100|*|10|*|100|*|px+',
            'mobileportraitpadding'  => '10|*|10|*|10|*|10|*|px+'
        ));

        if ($hasTitle) {

            $heading = new N2SmartSliderSlideBuilderLayer($slideBuilder->content, 'heading');
            $heading->item->set(array(
                'heading' => '{name/slide}',
                'font'    => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"48||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
            ));

        }

        if ($hasDescription) {
            $text = new N2SmartSliderSlideBuilderLayer($slideBuilder->content, 'text');
            $text->set(array(
                'desktopportraitmargin' => '0|*|0|*|20|*|0|*|px+',
            ));
            $text->item->set(array(
                'content' => '{description/slide}',
                'font'    => base64_encode('{"name":"Static","data":[{"extra":"","color":"ffffffff","size":"18||px","tshadow":"0|*|0|*|0|*|000000ff","afont":"Roboto,Arial","lineheight":"1.5","bold":0,"italic":0,"underline":0,"align":"inherit","letterspacing":"normal","wordspacing":"normal","texttransform":"none"},{"extra":""}]}'),
            ));
        }

        if (!empty($button)) {
            $buttonLayer = new N2SmartSliderSlideBuilderLayer($slideBuilder->content, 'button');
            $buttonLayer->item->set(array(
                'content' => n2_('Read more'),
                'link'    => $button . '|*|_self'
            ));
        }

        return $slideBuilder->getLayersData();
    }

    public function import($slide, $sliderId) {
        return $this->_create($slide['title'], $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], $slide['first'], $slide['params']->toJson(), $sliderId, $slide['ordering'], $slide['generator_id']);
    }

    private function _create($title, $slide, $description, $thumbnail, $published, $publish_up, $publish_down, $first, $params, $slider, $ordering, $generator_id) {
        $this->db->insert(array(
            'title'        => $title,
            'slide'        => $slide,
            'description'  => $description,
            'thumbnail'    => $thumbnail,
            'published'    => $published,
            'publish_up'   => $publish_up,
            'publish_down' => $publish_down,
            'first'        => $first,
            'params'       => $params,
            'slider'       => $slider,
            'ordering'     => $ordering,
            'generator_id' => $generator_id
        ));

        return $this->db->insertId();
    }

    public function duplicate($id) {
        $slide = $this->get($id);

        // Shift the afterwards slides ++
        $this->db->query("UPDATE {$this->getTable()} SET ordering = ordering + 1 WHERE slider = :sliderid AND ordering > :ordering", array(
            ":sliderid" => intval($slide['slider']),
            ":ordering" => intval($slide['ordering'])
        ), '');

        if (!empty($slide['generator_id'])) {
            $generatorModel        = new N2SmartsliderGeneratorModel();
            $slide['generator_id'] = $generatorModel->duplicate($slide['generator_id']);
        }

        $slide['slide'] = N2Data::json_encode(N2SSSlideComponent::translateUniqueIdentifier(json_decode($slide['slide'], true)));

        $slideId = $this->_create($slide['title'] . n2_(' - copy'), $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], 0, $slide['params'], $slide['slider'], $slide['ordering'] + 1, $slide['generator_id']);

        self::markChanged($slide['slider']);

        return $slideId;
    }

    public function copy($id, $targetSliderId) {
        $id    = intval($id);
        $slide = $this->get($id);
        if ($slide['generator_id'] > 0) {
            $generatorModel        = new N2SmartSliderGeneratorModel();
            $slide['generator_id'] = $generatorModel->duplicate($slide['generator_id']);
        }

        $slide['slide'] = N2Data::json_encode(N2SSSlideComponent::translateUniqueIdentifier(json_decode($slide['slide'], true)));

        $slideId = $this->_create($slide['title'], $slide['slide'], $slide['description'], $slide['thumbnail'], $slide['published'], $slide['publish_up'], $slide['publish_down'], 0, $slide['params'], $targetSliderId, $this->getMaximalOrderValue($targetSliderId), $slide['generator_id']);
        self::markChanged($slide['slider']);

        return $slideId;
    }

    public function first($id) {
        $slide = $this->get($id);

        $this->db->update(array("first" => 0), array(
            "slider" => $slide['slider']
        ));

        $this->db->update(array(
            "first" => 1
        ), array(
            "id" => $id
        ));

        self::markChanged($slide['slider']);
    }

    public function publish($id) {

        self::markChanged(N2Request::getInt('sliderid'));

        return $this->db->update(array(
            "published" => 1
        ), array("id" => intval($id)));
    }

    public function unPublish($id) {
        $this->db->update(array(
            "published" => 0
        ), array(
            "id" => intval($id)
        ));

        self::markChanged(N2Request::getInt('sliderid'));

    }

    public function deleteBySlider($sliderid) {

        $slides = $this->getAll($sliderid);
        foreach ($slides as $slide) {
            $this->delete($slide['id']);
        }
        self::markChanged($sliderid);
    }

    /**
     * @param $sliderid
     * @param $ids
     *
     * @return bool|int
     */
    public function order($sliderid, $ids) {
        if (is_array($ids) && count($ids) > 0) {
            $i = 0;
            foreach ($ids AS $id) {
                $id = intval($id);
                if ($id > 0) {
                    $update = $this->db->update(array(
                        'ordering' => $i,
                    ), array(
                        "id"     => $id,
                        "slider" => $sliderid
                    ));

                    $i++;
                }
            }

            self::markChanged($sliderid);

            return $i;
        }

        return false;
    }

    public static function markChanged($sliderid) {
        N2SmartSliderHelper::getInstance()
            ->setSliderChanged($sliderid, 1);
    }

    public function makeStatic($slideId) {
        $slideData = $this->get($slideId);
        if ($slideData['generator_id'] > 0) {
            $sliderObj = new N2SmartSlider($slideData['slider'], array());
            $rootSlide = new N2SmartSliderSlide($sliderObj, $slideData);
            $rootSlide->initGenerator(array());
            $slides = $rootSlide->expandSlide();

            // Shift the afterwards slides with the slides count
            $this->db->query("UPDATE {$this->getTable()} SET ordering = ordering + " . count($slides) . " WHERE slider = :sliderid AND ordering > :ordering", array(
                ":sliderid" => intval($slideData['slider']),
                ":ordering" => intval($slideData['ordering'])
            ), '');

            $firstUsed = false;
            $i         = 1;
            foreach ($slides AS $slide) {
                $row = $slide->getRow();
                // set the proper ordering
                $row['ordering'] += $i;
                if ($row['first']) {
                    // Make sure to mark only one slide as start slide
                    if ($firstUsed) {
                        $row['first'] = 0;
                    } else {
                        $firstUsed = true;
                    }
                }
                $this->db->insert($row);
                $i++;
            }

            $this->db->query("UPDATE {$this->getTable()} SET published = 0, first = 0 WHERE id = :id", array(
                ":id" => $slideData['id']
            ), '');

            return count($slides);
        } else {
            return false;
        }
    }

    /**
     * @param $slide  N2SmartSliderSlide
     * @param $slider N2SmartSliderAbstract
     * @param $widget
     * @param $appType
     *
     * @throws Exception
     */
    public static function box($slide, $slider, $appType, $optimize) {
        $lt   = array();
        $lt[] = N2Html::tag('div', array(
            'class' => 'n2-ss-box-select',
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-tick2'), ''));

        $rt = array();

        $rb = array();

        $image = $slide->getThumbnail();
        if (empty($image)) {
            $image = N2ImageHelper::fixed('$system$/images/placeholder/image.png');
        }

        $editUrl = $appType->router->createUrl(array(
            'slides/edit',
            array(
                'sliderid' => $slider->sliderId,
                'slideid'  => $slide->id
            )
        ));

        $lb = array();

        if ($slide->parameters->get('static-slide', 0)) {
            $lb[] = '<div class="n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5 n2-uc">' . n2_('Static slide') . '</div>';
        }

        if ($slide->generator_id > 0) {
            $lb[] = '<div class="n2-button n2-button-normal n2-button-xs n2-radius-s n2-button-grey n2-h5 n2-uc">' . n2_('Dynamic slide') . '</div>';
        }

        $class = 'n2-box-small n2-box-selectable n2-box-slide ';
        $class .= ($slide->isFirst() ? ' n2-slide-state-first' : '');
        $class .= ($slide->published ? ' n2-slide-state-published' : '');
        $class .= ($slide->hasGenerator() ? ' n2-slide-state-has-generator' : '');
        $class .= ($slide->isCurrentlyEdited() ? ' n2-ss-slide-active' : '');

        $attributes = array(
            'style'            => 'background-image: URL("' . $optimize->adminOptimizeThumbnail($image) . '");',
            'class'            => $class,
            'data-slideid'     => $slide->id,
            'data-title'       => $slide->getRawTitle(),
            'data-description' => $slide->getRawDescription(),
            'data-link'        => $slide->getRawLink(),
            'data-image'       => N2ImageHelper::fixed($image),
            'data-editUrl'     => $editUrl
        );

        if ($slide->hasGenerator()) {
            $attributes['data-generator'] = $appType->router->createUrl(array(
                'generator/edit',
                array(
                    'generator_id' => $slide->generator_id
                )
            ));
        }
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
                    'class' => 'n2-box-placeholder-title n2-h4'
                ), N2Html::link(n2_esc_html($slide->getTitle(true)) . ($slide->hasGenerator() ? ' [' . $slide->getSlideStat() . ']' : ''), $editUrl, array('class' => 'n2-h4'))) . N2Html::tag('div', array(
                    'class' => 'n2-box-placeholder-buttons'
                ), N2Html::tag('i', array('class' => 'n2-slide-first n2-i n2-it n2-i-star'), '') . N2Html::tag('a', array(
                        'class'      => 'n2-slide-published',
                        'data-n2tip' => 'Publish - Unpublish',
                        'href'       => $appType->router->createUrl(array(
                            'slides/publish',
                            array(
                                'sliderid' => $slider->sliderId,
                                'slideid'  => $slide->id
                            ) + N2Form::tokenizeUrl()
                        ))
                    ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-unpublished'), '')))

        ));
    }

    public static function prepareSample(&$layers) {
        for ($i = 0; $i < count($layers); $i++) {

            if (isset($layers[$i]['type'])) {
                switch ($layers[$i]['type']) {
                    case 'content':
                        N2SSSlideComponentContent::prepareSample($layers[$i]);
                        break;
                    case 'row':
                        N2SSSlideComponentRow::prepareSample($layers[$i]);
                        break;
                    case 'col':
                        N2SSSlideComponentCol::prepareSample($layers[$i]);
                        break;
                    case 'group':
                        N2SSSlideComponentGroup::prepareSample($layers[$i]);
                        break;
                    default:
                        N2SSSlideComponentLayer::prepareSample($layers[$i]);
                }
            } else {
                N2SSSlideComponentLayer::prepareSample($layers[$i]);
            }
        }
    }
} 