<?php

class N2SmartSliderSlide extends N2SmartSliderComponentOwnerAbstract {

    /**
     * @var N2SmartSliderAbstract
     */
    protected $sliderObject;
    public    $id = 0, $slider = 0, $publish_up, $publish_down, $published = 1, $first = 0, $slide = '', $ordering = 0, $generator_id = 0;

    protected $title = '', $description = '', $thumbnail = '';

    public $parameters, $background = '';

    protected $isProcessedFirst = false;

    protected $html = '';

    protected $visible = 1;

    public $hasLink = false;

    /**
     * @var bool|N2SmartSliderSlidesGenerator
     */
    protected $generator = false;
    protected $variables = array();

    /**
     * @var N2SmartSliderLayerRenderContext
     */
    protected $context;

    public $index = -1;

    public $attributes = array(), $linkAttributes = array(), $containerAttributes = array(
        'class'             => 'n2-ss-layers-container n2-ow',
        'data-csstextalign' => 'center',
        'style'             => ''
    ), $classes = '', $style = '';

    public $nextCacheRefresh = 2145916800; // 2038

    /**
     * N2SmartSliderSlide constructor.
     *
     * @param $slider N2SmartSliderAbstract
     * @param $data   array
     */
    public function __construct($slider, $data) {
        $this->parameters = new N2Data($data['params'], true);
        unset($data['params']);
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        $this->slide = json_decode($this->slide, true);

        if ($this->parameters->get('version') == '') {

            self::fixOldZIndexes($this->slide);

        }

        $this->sliderObject = $slider;
        $this->renderable   = $slider;
        $this->onCreate();
    }

    private static function fixOldZIndexes(&$layers) {
        /**
         * If we do not have version info for the slide, we should do the check for the old zIndexed storage and sort the layers to the new structure.
         */
        if (is_array($layers)) {
            for ($i = 0; $i < count($layers); $i++) {
                if (!isset($layers[$i]['zIndex'])) {
                    if (isset($layers[$i]['style']) && preg_match('/z\-index:[ ]*([0-9]+);/', $layers[$i]['style'], $matches)) {
                        $layers[$i]['zIndex'] = intval($matches[1]);
                    } else {
                        $layers[$i]['zIndex'] = 0;
                    }
                }

                if (isset($layers[$i]['type']) && $layers[$i]['type'] == 'group') {
                    self::fixOldZIndexes($layers[$i]['layers']);
                }
            }

            if (isset($layers[0]['zIndex'])) {
                usort($layers, "N2SSSlidePlacementAbsolute::sortOldZIndex");
            }
        }
    }

    public function __clone() {
        $this->parameters = clone $this->parameters;
    }

    protected function onCreate() {
        N2Pluggable::doAction('ssSlide', array( $this ));
    }

    public function initGenerator($extend = array()) {
        if ($this->generator_id > 0) {
            N2Loader::import('libraries.slider.generator.generator', 'smartslider');
            $this->generator = new N2SmartSliderSlidesGenerator($this, $this->sliderObject, $extend);
        }
    }

    public function hasGenerator() {
        return !!$this->generator;
    }

    public function isComponentVisible($generatorVisibleVariable) {
        return !empty($generatorVisibleVariable) && $this->hasGenerator();
    }

    /**
     * @return N2SmartSliderSlide[]
     */
    public function expandSlide() {
        return $this->generator->getSlides();
    }

    public function expandSlideAdmin() {
        return $this->generator->getSlidesAdmin();
    }

    public function fillSample() {
        if ($this->hasGenerator()) {
            $this->generator->fillSample();
        }
    }

    public function setVariables($variables) {
        $this->variables = array_merge($this->variables, (array)$variables);
    }

    public function isFirst() {
        return !!$this->first;
    }

    public function isProcessedFirst() {
        return $this->isProcessedFirst;
    }

    public function isCurrentlyEdited() {
        return N2Request::getInt('slideid') == $this->id;
    }

