<?php


namespace Nextend\SmartSlider3\Platform\Joomla;


use JAccessExceptionNotallowed;
use JEventDispatcher;
use JFactory;
use JPluginHelper;
use JText;
use JUri;
use Nextend\Framework\PageFlow;
use Nextend\SmartSlider3\Application\ApplicationSmartSlider3;
use Nextend\SmartSlider3\Install\Install;
use Nextend\SmartSlider3\Settings;
use Nextend\SmartSlider3\SmartSlider3Info;
use plgSystemSmartSlider3;

class AdministratorComponent {

    public function __construct() {

        $this->checkAcl();

        if (!isset($_GET['keepalive'])) {

            $this->loadSystemPlugins();

            if (Settings::get('n2_ss3_version') != SmartSlider3Info::$completeVersion) {

                Install::install();
            }

            $applicationType = ApplicationSmartSlider3::getInstance()
                                                      ->getApplicationTypeAdmin();

            $isAjax = isset($_GET['nextendajax']) && $_GET['nextendajax'];

            $applicationType->processRequest('sliders', 'gettingstarted', $isAjax);

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
            PageFlow::markApplicationEnd();
        }
    }

    protected function checkAcl() {

        if (!JFactory::getUser()
                     ->authorise('core.manage', 'com_smartslider3')) {
            throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    protected function loadSystemPlugins() {

        if (!class_exists('plgSystemSmartSlider3')) {

            $dispatcher = JEventDispatcher::getInstance();
            $plugin     = JPluginHelper::getPlugin('system', 'smartslider3');
            new plgSystemSmartSlider3($dispatcher, (array)($plugin));
        }
    }
}