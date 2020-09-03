<?php


namespace Nextend\SmartSlider3\Renderable;


use Nextend\Framework\ResourceTranslator\ResourceTranslator;
use Nextend\SmartSlider3\Renderable\Component\ComponentCol;
use Nextend\SmartSlider3\Renderable\Component\ComponentContent;
use Nextend\SmartSlider3\Renderable\Component\ComponentLayer;
use Nextend\SmartSlider3\Renderable\Component\ComponentRow;

abstract class AbstractRenderableOwner {

    public $underEdit = false;

    /**
     * @var AbstractRenderable
     */
    protected $renderable;

    /** @var string Used for generators when multiple slides might contain the same unique class */
    public $unique = '';

    /**
     * @return AbstractRenderable
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
                    case 'slide':
                        $this->fillLayers($layers[$i]['layers']);
                        break;
                    case 'content':
                        ComponentContent::getFilled($this, $layers[$i]);
                        break;
                    case 'row':
                        ComponentRow::getFilled($this, $layers[$i]);
                        break;
                    case 'col':
                        ComponentCol::getFilled($this, $layers[$i]);
                        break;
                    case 'group':
                        $this->fillLayers($layers[$i]['layers']);
                        break;
                    default:
                        ComponentLayer::getFilled($this, $layers[$i]);
                }
            } else {
                ComponentLayer::getFilled($this, $layers[$i]);
            }
        }
    }

    public function isLazyLoadingEnabled() {
        return false;
    }

    public function optimizeImage($image) {
        return array(
            'src' => ResourceTranslator::toUrl($this->fill($image))
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