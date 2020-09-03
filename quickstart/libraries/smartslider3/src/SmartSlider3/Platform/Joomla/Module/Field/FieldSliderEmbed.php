<?php


namespace Nextend\SmartSlider3\Platform\Joomla\Module\Field;

use JEventDispatcher;
use JFormField;
use JPluginHelper;
use plgSystemSmartSlider3;

jimport('joomla.form.formfield');

class FieldSliderEmbed extends JFormField {

    protected $type = 'SliderEmbed';

    public function getInput() {

        $this->loadSystemPlugins();

        ob_start();
        ?>
        <script type="text/javascript">
            N2Classes.SelectSlider(n2_('Select A Slider'), function (id, alias) {
                jQuery('#jform_params_slider').val(alias || id).trigger('change').trigger("liszt:updated").trigger('chosen:updated');
            });
        </script>
        <?php
        return ob_get_clean();
    }

    protected function loadSystemPlugins() {

        if (!class_exists('plgSystemSmartSlider3')) {
            $dispatcher = JEventDispatcher::getInstance();
            $plugin     = JPluginHelper::getPlugin('system', 'nextendsmartslider3');
            new plgSystemSmartSlider3($dispatcher, (array)($plugin));
        }
    }
}