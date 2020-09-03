<?php


namespace Nextend\SmartSlider3\Slider\Joomla;


use JEventDispatcher;
use JPluginHelper;
use Nextend\SmartSlider3\Settings;
use Nextend\SmartSlider3\Slider\Base\PlatformSliderBase;
use stdClass;

class PlatformSlider extends PlatformSliderBase {

    public $_module;

    public function addCMSFunctions($text) {
        static $contentPluginsEnabled, $excludedPlugins, $pluginsToRun;
        if ($contentPluginsEnabled === null) {
            $contentPluginsEnabled = intval(Settings::get('joomla-plugins-content-enabled', 1));
            if ($contentPluginsEnabled && class_exists('JEventDispatcher', false)) {
                $excludedPlugins   = explode('||', Settings::get('joomla-plugins-content-excluded', ''));
                $excludedPlugins[] = 'plgcontentemailcloak';
                $excludedPlugins[] = 'plgcontentdropeditor';
                $excludedPlugins[] = 'plgcontentshortcode_ultimate';
                $excludedPlugins[] = 'plgcontentarkcontent';

                JPluginHelper::importPlugin('content');

                $classNames = array();
                foreach (JPluginHelper::getPlugin('content') AS $plugin) {
                    $classNames[] = strtolower('Plg' . $plugin->type . $plugin->name);
                }
                $classNames = array_diff($classNames, $excludedPlugins);

                if (!empty($classNames)) {
                    $dispatcher   = JEventDispatcher::getInstance();
                    $pluginsToRun = array();
                    foreach ($dispatcher->get('_observers') AS $observer) {
                        if (is_object($observer)) {
                            $className = strtolower(get_class($observer));
                            if (in_array($className, $classNames)) {
                                $pluginsToRun[] = $observer;
                            } else if (method_exists($observer, 'onContentPrepare') && !in_array($className, $excludedPlugins)) {
                                $pluginsToRun[] = $observer;
                            }
                        }
                    }
                }
            }
        }

        $text = '<div>' . $text . '</div>';
        if ($contentPluginsEnabled && !empty($pluginsToRun)) {

            $params        = new stdclass();
            $article       = new stdClass;
            $article->text = '<div>' . $text . '</div>';

            $data = array(
                'mod_smartslider',
                &$article,
                &$params,
                0
            );
            foreach ($pluginsToRun AS $observer) {
                // Joomla removes it in every update
                $data['event'] = 'oncontentprepare';
                $observer->update($data);
            }

            return $article->text;
        }

        return $text;
    }
}