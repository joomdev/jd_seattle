<?php

class N2SSSlideComponentLayer extends N2SSSlideComponent {

    protected $type = 'layer';

    /** @var N2SSItemAbstract */
    private $item;

    public function __construct($index, $owner, $group, $data, $placementType) {


        $this->item = new N2SmartSliderItemsFactory($owner);
        parent::__construct($index, $owner, $group, $data, $placementType);


        $this->attributes['style'] = '';

        $item = $this->data->get('item');

        $this->item = N2SmartSliderItemsFactory::create($this, $item);

        $this->placement->attributes($this->attributes);
    }

    public function render($isAdmin) {
        if ($this->isRenderAllowed()) {
            if ($isAdmin) {
                $this->admin();
            }
            $this->prepareHTML();

            $item = $this->data->get('item');
            if (empty($item)) {
                $items = $this->data->get('items');
                $item  = $items[0];
            }


            if ($isAdmin) {
                $renderedItem = $this->item->renderAdmin();
            } else {
                $renderedItem = $this->item->render();
            }

            if ($renderedItem === false) {
                return '';
            }

            if ($this->item->needSize()) {
                $this->attributes['class'] .= ' n2-ss-layer-needsize';
            }

            $html = $this->renderPlugins($renderedItem);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {
        N2SSSlideComponent::getFilled($slide, $layer);

        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }
        N2SmartSliderItemsFactory::getFilled($slide, $layer['item']);
    }

    public static function prepareExport($export, $layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        N2SmartSliderItemsFactory::prepareExport($export, $layer['item']);

    }

    public static function prepareImport($import, &$layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        $layer['item'] = N2SmartSliderItemsFactory::prepareImport($import, $layer['item']);
    }

    public static function prepareSample(&$layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        $layer['item'] = N2SmartSliderItemsFactory::prepareSample($layer['item']);
    }
}