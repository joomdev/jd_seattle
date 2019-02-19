<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('sellacious.loader');

/**
 * Create an empty class to meet the situation where sellacious backoffice is not installed yet.
 * In this case however, the backoffice part of the component will not be processed and only the joomla frontend and backend files, and the datanse will be installed.
 */
if (!class_exists('SellaciousInstallerModule'))
{
    class SellaciousInstallerModule
    {
    }
}

/**
 * Script file of filter module.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_testInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *
 * @package     Joomla.Administrator
 * @subpackage  mod_sellacious_filters
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since  1.6.0
 */
class mod_sellacious_filtersInstallerScript extends SellaciousInstallerModule
{
}
