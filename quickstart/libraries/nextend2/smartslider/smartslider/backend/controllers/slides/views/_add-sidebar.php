<div id="n2-ss-add-sidebar">
    <div class="n2-ss-add-sidebar-inner">
        <div class="n2-ss-addlayer">
            <a href="#" class="n2-ss-add-layer-button n2-button n2-button-icon n2-button-m n2-button-green n2-radius-s n2-uc n2-h4" data-n2tip="<?php n2_e('Add layer'); ?>"><i class="n2-i n2-i-addlayer2"></i></a>
            <div class="n2-ss-available-layers">
                <?php
                ob_start();
                echo N2Html::openTag('div', array('class' => 'n2-ss-available-layers-container'));

                N2Loader::import(array(
                    'models.css'
                ), 'system');
                $cssModel = new N2SystemCssModel();

                $itemDefaults = call_user_func(array(
                    'N2SmartSliderType' . $slider->data->get('type'),
                    'getItemDefaults'
                ));
                ?>
                <?php
                foreach (N2SmartSliderItemsFactory::getItemGroups() AS $groupLabel => $group) {
                    echo N2Html::tag('div', array('class' => 'n2-h5 n2-uc n2-ss-slide-item-group'), $groupLabel);
                    foreach ($group AS $type => $item) {
                        if ($item->isLegacy()) continue;
                        $visualKey = 'ss3item' . $type;
                        $visuals   = $cssModel->getVisuals($visualKey);
                        N2Pluggable::doAction($visualKey . 'Storage', array(
                            &$visuals
                        ));
                        N2JS::addInline('window["' . $visualKey . '"] = ' . json_encode($visuals) . ';');
                        echo N2Html::tag('div', array(
                            'class'                => 'n2-h5 n2-ss-core-item n2-ss-core-item-' . $type,
                            'data-layerproperties' => json_encode((object)array_merge($item->getLayerProperties(), $itemDefaults)),
                            'data-item'            => $type
                        ), N2Html::tag('div', array('class' => 'n2-ss-core-item-title'), $item->getTitle()));
                    }
                }

                echo N2Html::closeTag('div');
                $layersHTML = ob_get_clean();

                ob_start();
                require_once(dirname(__FILE__) . '/_structures.php');
                $structuresHTML = ob_get_clean();
                ?>
                <div id="n2-ss-layers-switcher">
                        <div class="n2-table n2-table-fixed n2-labels n2-sidebar-tab-switcher n2-tab-bordered n2-sidebar-tab-bg n2-has-underline">
                            <div class="n2-tr">
                                <div data-tab="layer" class="n2-td n2-h3 n2-uc n2-has-underline n2-active"><span class="n2-underline"><?php n2_e("Layer"); ?></span></div>
                                <div data-tab="structure" class="n2-td n2-h3 n2-uc n2-has-underline"><span class="n2-underline"><?php n2_e("Structure"); ?></span></div>
                            </div>
                        </div>
                        <div class="n2-tabs">
                            <div id="n2-ss-layers-switcher_0" style="display: block;" data-tab="layer">
                                <?php echo $layersHTML; ?>
                            </div>
                            <div id="n2-ss-layers-switcher_1" style="display: none;" data-tab="structure">
                                <?php echo $structuresHTML; ?>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                    N2R('documentReady', function ($) {
                        new N2Classes.NextendHeadingPane($('#n2-ss-layers-switcher'), $('#n2-ss-layers-switcher > .n2-labels .n2-td'), $('#n2-ss-layers-switcher > .n2-tabs > div'));
                    });
                </script>
            </div>
        </div>
        <a href="#" data-itemshortcut="heading" data-n2tip="<?php n2_e('Heading layer'); ?>" data-n2tipv="0" class="n2-button n2-button-icon n2-button-m n2-h4"><i class="n2-i n2-i-layer-heading"></i></a>
        <a href="#" data-itemshortcut="text" data-n2tip="<?php n2_e('Text layer'); ?>" data-n2tipv="0" class="n2-button n2-button-icon n2-button-m n2-h4"><i class="n2-i n2-i-layer-text"></i></a>
        <a href="#" data-itemshortcut="image" data-n2tip="<?php n2_e('Image layer'); ?>" data-n2tipv="0" class="n2-button n2-button-icon n2-button-m n2-h4"><i class="n2-i n2-i-layer-image"></i></a>
        <a href="#" data-itemshortcut="button" data-n2tip="<?php n2_e('Button layer'); ?>" data-n2tipv="0" class="n2-button n2-button-icon n2-button-m n2-h4"><i class="n2-i n2-i-layer-button"></i></a>
        <a href="#" data-structureshortcut="1col" data-n2tip="<?php n2_e('Structure'); ?>" data-n2tipv="0" class="n2-button n2-button-icon n2-button-m n2-button-blue n2-radius-s n2-uc n2-h4"><i class="n2-i n2-i-row"></i></a>

    </div>
</div>