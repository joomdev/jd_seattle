<?php

define('NEXTEND_INSTALL', true);

jimport('joomla.installer.helper');
jimport('joomla.filesystem.folder');

if (!function_exists('NextendSS3DeleteExtensionFolder')) {

    function NextendSS3DeleteExtensionFolder() {
        $pkg_path = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_nextend_installer/';
        if (file_exists($pkg_path)) {
            @JFolder::delete($pkg_path . '/components');
            @JFolder::delete($pkg_path . '/modules');
            @JFolder::delete($pkg_path . '/plugins');
            @JFolder::delete($pkg_path . '/libraries');
            @JFolder::delete($pkg_path . '/media/n2/n');
            @JFolder::delete($pkg_path . '/media/n2/ss3');
        }
        $db = JFactory::getDBO();
        $db->setQuery("DELETE FROM #__menu WHERE title LIKE 'com_nextend_installer'")
           ->execute();
        $db->setQuery("DELETE FROM #__extensions WHERE name LIKE 'nextend_installer'")
           ->execute();
    }

    function com_install() {
        register_shutdown_function("NextendSS3DeleteExtensionFolder");
        $installer = new Installer();
        $installer->install();

        return true;
    }

    function com_uninstall() {
        $installer = new Installer();
        $installer->uninstall();

        return true;
    }

    class Installer extends JObject {

        var $name = 'Nextend Installer';
        var $com = 'com_nextend_installer';

        function install() {
            $pkg_path = str_replace('/', DIRECTORY_SEPARATOR, JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->com . DIRECTORY_SEPARATOR);

            if (JFolder::exists($pkg_path . 'libraries')) {
                $librariesPath = defined('JPATH_LIBRARIES') ? JPATH_LIBRARIES : JPATH_PLATFORM;
                $target        = $librariesPath . DIRECTORY_SEPARATOR . 'nextend2';
                if (JFolder::exists($target)) {
                    JFolder::delete($target);
                }
                JFolder::copy($pkg_path . 'libraries/nextend2', $target, '', true);
                JFolder::delete($pkg_path . 'libraries');
            }

            if (JFolder::exists($pkg_path . 'media/n2')) {
                $librariesPath = JPATH_SITE . DIRECTORY_SEPARATOR . 'media';
                $target        = $librariesPath . DIRECTORY_SEPARATOR . 'n2';
                if (JFolder::exists($target)) {
                    JFolder::delete($target);
                }
                JFolder::copy($pkg_path . 'media/n2', $target, '', true);
                JFolder::delete($pkg_path . 'media');
            }


            $extensions = array_merge(JFolder::folders($pkg_path . 'components', '.', false, true), JFolder::folders($pkg_path . 'modules', '.', false, true), JFolder::folders($pkg_path . 'plugins/system', '.', false, true), JFolder::folders($pkg_path . 'plugins/installer', '.', false, true));

            foreach ($extensions as $path) {
                $installer = new JInstaller();
                $installer->setOverwrite(true);
                if ($success = $installer->install($path)) {
                } else {
                    $msgcolor = "#FFD0D0";
                    $msgtext  = "ERROR: Could not install the $path. Please contact us on our support page: http://www.nextendweb.com/help/support";
                    ?>
                    <table bgcolor="<?php echo $msgcolor; ?>" width="100%">
                        <tr style="height:30px">
                            <td><font size="2"><b><?php echo $msgtext; ?></b></font></td>
                        </tr>
                    </table>
                    <?php
                }
                if ($success && file_exists($path . "/install.php")) {
                    require_once $path . "/install.php";
                }
                if ($success && file_exists($path . "/message.php")) {
                    include($path . "/message.php");
                }
            }
            $db = JFactory::getDBO();
            $db->setQuery("UPDATE #__extensions SET enabled=1 WHERE (name LIKE '%nextend%' OR name LIKE '%smartslider3%')  AND type='plugin'")
               ->execute();
            $proInvert = 1;
        

            // We must delete the stucked update sites if upgrade to pro or downgrade to free
            $update_site_id_to_remove = $db->setQuery("SELECT update_site_id FROM #__update_sites WHERE location LIKE 'https://secure.nextendweb.com/api/api.php?action=joomla_version&platform=joomla&product=smartslider3&pro=" . $proInvert . "'")
                                           ->loadResult();

            if ($update_site_id_to_remove) {
                $db->setQuery("DELETE FROM #__update_sites_extensions WHERE update_site_id = '" . $update_site_id_to_remove . "'")
                   ->execute();
                $db->setQuery("DELETE FROM #__update_sites WHERE update_site_id = '" . $update_site_id_to_remove . "'")
                   ->execute();
            }
        }

        function uninstall() {
        }

    }

    class com_nextend_installerInstallerScript {

        function install($parent) {
            com_install();
        }

        function uninstall($parent) {
            com_uninstall();
        }

        function update($parent) {
            com_install();
        }
    }
}