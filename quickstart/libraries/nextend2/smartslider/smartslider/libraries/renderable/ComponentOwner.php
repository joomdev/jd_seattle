<?php

abstract class N2SmartSliderComponentOwnerAbstract {

    public $underEdit = false;

    /**
     * @var N2SmartSliderRenderableAbstract
     */
    protected $renderable;

    /** @var string Used for generators when multiple slides might contain the same unique class */
    public $unique = '';

    /**
     * @return N2SmartSliderRenderableAbstract
     */
    public function getRenderable() {
        return $this->renderable;
    }

    public abstract function getElementID();

    public function isComponentVisible($generatorVisibleVariable) {
        return true;
    }

    public function fill($value) {
        return $value;
    }

    public function fillLayers(&$layers) {
        for ($i = 0; $i < count($layers); $i++) {
            if (isset($layers[$i]['type'])) {
                switch ($layers[$i]['type']) {
                    case 'content':
                        N2SSSlideComponentContent::getFilled($this, $layers[$i]);
                        break;
                    case 'row':
                        N2SSSlideComponentRow::getFilled($this, $layers[$i]);
                        break;
                    case 'col':
                        N2SSSlideComponentCol::getFilled($this, $layers[$i]);
                        break;
                    case 'group':
                        N2SSSlideComponentGroup::getFilled($this, $layers[$i]);
                        break;
                    default:
                        N2SSSlideComponentLayer::getFilled($this, $layers[$i]);
                }
            } else {
                N2SSSlideComponentLayer::getFilled($this, $layers[$i]);
            }
        }
    }

    public function isLazyLoadingEnabled() {
        return false;
    }

    public function optimizeImage($image) {
        return array(
            'src' => N2ImageHelper::fixed($this->fill($image))
        );
    }

    public abstract function addScript($script, $name = false);

    public abstract function isScriptAdded($name);

    public abstract function addLess($file, $context);

    public abstract function addCSS($css);

    public abstract function addFont($font, $mode, $pre = null);

    public abstract function addStyle($style, $mode, $pre = null);

    public abstract function addImage($imageUrl);

    public abstract function isAdmin();
}