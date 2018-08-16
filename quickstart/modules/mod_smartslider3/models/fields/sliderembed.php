<?php
jimport('joomla.form.formfield');

class JFormFieldSliderEmbed extends JFormField {

    protected $type = 'SliderEmbed';

    public function getInput() {

        if (!class_exists('plgSystemNextendSmartslider3')) {
            require_once(JPATH_PLUGINS . '/system/nextendsmartslider3/nextendsmartslider3.php');
            if (class_exists('JEventDispatcher', false)) {
                $dispatcher = JEventDispatcher::getInstance();
            } else {
                $dispatcher = JDispatcher::getInstance();
            }
            $plugin = JPluginHelper::getPlugin('system', 'nextendsmartslider3');
            new plgSystemNextendSmartslider3($dispatcher, (array)($plugin));
        }

        jimport("nextend2.nextend.joomla.library");

        N2Loader::import('libraries.settings.settings', 'smartslider');
        ob_start();

        $router = N2Base::getApplication('smartslider')->router;

        ?>
        <iframe id="n2-ss-slider-selector-frame" style="width:100%; height: 510px; border: 0;" src="<?php echo $router->createUrl(array('sliders/embed')); ?>"></iframe>
        <script type="text/javascript">
				jQuery(document).ready(function ($) {
                    var iframe = $('#n2-ss-slider-selector-frame');

                    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
                    window[eventMethod](eventMethod == "attachEvent" ? "onmessage" : "message", function (e) {
                        if (e.source == (iframe[0].contentWindow || iframe[0].contentDocument)) {
                            var message = e[e.message ? "message" : "data"];
                            try{
                                message = JSON.parse(message);
                                if(message.action && message.action === 'ss3embed') {
                                    jQuery('#jform_params_slider').val(message.value).trigger('change').trigger("liszt:updated").trigger('chosen:updated');
                                }
                            } catch(ex) {
                            
                            }
                        }
                    }, false);
                });
			</script>
        <?php
        return ob_get_clean();
    }
}