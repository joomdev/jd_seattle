<?php
$actions = array(
    N2Html::tag('a', array(
        'id'    => 'n2-ss-undo',
        'href'  => '#',
        'class' => 'n2-h3 n2-button n2-button-grey n2-button-l n2-button-icon n2-radius-s',
        'style' => 'font-size: 12px;'
    ), '<i class="n2-i n2-it n2-i-16 n2-i-undo"></i>'),
    N2Html::tag('a', array(
        'id'    => 'n2-ss-redo',
        'href'  => '#',
        'class' => 'n2-h3 n2-button n2-button-grey n2-button-l n2-button-icon n2-radius-s',
        'style' => 'font-size: 12px;'
    ), '<i class="n2-i n2-it n2-i-16 n2-i-redo"></i>'),
    N2Html::tag('a', array(
        'href'    => $this->appType->router->createUrl(array(
            "slider/edit",
            array(
                "sliderid" => $sliderId
            )
        )),
        'class'   => 'n2-button n2-button-normal n2-button-red n2-button-l n2-h4 n2-b n2-uc n2-radius-s',
        'onclick' => 'return nextend.cancel(this.href);'
    ), n2_('Cancel'))
);

if ($slide && $slide['generator_id'] > 0) {
    $actions[] = N2Html::tag('a', array(
        'href'    => '#',
        'class'   => 'n2-button n2-button-normal n2-button-l n2-radius-s n2-button-blue n2-h4 n2-b n2-uc',
        'onclick' => 'nextend.askToSave = false;setTimeout(function() {var static = N2Classes.$("<input name=\'static\' value=\'1\' />"); N2Classes.$(\'#smartslider-form\').append(static).submit(); static.remove();}, 300); return false;'
    ), n2_('Static save'));
}

$actions[] = N2Html::tag('a', array(
    'href'    => '#',
    'class'   => 'n2-button n2-button-normal n2-button-green n2-button-l n2-h4 n2-b n2-uc n2-radius-s',
    'onclick' => 'return N2Classes.Form.submit("#smartslider-form");'
), n2_('Save'));

N2Html::topBar(array(
    "actions"     => $actions,
    'back'        => N2Html::tag('a', array(
            'class' => 'n2-ss-back-slider n2-h4 n2-uc',
            'href'  => $this->appType->router->createUrl(array(
                "slider/edit",
                array(
                    "sliderid" => $sliderId
                )
            ))
        ), n2_('Slider')) . N2Html::tag('a', array(
            'class'   => 'n2-ss-back-slides n2-h4 n2-uc',
            'onclick' => 'N2Classes.$("html").toggleClass("n2-ss-show-slides");N2Classes.$("html, body").scrollTop(0);return false;',
            'href'    => $this->appType->router->createUrl(array(
                "slider/edit",
                array(
                    "sliderid" => $sliderId
                )
            ))
        ), n2_('Slides')) . N2Html::tag('a', array(
            'id'    => 'n2-ss-preview',
            'href'  => $this->appType->router->createUrl(array(
                "preview/index",
                array('sliderid' => $sliderId) + N2Form::tokenizeUrl()
            )),
            'class' => 'n2-h4 n2-uc'
        ), n2_('Preview')),
    'middle'      => '<div class="n2-ss-device-zoomer">
                    <div id="n2-ss-devices" class="n2-ss-devices-compact">
                        <div class="n2-controls-panel n2-table n2-table-auto">
                            <div class="n2-tr">
                            </div>
                        </div>
                    </div>
                    <div id="n2-ss-zoom">
                        <div class="n2-ss-slider-zoom-container">
                            <i class="n2-i n2-i-minus"></i>
                            <i class="n2-i n2-i-plus"></i>

                            <div class="n2-ss-slider-zoom-bg"></div>

                            <div class="n2-ss-slider-zoom-1"></div>

                            <div id="n2-ss-slider-zoom"></div>
                        </div>
                    </div>

                    </div>',
    "hideSidebar" => true
));
?>

<script type="text/javascript">
    nextend.isPreview = false;
    N2R('documentReady', function ($) {

        var form = $('#smartslider-form'),
            formAction = form.attr('action');
        var newWindow = <?php echo intval(N2SmartSliderSettings::get('preview-new-window', 0)); ?>;

        if (!newWindow) {
            var modal = new N2Classes.NextendSimpleModal('<iframe name="n2-tab-preview" src="" style="width: 100%;height:100%;"></iframe>', {
                "class": 'n2-ss-preview-modal'
            });
            modal.modal.on('ModalHide', function () {
                modal.modal.find('iframe').attr('src', 'about:blank');
                $(window).trigger('SSPreviewHide');
            });
        }

        $('#n2-ss-preview').on('click', function (e) {
            nextend.isPreview = true;
            e.preventDefault();
            nextend.currentEditor.prepareForm();
            if (!newWindow) {
                modal.show();
            } else {
                N2Classes.NextendModal.newFullWindow('', 'n2-tab-preview');
            }
            //var currentRequest = form.serialize();
            form.attr({
                action: '<?php echo $this->appType->router->createUrl(array(
                    "preview/slide",
                    N2Form::tokenizeUrl() + array(
                        'slideId'  => $slide ? $slide['id'] : 0,
                        'sliderId' => $sliderId
                    )
                ))?>',
                target: 'n2-tab-preview'
            }).submit().attr({
                action: formAction,
                target: null
            });
            nextend.isPreview = false;
        });

        <?php
        if (N2Get::getCmd('nextendaction') == 'create') {
        ?>
        $('.n2-ss-tab-background').trigger('click');
        <?php
        }
        ?>
    });
</script>