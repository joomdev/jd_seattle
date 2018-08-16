<?php
/** @var N2SmartSliderRenderableAbstract $renderable */

N2Loader::import("models.Layers", "smartslider");
?>

<div id="n2-ss-layer-window" class="n2-form-dark">
    <div class="n2-ss-layer-window-crop">
        <div class="n2-ss-layer-window-title">

            <div class="n2-ss-layer-window-title-nav n2-ss-layer-window-title-nav-left">
            </div>

            <div class="n2-ss-layer-window-title-inner"></div>

            <div class="n2-ss-layer-window-title-nav n2-ss-layer-window-title-nav-right">
            </div>
        </div>
        <?php
        $layerModel = new N2SmartsliderLayersModel($renderable);
        $layerModel->renderForm();
        ?>
    </div>
    <div class="n2-ss-layer-window-actions">
        <a href="#" class="n2-ss-slide-show-layers n2-button n2-button-icon n2-button-m" data-n2tip="<?php n2_e('Layer list'); ?>">
            <i class="n2-i n2-i-layerlist"></i>
        </a>
        <a href="#" class="n2-ss-slide-duplicate-layer n2-button n2-button-icon n2-button-m">
            <i class="n2-i n2-i-duplicate"></i>
        </a>
        <a href="#" class="n2-ss-slide-delete-layer n2-button n2-button-icon n2-button-m">
            <i class="n2-i n2-i-delete"></i>
        </a>
    </div>
</div>