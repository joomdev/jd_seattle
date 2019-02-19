<?php
N2Loader::import('libraries.renderable.layers.item.itemAbstract', 'smartslider');

class N2SmartSliderItemsFactory {

    public static $i = array();

    public $layerOwner;

    /** @var N2SSPluginItemFactoryAbstract[][] */
    private static $itemGroups = array();

    /**
     * @var N2SSPluginItemFactoryAbstract[]
     */
    private static $items = array();

    /**
     * @param N2SSPluginItemFactoryAbstract $item
     */
    public static function addItem($item) {
        self::$items[$item->getType()] = $item;
        require_once $item->getPath() . 'item.php';
    }

    private static function _load() {
        static $loaded;
        if (!$loaded) {

            self::sortItems();

            self::$itemGroups[n2_('Content')] = array();
            self::$itemGroups[n2_('Image')]   = array();
            self::$itemGroups[n2_('Media')]   = array();

            foreach (self::$items as $type => $item) {
                $group = $item->getGroup();
                if (!isset(self::$itemGroups[$group])) {
                    self::$itemGroups[$group] = array();
                }
                self::$itemGroups[$group][$type] = $item;
            }

            $loaded = true;
        }
    }

    /**
     * @return N2SSPluginItemFactoryAbstract[]
     */
    public static function getItems() {
        self::_load();

        return self::$items;
    }

    /**
     * @param $type
     *
     * @return N2SSPluginItemFactoryAbstract
     */
    public static function getItem($type) {
        self::_load();

        return self::$items[$type];
    }

    /**
     * @return N2SSPluginItemFactoryAbstract[][]
     */
    public static function getItemGroups() {
        self::_load();

        return self::$itemGroups;
    }

    /**
     * @param N2SSSlideComponentLayer $layer
     * @param array                   $itemData
     *
     * @return N2SSItemAbstract
     * @throws Exception
     */
    public static function create($layer, $itemData) {
        self::_load();
        if (!isset($itemData['type'])) {
            throw new Exception('Error with itemData: ' . $itemData);
        }

        $type = $itemData['type'];
        if (!isset(self::$items[$type])) {
            throw new Exception('Missing layer type: ' . $type . '. This layer type is only available in PRO version.');
        }

        /** @var N2SSPluginItemFactoryAbstract $factory */
        $factory = self::$items[$type];
        $class   = $factory->getClass();
        if (class_exists($class)) {

            $elementID = $layer->getOwner()
                               ->getElementID();
            self::$i[$elementID]++;
            $id = $elementID . 'item' . self::$i[$elementID];

            /**
             * @var $item N2SSItemAbstract
             */
            $item = new $class($factory, $id, $itemData['values'], $layer);

            return $item;
        }

        throw new Exception('Missing ' . $type . ' class:' . $class);
    }

    /**
     * @param $layerOwner  N2SmartSliderComponentOwnerAbstract
     */
    public function __construct($layerOwner) {
        self::_load();
        $this->layerOwner = $layerOwner;

        $elementID = $layerOwner->getElementID();
        if (!isset(self::$i[$elementID])) {
            self::$i[$elementID] = 0;
        }

    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $item
     */
    public static function getFilled($slide, &$item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->getFilled($slide, new N2Data($item['values']))
                                                 ->toArray();
        }
    }

    /**
     * @param N2SmartSliderExport      $export
     * @param                          $item
     */
    public static function prepareExport($export, $item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            self::$items[$type]->prepareExport($export, new N2Data($item['values']));
        }
    }

    /**
     * @param N2SmartSliderImport      $import
     * @param                          $item
     *
     * @return mixed
     */
    public static function prepareImport($import, $item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->prepareImport($import, new N2Data($item['values']))
                                                 ->toArray();
        }

        return $item;
    }

    public static function prepareSample($item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->prepareSample(new N2Data($item['values']))
                                                 ->toArray();
        }

        return $item;
    }

    private static function sortItems() {
        uasort(self::$items, array(
            'N2SmartSliderItemsFactory',
            'compareItems'
        ));
    }

    /**
     * @param N2SSPluginItemFactoryAbstract $a
     * @param N2SSPluginItemFactoryAbstract $b
     *
     * @return int
     */
    private static function compareItems($a, $b) {
        return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
    }
}
