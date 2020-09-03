<?php


namespace Nextend\SmartSlider3\Application\Helper;


use Nextend\Framework\Data\Data;
use Nextend\Framework\Model\ApplicationSection;
use Nextend\Framework\Model\StorageSectionManager;
use Nextend\Framework\Pattern\MVCHelperTrait;
use Nextend\SmartSlider3\Application\Model\ModelSliders;
use Nextend\SmartSlider3\Application\Model\ModelSlidersXRef;
use WP_Post;

class HelperSliderChanged {

    use MVCHelperTrait;

    /** @var ApplicationSection */
    protected $storage;

    /**
     * HelperSliderChanged constructor.
     *
     * @param MVCHelperTrait $MVCHelper
     */
    public function __construct($MVCHelper) {

        $this->setMVCHelper($MVCHelper);

        $this->storage = StorageSectionManager::getStorage('smartslider');
    }


    public function isSliderChanged($sliderId, $value = 1) {
        return intval($this->storage->get('sliderChanged', $sliderId, $value));
    }

    public function setSliderChanged($sliderId, $value = 1) {
        $this->storage->set('sliderChanged', $sliderId, $value);
        $changedSliders = array($sliderId);

        $xref = new ModelSlidersXRef($this);
        foreach ($xref->getGroups($sliderId) AS $row) {
            if ($row['group_id'] > 0) {
                $this->storage->set('sliderChanged', $row['group_id'], $value);
                $changedSliders[] = $row['group_id'];
            }
        }
    }
}