    public function setIndex($index) {
        $this->index = $index;
    }

    public function setFirst() {
        //$this->classes .= ' n2-ss-slide-active';
        $this->isProcessedFirst = true;

        $this->attributes['data-first'] = '1';
    }

    public function prepare() {
        $this->variables['slide'] = array(
            'name'        => $this->getTitle(),
            'description' => $this->getDescription()
        );
    }

    public function setSlidesParams() {

        $this->background = $this->sliderObject->features->makeBackground($this);

        $this->addSlideLink();

        $this->attributes['data-slide-duration'] = n2_floatval(max(0, $this->parameters->get('slide-duration', 0) ) / 1000);
        $this->attributes['data-id']             = $this->id;

        $this->classes .= ' n2-ss-slide-' . $this->id;

        $this->sliderObject->features->makeSlide($this);

        $this->renderHtml();
    }

    protected function addSlideLink() {

        $linkV1 = $this->parameters->getIfEmpty('link', '');
        if (!empty($linkV1)) {
            list($link, $target) = array_pad((array)N2Parse::parse($linkV1), 2, '');
            $this->parameters->un_set('link');
            $this->parameters->set('href', $link);
            $this->parameters->set('href-target', $target);
        }

        $url    = $this->parameters->get('href');
        $target = $this->parameters->get('href-target');

        if (!empty($url) && $url != '#') {
            $url = $this->fill($url);
        }

        if (!empty($url) && $url != '#') {

            if (empty($target)) {
                $target = '_self';
            }

            $url = N2ImageHelper::fixed($url);

            $this->linkAttributes['onclick'] = '';
            if (strpos($url, 'javascript:') === 0) {
                $this->linkAttributes['onclick'] = $url;
            } else {

                N2Loader::import('libraries.link.link');
                $url = N2LinkParser::parse($url, $this->linkAttributes);

                $this->linkAttributes['data-href'] = (N2Platform::$isJoomla ? JRoute::_($url, false) : $url);
                if (empty($this->linkAttributes['onclick'])) {
                    if ($target == '_blank') {
                        $this->linkAttributes['data-n2click'] = "window.open(this.getAttribute('data-href'),'_blank');";
                    } else {
                        $this->linkAttributes['data-n2click'] = "n2const.setLocation(this.getAttribute('data-href'))";
                    }
                    $this->linkAttributes['data-n2middleclick'] = "window.open(this.getAttribute('data-href'),'_blank');";
                }
            }
            if (!isset($this->linkAttributes['style'])) {
                $this->linkAttributes['style'] = '';
            }
            $this->linkAttributes['style'] .= 'cursor:pointer;';
            $this->hasLink                 = true;
        }
    }

    public function getRawLink() {
        return $this->parameters->getIfEmpty('link', '|*|');
    }

    public function getSlider() {
        return $this->sliderObject;
    }

    protected function renderHtml() {
        if (empty($this->html)) {

            N2SSSlideComponent::$isAdmin = $this->sliderObject->isAdmin;

            $mainContainer = new N2SSSlideComponentMain(null, $this, null, $this->slide);

            $this->html = N2Html::tag('div', $this->containerAttributes, $mainContainer->render($this->sliderObject->isAdmin));
        }
    }

    public function finalize() {

        if ($this->sliderObject->exposeSlideData['title']) {
            $title = $this->getTitle();
            if (!empty($title)) {
                $this->attributes['data-title'] = N2SmartSlider::addCMSFunctions(N2Translation::_($title));
            }
        }

        if ($this->sliderObject->exposeSlideData['description']) {
            $description = $this->getDescription();
            if (!empty($description)) {
                $this->attributes['data-description'] = N2SmartSlider::addCMSFunctions(N2Translation::_($description));
            }
        }

        if ($this->sliderObject->exposeSlideData['thumbnail']) {
            $thumbnail = $this->getThumbnail();
            if (!empty($thumbnail)) {
                $this->attributes['data-thumbnail'] = $this->sliderObject->features->optimize->optimizeThumbnail($thumbnail);
            }
        }

        if ($this->sliderObject->exposeSlideData['thumbnailType']) {
            $thumbnailType = $this->parameters->get('thumbnailType', 'default');

            if ($thumbnailType != 'default') {
                $this->attributes['data-thumbnail-type'] = $thumbnailType;
            }
        }

        if ($this->hasLink) {
            $this->attributes['data-haslink'] = 1;
        }
    }

