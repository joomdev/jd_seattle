<?php


namespace Nextend\SmartSlider3\Platform\Joomla\Plugin;

use Artx;
use ArtxPage;
use EshopHelper;
use JEventDispatcher;
use JFactory;
use JPlugin;
use Nextend\Framework\Asset\AssetManager;
use Nextend\SmartSlider3\Platform\Joomla\Joomla3Assets;

jimport('joomla.plugin.plugin');

/**
 * Class PluginSmartSlider3
 *
 * Used in Joomla -> Plugin -> System -> Nextend2
 */
class PluginSmartSlider3 extends JPlugin {

    /*
    Artisteer jQuery fix
    */
    public function onAfterDispatch() {
        if (class_exists('Artx', true)) {
            Artx::load("Artx_Page");
            if (isset(ArtxPage::$inlineScripts)) {
                ArtxPage::$inlineScripts[] = '<script type="text/javascript">if(typeof jQuery != "undefined") window.artxJQuery = jQuery;</script>';
            }
        }
    }

    private function onNextendBeforeCompileHead() {
        $application = JFactory::getApplication();
        if ($application->isSite()) {
            $request = $application->input->request;
            if (!JFactory::getUser()->guest && (($application->get('frontediting', 1) && $request->get('option') == 'com_content' && $request->get('view') == 'form' && $request->get('layout') == 'edit' && $application->input->getInt('a_id') > 0) || ($request->get('option') == 'com_quix' && ($request->get('layout') == 'edit') || $request->get('builder') == 'frontend')) || $request->get('type') == 'rss') {
                return;
            }

            $body = $application->getBody();

            // Simple performance check to determine whether bot should process further
            if (strpos($body, 'smartslider3[') !== false) {
                if (class_exists('\\EshopHelper', false) && EshopHelper::getConfigValue('rich_snippets') == '1') {
                    $body = preg_replace_callback('/(<.*?>)?smartslider3\[([0-9]+)\]/', array(
                        self::class,
                        'cleanEshop'
                    ), $body);
                }

                $bodyParts = explode('</head>', $body);
                /**
                 * Last part is not the head
                 */
                $lastPart             = count($bodyParts) - 1;
                $bodyParts[$lastPart] = preg_replace_callback('/smartslider3\[([0-9]+)\]/', array(
                    self::class,
                    'prepare'
                ), $bodyParts[$lastPart]);

                $application->setBody(implode('</head>', $bodyParts));
            }
        }
    }

    public function onAfterRender() {

        $joomla3Assets = new Joomla3Assets();

        $this->onNextendBeforeCompileHead();

        $joomla3Assets->process();


        ob_start();
        if (class_exists('\\Nextend\\Framework\\Asset\\AssetManager', false)) {
            echo AssetManager::getCSS();
            echo AssetManager::getJs();
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

    public static function prepare($matches) {
        ob_start();
        nextend_smartslider3($matches[1]);

        return ob_get_clean();
    }

    public static function cleanEshop($matches) {
        if (strpos($matches[1], 'itemprop') !== false) {
            return $matches[1];
        }

        return $matches[0];
    }
}