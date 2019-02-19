<?php
/**
 * @package    Sellacious.Application
 *
 * @copyright  Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access.
defined('_JEXEC') or die;

// Global definitions
$parts = explode(DIRECTORY_SEPARATOR, JPATH_BASE);
array_pop($parts);

// Defines
define('JPATH_ROOT',          implode(DIRECTORY_SEPARATOR, $parts));
define('JPATH_SITE',          JPATH_ROOT);
define('JPATH_CONFIGURATION', JPATH_ROOT);
define('JPATH_LIBRARIES',     JPATH_ROOT . '/libraries');
define('JPATH_PLATFORM',      JPATH_ROOT . '/libraries');
define('JPATH_PLUGINS',       JPATH_ROOT . '/plugins');
define('JPATH_INSTALLATION',  JPATH_ROOT . '/installation');
define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');

define('JPATH_CACHE',         JPATH_BASE . '/cache');
define('JPATH_THEMES',        JPATH_BASE . '/templates');
define('JPATH_MANIFESTS',     JPATH_ADMINISTRATOR . '/manifests');

// Auto evaluate the base dir for sellacious application
define('JPATH_SELLACIOUS', JPATH_BASE);
define('JPATH_SELLACIOUS_DIR', basename(JPATH_BASE));
