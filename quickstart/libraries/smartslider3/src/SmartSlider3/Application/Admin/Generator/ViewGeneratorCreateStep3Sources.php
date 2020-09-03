<?php


namespace Nextend\SmartSlider3\Application\Admin\Generator;


use Nextend\Framework\Sanitize;
use Nextend\Framework\View\AbstractView;
use Nextend\SmartSlider3\Application\Admin\Layout\Block\Core\Header\BlockHeader;
use Nextend\SmartSlider3\Application\Admin\Layout\LayoutDefault;
use Nextend\SmartSlider3\Application\Admin\TraitAdminUrl;
use Nextend\SmartSlider3\Generator\AbstractGeneratorGroup;

class ViewGeneratorCreateStep3Sources extends AbstractView {

    use TraitAdminUrl;

    protected $groupID = 0;

    protected $groupTitle = '';

    /** @var array */
    protected $slider;

    /** @var AbstractGeneratorGroup */
    protected $generatorGroup;

    public function display() {

        $this->layout = new LayoutDefault($this);

        if ($this->groupID) {
            $this->layout->addBreadcrumb(Sanitize::esc_html($this->groupTitle), 'ssi_16 ssi_16--folderclosed', $this->getUrlSliderEdit($this->groupID));
        }

        $this->layout->addBreadcrumb(Sanitize::esc_html($this->slider['title']), 'ssi_16 ssi_16--image', $this->getUrlSliderEdit($this->slider['id'], $this->groupID));

        $this->layout->addBreadcrumb(n2_('Add dynamic slides'), '', $this->getUrlGeneratorCreate($this->slider['id'], $this->groupID));

        $this->layout->addBreadcrumb($this->generatorGroup->getLabel(), '');


        $blockHeader = new BlockHeader($this);
        $blockHeader->setHeading(n2_('Add dynamic slides') . ': ' . $this->generatorGroup->getLabel());

        $this->layout->addContentBlock($blockHeader);

        $this->layout->addContent($this->render('CreateStep3Sources'));

        $this->layout->render();
    }

    /**
     * @param int    $groupID
     * @param string $groupTitle
     */
    public function setGroupData($groupID, $groupTitle) {
        $this->groupID    = $groupID;
        $this->groupTitle = $groupTitle;
    }

    /**
     * @return array
     */
    public function getSlider() {
        return $this->slider;
    }

    /**
     * @param array $slider
     */
    public function setSlider($slider) {
        $this->slider = $slider;
    }

    /**
     * @return int
     */
    public function getSliderID() {
        return $this->slider['id'];
    }

    /**
     * @return AbstractGeneratorGroup
     */
    public function getGeneratorGroup() {
        return $this->generatorGroup;
    }

    /**
     * @param AbstractGeneratorGroup $generatorGroup
     */
    public function setGeneratorGroup($generatorGroup) {
        $this->generatorGroup = $generatorGroup;
    }


}