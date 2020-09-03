<?php

namespace Nextend\SmartSlider3\Application\Admin\Slides;


use Nextend\Framework\Asset\Js\Js;
use Nextend\Framework\Localization\Localization;
use Nextend\Framework\Platform\Platform;
use Nextend\Framework\Request\Request;
use Nextend\Framework\View\Html;
use Nextend\SmartSlider3\Application\Model\ModelLicense;
use Nextend\SmartSlider3\Settings;
use Nextend\SmartSlider3\SmartSlider3Info;
use Nextend\SmartSlider3Pro\LayerAnimation\LayerAnimationStorage;

/**
 * @var $this ViewSlidesEdit
 */


JS::addGlobalInline('document.documentElement.classList.add("n2_html--application-only");');

$externals = Settings::get('external-css-files');
if (!empty($externals)) {
    $externals = explode("\n", $externals);
    foreach ($externals AS $external) {
        echo "<link rel='stylesheet' href='" . $external . "' type='text/css' media='all' />";
    }
}


$slider = $this->frontendSlider;

$renderedSlider = $this->renderedSlider;
?>

    <form id="n2-ss-form-slide-edit" action="#" method="post">
        <?php
        $this->formManager->render();
        ?>
    </form>

    <div id='n2-ss-slide-canvas-container' class='n2_slide_editor_slider'>
        <?php echo Html::tag('div', array(
            'class' => "n2_slide_editor_slider__editor"
        ), Html::tag('div', array(
            'class' => "n2_slide_editor_slider__editor_inner"
        ), $renderedSlider)); ?>
    </div>

    <?php

$fillMode = $slider->params->get('backgroundMode', 'fill');
if ($fillMode == 'fixed') {
    $fillMode = 'fill';
}

$options = array(
    'isUploadDisabled'    => defined('N2_IMAGE_UPLOAD_DISABLE'),
    'slideBackgroundMode' => $fillMode,
    'settingsGoProUrl'    => SmartSlider3Info::getProUrlPricing(array(
        'utm_source'   => 'go-pro-button-editor-settings',
        'utm_medium'   => 'smartslider-' . Platform::getName() . '-' . SmartSlider3Info::$plan,
        'utm_campaign' => SmartSlider3Info::$campaign
    ))
);
if (!defined('N2_IMAGE_UPLOAD_DISABLE')) {
    $options['uploadUrl'] = $this->createAjaxUrl(array('browse/upload'));
    $options['uploadDir'] = 'slider' . $slider->sliderId;
}
$options['sectionLibraryUrl']      = 'https://smartslider3.com/slides/v2/free';
$options['sectionLibraryGoProUrl'] = SmartSlider3Info::getProUrlPricing(array(
    'utm_source'   => 'go-pro-button-section-library',
    'utm_medium'   => 'smartslider-' . Platform::getName() . '-' . SmartSlider3Info::$plan,
    'utm_campaign' => SmartSlider3Info::$campaign
));



JS::addInline('new N2Classes.SlideEdit(' . json_encode(array(
        'ajaxUrl'            => $this->getAjaxUrl(),
        'slideAsFile'        => intval(Settings::get('slide-as-file', 0)),
        'nextendAction'      => Request::$GET->getCmd('nextendaction'),
        'previewInNewWindow' => !!Settings::get('preview-new-window', 0),
        'previewUrl'         => $this->getUrlPreviewSlider($slider->data->get('id'), $this->getSlideID()),
        'sliderElementID'    => $slider->elementId,
        'slideEditorOptions' => $options
    )) . ');');