    public function getHTML() {
        return $this->html;
    }

    public function getAsStatic() {
        $mainContainer = new N2SSSlideComponentMain(null, $this, null, $this->slide);

        return N2Html::tag('div', array(
            'class'             => 'n2-ss-static-slide n2-ow' . $this->classes,
            'data-csstextalign' => 'center'
        ), $mainContainer->render($this->sliderObject->isAdmin));
    }

    public function isStatic() {
        if ($this->parameters->get('static-slide', 0)) {
            return true;
        }

        return false;
    }

    private static function splitTokens($input) {
        $tokens       = array();
        $currentToken = "";
        $nestingLevel = 0;
        for ($i = 0; $i < strlen($input); $i++) {
            $currentChar = $input[$i];
            if ($currentChar === "," && $nestingLevel === 0) {
                $tokens[]     = $currentToken;
                $currentToken = "";
            } else {
                $currentToken .= $currentChar;
                if ($currentChar === "(") {
                    $nestingLevel++;
                } else if ($currentChar === ")") {
                    $nestingLevel--;
                }
            }
        }
        if (strlen($currentToken)) {
            $tokens[] = $currentToken;
        }

        return $tokens;
    }

    public function fill($value) {
        if (!empty($this->variables) && !empty($value)) {
            return preg_replace_callback('/{((([a-z]+)\(([^}]+)\))|([a-zA-Z0-9][a-zA-Z0-9_\/]*))}/', array(
                $this,
                'parseFunction'
            ), $value);
        }

        return $value;
    }

    private function parseFunction($match) {
        if (!isset($match[5])) {
            $args = self::splitTokens($match[4]);
            for ($i = 0; $i < count($args); $i++) {
                $args[$i] = $this->parseVariable($args[$i]);
            }

            if (method_exists($this, '_' . $match[3])) {
                return call_user_func_array(array(
                    $this,
                    '_' . $match[3]
                ), $args);
            }

            return $match[0];
        } else {
            return $this->parseVariable($match[5]);
        }
    }

    private function parseVariable($variable) {
        preg_match('/^("|\')(.*)("|\')$/', $variable, $match);
        if (!empty($match)) {
            return $match[2];
        }

        preg_match('/((([a-z]+)\(([^}]+)\)))/', $variable, $match);
        if (!empty($match)) {
            return call_user_func(array(
                $this,
                'parseFunction'
            ), $match);
        } else {
            preg_match('/([a-zA-Z][0-9a-zA-Z_]*)(\/([0-9a-z]+))?/', $variable, $match);
            if ($match) {
                $index = empty($match[3]) ? 0 : $match[3];
                if (is_numeric($index)) {
                    $index = max(1, intval($index)) - 1;
                }

                if (isset($this->variables[$index]) && isset($this->variables[$index][$match[1]])) {
                    return $this->variables[$index][$match[1]];
                } else {
                    return '';
                }
            }

            return $variable;
        }
    }

    private function _fallback($s, $def) {
        if (empty($s)) {
            return $def;
        }

        return $s;
    }

    private function _cleanhtml($s) {
        return strip_tags($s, '<p><a><b><br><br/><i>');
    }

    private function _removehtml($s) {
        return strip_tags($s);
    }

    private function _splitbychars($s, $start = 0, $length = null) {
        return N2String::substr($s, $start, $length);
    }

