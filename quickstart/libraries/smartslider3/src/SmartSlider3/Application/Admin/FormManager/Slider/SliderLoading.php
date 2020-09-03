<?php


namespace Nextend\SmartSlider3\Application\Admin\FormManager\Slider;


use Nextend\Framework\Form\Container\ContainerTable;
use Nextend\Framework\Form\Element\Message\Warning;
use Nextend\Framework\Form\Element\OnOff;
use Nextend\Framework\Form\Element\Radio\ImageListFromFolder;
use Nextend\Framework\Form\Element\Select;
use Nextend\Framework\Form\Element\Text\Color;
use Nextend\Framework\Form\Element\Text\FieldImage;
use Nextend\Framework\Form\Element\Text\Number;
use Nextend\Framework\Form\Element\Text\NumberAutoComplete;
use Nextend\Framework\Form\FormTabbed;
use Nextend\Framework\ResourceTranslator\ResourceTranslator;

class SliderLoading extends AbstractSliderTab {

    /**
     * SliderLoading constructor.
     *
     * @param FormTabbed $form
     */
    public function __construct($form) {
        parent::__construct($form);

        $this->loading();
    }

    /**
     * @return string
     */
    protected function getName() {
        return 'loading';
    }

    /**
     * @return string
     */
    protected function getLabel() {
        return n2_('Loading');
    }

    protected function loading() {

        $table = new ContainerTable($this->tab, 'loading', n2_('Loading'));

        $row0 = $table->createRow('loading-0');

        new Select($row0, 'loading-type', 'Loading type', '', array(
            'options'            => array(
                ''            => n2_('Instant'),
                'afterOnLoad' => n2_('After page loaded'),
                'afterDelay'  => n2_('After delay')
            ),
            'relatedValueFields' => array(
                array(
                    'values' => array(
                        'afterDelay'
                    ),
                    'field'  => array(
                        'sliderdelay'
                    )
                )
            ),
        ));

        new Number($row0, 'delay', n2_('Load delay'), 0, array(
            'wide' => 5,
            'unit' => 'ms'
        ));

        $row1 = $table->createRow('loading-1');

        new OnOff($row1, 'playWhenVisible', n2_('Play when visible'), 1, array(
            'relatedFieldsOn' => array(
                'sliderplayWhenVisibleAt'
            ),
            'tipLabel'        => n2_('Play when visible'),
            'tipDescription'  => n2_('Makes sure that the autoplay and layer animations only start when your slider is visible.')
        ));
        new Number($row1, 'playWhenVisibleAt', n2_('At'), 50, array(
            'unit' => '%',
            'wide' => 3
        ));

        $row2 = $table->createRow('loading-2');

        new OnOff($row2, 'is-delayed', n2_('Delayed (for lightbox/tabs)'), 0);
    }

    protected function loadingAnimation() {
    }
}