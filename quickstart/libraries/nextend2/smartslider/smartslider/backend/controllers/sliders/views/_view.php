<?php


class N2SmartsliderBackendSlidersView extends N2ViewBase {

    public function getDashboardButtons() {

        $app        = N2Base::getApplication('smartslider');
        $accessEdit = N2Acl::canDo('smartslider_edit', $app->info);

        $buttons = '';
        if ($accessEdit) {

            $buttons .= N2Html::tag('a', array(
                'data-label' => n2_('Import slider'),
                'href'       => $app->router->createUrl(array('sliders/import')),
                'id'         => 'n2-ss-import-slider'
            ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-import')));

        }
        $updateModel = N2SmartsliderUpdateModel::getInstance();
        $hasUpdate   = $updateModel->hasUpdate();
        $this->appType->router->setMultiSite();
        $updateUrl = $this->appType->router->createUrl(array(
            'update/update',
            N2Form::tokenizeUrl() + array('download' => 1)
        ));
        $this->appType->router->unSetMultiSite();


        $buttons .= N2Html::tag('a', array(
            'data-label' => n2_('Check for updates'),
            'href'       => $app->router->createUrl(array(
                'update/check',
                N2Form::tokenizeUrl()
            )),
            'id'         => 'n2-ss-check-update',
        ), N2Html::tag('i', array('class' => 'n2-i n2-i-a-refresh')));


        if ($hasUpdate) {
            ?>
            <script type="text/javascript">
                    N2R('documentReady', function ($) {
                        $('.n2-main-top-bar').prepend('<div class="n2-left n2-top-bar-menu"><span><?php printf(n2_('Version %s available!'), $updateModel->getVersion()); ?></span> <a style="font-size: 12px;margin-right: 10px;" class="n2-h3 n2-uc n2-button n2-button-normal n2-button-blue n2-button-m n2-radius-s" href="<?php echo $updateUrl; ?>"><?php n2_e('Update'); ?></a> <a style="font-size: 12px;" class="n2-h3 n2-uc n2-button n2-button-normal n2-button-grey n2-button-m n2-radius-s" href="#" onclick="N2Classes.NextendModal.documentation(\'<?php n2_e('Changelog'); ?>\', \'https://smartslider3.helpscoutdocs.com/article/432-changelog?utm_campaign=<?php echo N2SS3::$campaign; ?>&utm_source=changelog&utm_medium=smartslider-<?php echo N2Platform::getPlatform(); ?>-<?php echo N2SS3::$plan ?>\');return false;"><?php n2_e('Changelog'); ?></a></div>');
                    });
                </script>
            <?php
        }
    


        return $buttons;
    }
} 