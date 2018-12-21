<?php
jimport('joomla.plugin.plugin');

class plgSystemNextend2 extends JPlugin {

    public static $regexpURL = '';

    /*
    Artisteer jQuery fix
    */
    function onAfterDispatch() {
        if (class_exists('Artx', true)) {
            Artx::load("Artx_Page");
            if (isset(ArtxPage::$inlineScripts)) {
                ArtxPage::$inlineScripts[] = '<script type="text/javascript">if(typeof jQuery != "undefined") window.artxJQuery = jQuery;</script>';
            }
        }
    }

    function onInitN2Library() {
        N2Base::registerApplication(JPATH_SITE . DIRECTORY_SEPARATOR . "libraries" . DIRECTORY_SEPARATOR . 'nextend2/nextend/library/applications/system/N2SystemApplicationInfo.php');
    }

    function onAfterRender() {

        require_once dirname(__FILE__) . '/joomla3.php';

        $joomla3Assets = new SystemNextend2Joomla3Assets();

        if (class_exists('JEventDispatcher', false)) {
            $dispatcher = JEventDispatcher::getInstance();
            $dispatcher->trigger('onNextendBeforeCompileHead');
        } else {
            JFactory::getApplication()
                    ->triggerEvent('onNextendBeforeCompileHead');
        }

        $joomla3Assets->process();


        ob_start();
        if (class_exists('N2AssetsManager')) {
            echo N2AssetsManager::getCSS();
            echo N2AssetsManager::getJs();
        }
        $head = ob_get_clean();
        if (!empty($head)) {

            $application = JFactory::getApplication();
            $body        = $application->getBody();

            $parts = preg_split('/<\/head>/', $body, 2);

            $body = implode($head . $joomla3Assets->renderHead() . '</head>', $parts);

            $application->setBody($body);
        }
    }
}