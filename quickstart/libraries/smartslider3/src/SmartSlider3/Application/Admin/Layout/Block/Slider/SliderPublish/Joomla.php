<?php
/**
 * @required N2JOOMLA
 */

namespace Nextend\SmartSlider3\Application\Admin\Layout\Block\Slider\SliderPublish;

/**
 * @var $this BlockPublishSlider
 */

$sliderID    = $this->getSliderID();
$publishData = new JoomlaPublishSlider($sliderID);
$modules     = $publishData->getModuleList();

?>

<div class="n2_ss_slider_publish">

    <div class="n2_ss_slider_publish__option">
        <div class="n2_ss_slider_publish__option_label"><?php n2_e('Module'); ?></div>

        <div class="n2_ss_slider_publish__option_description"><?php n2_e('Displays the slider in a template module position.'); ?></div>

        <a class="n2_button n2_button--big n2_button--green" href="<?php echo $publishData->getCreateModuleLink(); ?>" target="_blank"><span class="n2_button__label"><?php n2_e('Create module') ?></span></a>
    </div>

    <?php if (!empty($modules)): ?>
        <div class="n2_ss_slider_publish__option">
            <div class="n2_ss_slider_publish__option_label"><?php n2_e('Related modules'); ?></div>
            <div class="n2_ss_slider_publish__related_modules">
                <?php foreach ($modules AS $module): ?>
                    <a class="n2_button n2_button--small n2_button--grey" href="<?php echo $module['url']; ?>" target="_blank"><?php echo $module['label']; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="n2_ss_slider_publish__option">
        <div class="n2_ss_slider_publish__option_label"><?php n2_e('Articles'); ?></div>

        <div class="n2_ss_slider_publish__option_description"><?php n2_e('Paste the code into article:'); ?></div>

        <div class="n2_ss_slider_publish__option_code" dir="ltr">
            smartslider3[<?php echo $sliderID; ?>]
        </div>
    </div>

    <div class="n2_ss_slider_publish__option">
        <div class="n2_ss_slider_publish__option_label"><?php n2_e('PHP code'); ?></div>

        <div class="n2_ss_slider_publish__option_description"><?php n2_e('Paste the PHP code into source code:'); ?></div>

        <div class="n2_ss_slider_publish__option_code" dir="ltr">
            &lt;?php <br/>echo nextend_smartslider3(<?php echo $sliderID; ?>);<br/>?&gt;
        </div>
    </div>
</div>