    private function _splitbywords($s, $start, $length) {
        $len      = N2String::strlen($s);
        $posStart = max(0, $start == 0 ? 0 : N2String::strpos($s, ' ', $start));
        $posEnd   = max(0, $length > $len ? $len : N2String::strpos($s, ' ', $length));
        if ($posEnd == 0 && $length <= $len) $posEnd = $len;

        return N2String::substr($s, $posStart, $posEnd);
    }

    private function _findimage($s, $index) {
        $index = isset($index) ? intval($index) - 1 : 0;
        preg_match_all('/(<img.*?src=[\'"](.*?)[\'"][^>]*>)|(background(-image)??\s*?:.*?url\((["|\']?)?(.+?)(["|\']?)?\))/i', $s, $r);
        if (isset($r[2]) && !empty($r[2][$index])) {
            $s = $r[2][$index];
        } else if (isset($r[6]) && !empty($r[6][$index])) {
            $s = trim($r[6][$index], "'\" \t\n\r\0\x0B");
        } else {
            $s = '';
        }

        return $s;
    }

    private function _findlink($s, $index) {
        $index = isset($index) ? intval($index) - 1 : 0;
        preg_match_all('/href=["\']?([^"\'>]+)["\']?/i', $s, $r);
        if (isset($r[1]) && !empty($r[1][$index])) {
            $s = $r[1][$index];
        } else {
            $s = '';
        }

        return $s;
    }

    private function _removevarlink($s) {
        return preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', '', $s);
    }

    private function _removelinebreaks($s) {
        return preg_replace('/\r?\n|\r/', '', $s);
    }

    public function getTitle($isAdmin = false) {
        $title = $this->fill($this->title);
        if ($isAdmin && empty($title)) {
            return n2_('Empty title');
        }

        return $title;
    }

    public function getDescription() {
        return $this->fill($this->description);
    }

    public function getRawTitle() {
        return $this->title;
    }

    public function getRawDescription() {
        return $this->description;
    }

    public function getBackgroundImage() {
        return $this->fill($this->parameters->get('backgroundImage'));
    }

    public function getThumbnail() {
        $image = $this->thumbnail;
        if (empty($image)) {
            $image = $this->parameters->get('backgroundImage');
        }

        return N2ImageHelper::fixed($this->fill($image));
    }

    public function getLightboxImage() {
        $image = $this->fill($this->parameters->get('ligthboxImage'));
        if (empty($image)) {
            return $this->getBackgroundImage();
        }

        return $image;
    }

    public function getRow() {
        $this->fillParameters();

        return array(
            'title'        => $this->getTitle(),
            'slide'        => $this->getFilledSlide(),
            'description'  => $this->getDescription(),
            'thumbnail'    => N2ImageHelper::dynamic($this->getThumbnail()),
            'published'    => $this->published,
            'publish_up'   => $this->publish_up,
            'publish_down' => $this->publish_down,
            'first'        => $this->first,
            'params'       => $this->parameters->toJSON(),
            'slider'       => $this->slider,
            'ordering'     => $this->ordering,
            'generator_id' => 0
        );
    }

    public function fillParameters() {
        $this->parameters->set('backgroundImage', $this->fill($this->parameters->get('backgroundImage')));
        $this->parameters->set('backgroundAlt', $this->fill($this->parameters->get('backgroundAlt')));
        $this->parameters->set('backgroundTitle', $this->fill($this->parameters->get('backgroundTitle')));
        $this->parameters->set('backgroundVideoMp4', $this->fill($this->parameters->get('backgroundVideoMp4')));
        $this->parameters->set('link', $this->fill($this->parameters->get('link')));
    }

    public function getFilledSlide() {
        $children = $this->slide;
        if (!$this->underEdit) {
            $children = N2SSSlideComponent::translateUniqueIdentifier($children);
        }

        $this->fillLayers($children);

        return json_encode($children);
    }

