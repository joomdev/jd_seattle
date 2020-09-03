<?php
defined('_JEXEC') or die('Restricted access');
?><?php

global $acymPlugins;
function acym_loadPlugins()
{
    $dynamics = acym_getFolders(ACYM_BACK.'dynamics');

    $pluginClass = acym_get('class.plugin');
    $plugins = $pluginClass->getAll('folder_name');

    foreach ($dynamics as $key => $oneDynamic) {
        if (!empty($plugins[$oneDynamic]) && '0' === $plugins[$oneDynamic]->active) unset($dynamics[$key]);
        if ('managetext' === $oneDynamic) unset($dynamics[$key]);
    }

    foreach ($plugins as $pluginFolder => $onePlugin) {
        if (in_array($pluginFolder, $dynamics) || '0' === $onePlugin->active) continue;
        $dynamics[] = $pluginFolder;
    }

    $dynamics[] = 'managetext';

    global $acymPlugins;
    foreach ($dynamics as $oneDynamic) {
        $dynamicFile = acym_getPluginPath($oneDynamic);
        $className = 'plgAcym'.ucfirst($oneDynamic);

        if (isset($acymPlugins[$className]) || !file_exists($dynamicFile) || !include_once $dynamicFile) continue;
        if (!class_exists($className)) continue;

        $plugin = new $className();
        if (!in_array($plugin->cms, ['all', 'Joomla']) || !$plugin->installed) continue;

        $acymPlugins[$className] = $plugin;
    }
}

function acym_trigger($method, $args = [], $plugin = null)
{
    if (!in_array(acym_getPrefix().'acym_configuration', acym_getTableList())) return null;

    global $acymPlugins;
    if (empty($acymPlugins)) acym_loadPlugins();

    $result = [];
    foreach ($acymPlugins as $class => $onePlugin) {
        if (!method_exists($onePlugin, $method)) continue;
        if (!empty($plugin) && $class != $plugin) continue;

        try {
            $value = call_user_func_array([$onePlugin, $method], $args);
            if (isset($value)) $result[] = $value;
        } catch (Exception $e) {

        }
    }

    return $result;
}

function acym_checkPluginsVersion()
{
    $pluginClass = acym_get('class.plugin');
    $pluginsInstalled = $pluginClass->getMatchingElements();
    $pluginsInstalled = $pluginsInstalled['elements'];
    if (empty($pluginsInstalled)) return true;

    $url = ACYM_UPDATEMEURL.'integrationv6&task=getAllPlugin&cms='.ACYM_CMS;

    $res = acym_fileGetContent($url);
    $pluginsAvailable = json_decode($res, true);

    foreach ($pluginsInstalled as $key => $pluginInstalled) {
        foreach ($pluginsAvailable as $pluginAvailable) {
            if (str_replace('.zip', '', $pluginAvailable['file_name']) == $pluginInstalled->folder_name && !version_compare($pluginInstalled->version, $pluginAvailable['version'], '>=')) {
                $pluginsInstalled[$key]->uptodate = 0;
                $pluginsInstalled[$key]->latest_version = $pluginAvailable['version'];
                $pluginClass->save($pluginsInstalled[$key]);
            }
        }
    }

    return true;
}

