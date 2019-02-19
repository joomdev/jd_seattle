<?php
/**
 * @version     1.6.1
 * @package     Sellacious.Cli
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */

require_once JPATH_BASE . '/includes/defines.php';

// System includes
require_once JPATH_LIBRARIES . '/import.legacy.php';
require_once JPATH_PLATFORM . '/loader.php';

// Load sellacious dependency first
JLoader::registerNamespace('Sellacious', JPATH_LIBRARIES . '/sellacious/objects');
JLoader::registerAlias('JToolbarHelper', 'Sellacious\Toolbar\ToolbarHelper');

// Load this already to avoid loading from Joomla CMS, we want to skip unrecognised plugins
// require_once JPATH_BASE . '/includes/libraries/PluginHelper.php';

// Followed by Joomla libraries
require_once JPATH_LIBRARIES . '/cms.php';

// Pre-Load configuration.
// Don't remove the Output Buffering due to BOM issues, see JCode 26026
ob_start();
require_once JPATH_CONFIGURATION . '/configuration.php';
ob_end_clean();

// System configuration.
$config = new JConfig;
define('JDEBUG', $config->debug);
unset($config);

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);