    public function setNextCacheRefresh($time) {
        $this->nextCacheRefresh = min($this->nextCacheRefresh, $time);
    }

    public function setVisibility($visibility) {
        $this->visible = $visibility;
    }

    public function isVisible() {

        if (!$this->visible) {
            return false;
        }

        $time = N2Platform::getTime();

        $publish_up   = strtotime($this->publish_up);
        $publish_down = strtotime($this->publish_down);

        if ($publish_down) {
            if ($publish_down > $time) {
                $this->setNextCacheRefresh($publish_down);
            } else {
                return false;
            }
        }

        if ($publish_up) {
            if ($publish_up > $time) {
                $this->setNextCacheRefresh($publish_up);

                return false;
            }
        }

        return true;
    }

    public function getSlideCount() {
        if ($this->hasGenerator()) {
            return $this->generator->getSlideCount();
        }

        return 1;
    }

    public function getSlideStat() {
        if ($this->hasGenerator()) {
            return $this->generator->getSlideStat();
        }

        return '1/1';
    }


    public function getElementID() {
        return $this->getSlider()->elementId;
    }

    public function addScript($script, $name = false) {
        $this->sliderObject->addScript($script, $name);
    }

    public function isScriptAdded($name) {
        return $this->sliderObject->isScriptAdded($name);
    }

    public function addLess($file, $context) {
        $this->sliderObject->addLess($file, $context);
    }

    public function addCSS($css) {
        $this->sliderObject->addCSS($css);
    }

    public function addFont($font, $mode, $pre = null) {
        return $this->sliderObject->addFont($font, $mode, $pre);
    }

    public function addStyle($style, $mode, $pre = null) {
        return $this->sliderObject->addStyle($style, $mode, $pre);
    }

    public function addImage($imageUrl) {
        $this->sliderObject->addImage($imageUrl);
    }

    public function isAdmin() {
        return $this->sliderObject->isAdmin;
    }

    public function isLazyLoadingEnabled() {
        return $this->sliderObject->features->lazyLoad->isEnabled;
    }

    public function optimizeImage($image) {
        $image = $this->fill($image);

        $lazyLoad = $this->sliderObject->features->lazyLoad;

        $imagePath = N2ImageHelper::fixed($image, true);
        if (isset($imagePath[0]) && $imagePath[0] == '/' && $imagePath[1] != '/' && $lazyLoad->layerImageSizeBase64 && $lazyLoad->layerImageSizeBase64Size && filesize($imagePath) < $lazyLoad->layerImageSizeBase64Size) {
            $extension = pathinfo($image, PATHINFO_EXTENSION);
            if ($extension != 'svg') {
                return array(
                    'src' => N2Image::base64($imagePath, $image)
                );
            } else {
                return array(
                    'src' => N2ImageHelperAbstract::SVGToBase64($image)
                );
            }
        }

        $fixedImageUrl = N2ImageHelper::fixed($image);

        if (!$lazyLoad->layerImageOptimize || !$this->parameters->get('image-optimize', 1)) {
            $this->addImage($fixedImageUrl);

            return array(
                'src' => $fixedImageUrl
            );
        }

        $quality = intval($this->sliderObject->params->get('optimize-quality', 70));

        $tablet = N2Image::scaleImage('image', $image, $lazyLoad->layerImageTablet, false, $quality);
        $mobile = N2Image::scaleImage('image', $image, $lazyLoad->layerImageMobile, false, $quality);

        if ($image == $tablet && $image == $mobile) {
            $this->addImage($fixedImageUrl);

            return array(
                'src' => $fixedImageUrl
            );
        }

        return array(
            'src'          => N2Image::base64Transparent(),
            'data-desktop' => $fixedImageUrl,
            'data-tablet'  => N2ImageHelper::fixed($tablet),
            'data-mobile'  => N2ImageHelper::fixed($mobile),
            'data-device'  => '1'
        );
    }
}