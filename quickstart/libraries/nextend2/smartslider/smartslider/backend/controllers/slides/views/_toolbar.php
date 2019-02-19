<div id="n2-ss-toolbar">
    <div id="n2-ss-slide-canvas-settings"><a href="#" class="n2-button n2-button-icon n2-button-m n2-radius-s n2-button-grey n2-uc n2-h4" data-n2tip="<?php n2_e('Canvas settings'); ?>"><i class="n2-i n2-it n2-i-16 n2-i-settings"></i></a><div class="n2-ss-settings-panel"><div class="n2-ss-settings-panel-inner"></div></div></div>
    <?php
    ?>
    <div>
        <div data-placement="absolute" id="n2-ss-layer-horizontal-align"
             class="n2-ss-tool n2-form-element-radio-tab n2-form-element-icon-radio">
            <div class="n2-radio-option n2-first" data-align="left"
                 data-n2tip="<?php n2_e('Horizontal align - Left'); ?>"><i
                        class="n2-i n2-it n2-i-horizontal-left"></i></div>

            <div class="n2-radio-option" data-align="center"
                 data-n2tip="<?php n2_e('Horizontal align - Center'); ?>"><i
                        class="n2-i n2-it n2-i-horizontal-center"></i>
            </div>

            <div class="n2-radio-option n2-last" data-align="right"
                 data-n2tip="<?php n2_e('Horizontal align - Right'); ?>"><i
                        class="n2-i n2-it n2-i-horizontal-right"></i></div>
        </div>

        <div data-placement="absolute" id="n2-ss-layer-vertical-align" class="n2-ss-tool n2-form-element-radio-tab n2-form-element-icon-radio">
            <div
                    class="n2-radio-option n2-first" data-align="top"
                    data-n2tip="<?php n2_e('Vertical align - Top'); ?>"><i
                        class="n2-i n2-it n2-i-vertical-top"></i></div>

            <div class="n2-radio-option" data-align="middle"
                 data-n2tip="<?php n2_e('Vertical align - Middle'); ?>"><i
                        class="n2-i n2-it n2-i-vertical-middle"></i>
            </div>

            <div class="n2-radio-option n2-last" data-align="bottom"
                 data-n2tip="<?php n2_e('Vertical align - Bottom'); ?>"><i
                        class="n2-i n2-it n2-i-vertical-bottom"></i></div></div>

        <div data-placement="all" id="n2-ss-layer-show-on" class="n2-ss-tool n2-form-element-radio-tab n2-form-element-icon-radio" data-n2tip="<?php n2_e('Show layer on selected devices'); ?>"></div>

        <div data-placement="all" id="n2-ss-layer-adaptive-font" class="n2-ss-tool n2-button n2-button-icon n2-button-grey n2-button-s n2-radius-s n2-expert"
             data-n2tip="<?php n2_e('Adaptive (Off: auto-scaled, On: fixed) font size'); ?>"><i
                    class="n2-i n2-it n2-i-16 n2-i-adaptive"></i></div>

        <div data-placement="all" data-n2tip="<?php n2_e('Font size scaling - enlarge or shrink text on each device'); ?>"
             class="n2-ss-tool n2-form-element-text n2-form-element-autocomplete n2-form-element-number n2-text-has-unit n2-border-radius">
        <div class="n2-text-sub-label n2-h5 n2-uc"><i
                    class="n2-i n2-it n2-i-16 n2-i-fontmodifier"></i></div>
        <input type="text" autocomplete="off" style="width:32px"
               class="n2-h5 ui-autocomplete-input" value="100" name="n2-ss-layer-font-size"
               id="n2-ss-layer-font-size">

        <div class="n2-text-unit n2-h5 n2-uc">%</div></div>

        <div id="n2-ss-layer-reset-to-desktop" class="n2-ss-tool n2-button n2-button-icon n2-button-s n2-button-grey n2-radius-s"
             data-n2tip="<?php n2_e('Clear device specific layer settings'); ?>"><i
                    class="n2-i n2-it n2-i-16 n2-i-reset"></i></div>

        <a data-placement="absolute" href="https://www.youtube.com/watch?v=yGpVsrzwt1U&index=4&list=PLSawiBnEUNfvzcI3pBHs4iKcbtMCQU0dB " class="n2-ss-tool n2-button n2-button-normal n2-button-s n2-button-grey n2-radius-s" target="_blank">
            <?php n2_e("Responsive tricks"); ?>
        </a>

        <a data-placement="normal" href="https://smartslider3.helpscoutdocs.com/article/1430-tutorial-video-content-mode" class="n2-ss-tool n2-button n2-button-normal n2-button-s n2-button-grey n2-radius-s" target="_blank">
            <?php n2_e("Responsive tips"); ?>
        </a>

    </div>

    <div class="n2-ss-editor-group-mode-only">

        <div id="n2-ss-layer-show-on" class="n2-ss-tool n2-form-element-radio-tab n2-form-element-icon-radio" data-n2tip="<?php n2_e('Show layer on selected devices'); ?>"></div>

        <div id="n2-ss-group-adaptive-font" class="n2-ss-tool n2-button n2-button-icon n2-button-grey n2-button-s n2-radius-s n2-expert"
             data-n2tip="<?php n2_e('Adaptive (Off: auto-scaled, On: fixed) font size'); ?>"><i
                    class="n2-i n2-it n2-i-16 n2-i-adaptive"></i></div>

        <div data-n2tip="<?php n2_e('Font size scaling - enlarge or shrink text on each device'); ?>"
             class="n2-ss-tool n2-form-element-text n2-form-element-autocomplete n2-form-element-number n2-text-has-unit n2-border-radius">
        <div class="n2-text-sub-label n2-h5 n2-uc"><i
                    class="n2-i n2-it n2-i-16 n2-i-fontmodifier"></i></div>
        <input type="text" autocomplete="off" style="width:32px"
               class="n2-h5 ui-autocomplete-input" value="100" name="n2-ss-group-font-size"
               id="n2-ss-group-font-size">

        <div class="n2-text-unit n2-h5 n2-uc">%</div></div>
    </div>
    <div>
        <div id="n2-ss-editor-mode" class="n2-ss-tool n2-form-element-radio-tab">
            <div class="n2-radio-option n2-h4 n2-uc n2-first" data-mode="content"><i class="n2-i n2-i-builder"></i> <?php n2_e("Content"); ?></div><div class="n2-radio-option n2-h4 n2-uc n2-last n2-active" data-mode="canvas"><i class="n2-i n2-i-canvas"></i> <?php n2_e("Canvas"); ?></div>
        </div>
    </div>
</div>
