<?php
if (!version_compare(PHP_VERSION, '5.6.20', '>=')) {
    JError::raiseWarning(500, 'Smart Slider 3 requires PHP version 5.6.20+, extension is currently NOT RUNNING.');
} else if (!version_compare(JVersion::RELEASE, '3.7', '>=')) {
    JError::raiseWarning(500, 'Smart Slider 3 requires Joomla version 3.7+. Because you are using an earlier version, the plugin is currently NOT RUNNING.');
} else {
    if (JFactory::getUser()
                ->authorise('core.manage', 'com_smartslider3')) {
        if (!isset($_GET['keepalive'])) {
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
            $smartSliderBackend = N2Base::getApplication("smartslider")
                                        ->getApplicationType('backend')
                                        ->setCurrent();

            if (N2Settings::get('n2_ss3_version') != N2SS3::$completeVersion) {
                $smartSliderBackend->render(array(
                    "controller" => "install",
                    "action"     => "index",
                    "useRequest" => false
                ), array(true));
            }

            $smartSliderBackend->render(array(
                "controller" => "sliders",
                "action"     => "index"
            ));
            ?>
            <script>
            N2R('$', function ($) {
                var __keepAlive = function () {
                    $.get('<?php echo JURI::current();?>?option=com_smartslider3&keepalive=1', function () {
                        setTimeout(__keepAlive, 300000);
                    });
                };
                setTimeout(__keepAlive, 300000);
            });
        </script>
            <?php
            n2_exit();
        }
    } else {
        return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    }
}