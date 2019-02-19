<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access
defined('JPATH_PLATFORM') or die;

/**
 * Class pkg_sellaciousInstallerScript
 *
 * @since   1.0.0
 */
class pkg_sellaciousInstallerScript
{
	/**
	 * method to run before package install
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function preflight($type, $installer)
	{
		// Sellacious products cache plugin faces issue due to changed table structure. Just kill that old plugin.
		// Todo: Remove this workaround and make extended extensions not load when package versions differ
		$fileName = JPATH_PLUGINS . '/system/sellaciouscache/sellaciouscache.php';

		if (is_file($fileName))
		{
			$contents = @file_get_contents($fileName);

			if (preg_match('/@version\s+(1\.[0-4]\.\d+)/', $contents))
			{
				$text = '<?php' . PHP_EOL .
					'// This file was removed due to Sellacious v1.5.0 version incompatibility.' . PHP_EOL .
					'// Install v1.5.0 and new plugin will be here.' . PHP_EOL;

				if (!JFile::write($fileName, $text) && !JFile::delete($fileName))
				{
					// Finally, fallback to disabling the plugin. User may need to enable it later manually.
					$table = JTable::getInstance('Extension');
					$table->load(array('type' => 'plugin', 'folder' => 'system', 'element' => 'sellaciouscache'));
					$table->set('enabled', 0);
					$table->store();
				}
			}
		}
	}

	/**
	 * method to run before package install
	 *
	 * @param   string                 $type
	 * @param   JInstallerAdapterFile  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.5.0
	 */
	public function postflight($type, $installer)
	{
		// Delete the old version files
		if ($type == 'update')
		{
			$this->cleanupOldFiles();
		}
	}

	/**
	 * method to run before package uninstall
	 *
	 * @param   JInstallerAdapterPackage  $installer
	 *
	 * @throws  Exception
	 *
	 * @since   1.4.4
	 */
	public function uninstall($installer)
	{
		$table = JTable::getInstance('Extension');
		$files = $table->load(array('type' => 'file', 'element' => 'sellacious'));

		$table = JTable::getInstance('Extension');
		$lib   = $table->load(array('type' => 'library', 'element' => 'sellacious'));

		if ($files || $lib)
		{
			JLog::add('You need to uninstall "Sellacious Extended Package" first. The list has been filtered for your convenience.', JLog::WARNING, 'jerror');

			JFactory::getApplication()->redirect('index.php?option=com_installer&view=manage&filter[search]=Sellacious&filter[type]=package');
		}
	}

	/**
	 * Delete the files which existed in earlier versions but not in this version
	 *
	 * @return  void
	 *
	 * @since   1.5.2
	 */
	protected function cleanupOldFiles()
	{
		$files = array(
			'media/com_sellacious/js/fe.view.profile.js',
			'media/com_sellacious/js/util.rollover.js',
			'modules/mod_usercurrency/tmpl/default.php',
			'modules/mod_usercurrency/language/en-GB/en-GB.mod_usercurrency.ini',
			'modules/mod_usercurrency/language/en-GB/en-GB.mod_usercurrency.sys.ini',
			'modules/mod_usercurrency/mod_usercurrency.php',
			'media/mod_usercurrency/css/style.css',
			'media/mod_usercurrency/js/default.js',
			'modules/mod_usercurrency/mod_usercurrency.xml',
			'modules/mod_sellacious_cart/mod_sellacious_cart.php',
			'modules/mod_sellacious_cart/language/en-GB/en-GB.mod_sellacious_cart.ini',
			'modules/mod_sellacious_cart/language/en-GB/en-GB.mod_sellacious_cart.sys.ini',
			'modules/mod_sellacious_cart/tmpl/default.php',
			'media/mod_sellacious_cart/css/style.css',
			'media/mod_sellacious_cart/js/show-cart.js',
			'modules/mod_sellacious_cart/mod_sellacious_cart.xml',
			'modules/mod_sellacious_filters/mod_sellacious_filters.php',
			'modules/mod_sellacious_filters/helper.php',
			'modules/mod_sellacious_filters/language/en-GB/en-GB.mod_sellacious_filters.ini',
			'modules/mod_sellacious_filters/language/en-GB/en-GB.mod_sellacious_filters.sys.ini',
			'modules/mod_sellacious_filters/language/index.html',
			'modules/mod_sellacious_filters/tmpl/default.php',
			'modules/mod_sellacious_filters/tmpl/default_level.php',
			'media/mod_sellacious_filters/css/filters.css',
			'media/mod_sellacious_filters/less/filters.less',
			'media/mod_sellacious_filters/js/filters.js',
			'media/mod_sellacious_filters/js/jquery.treeview.js',
			'modules/mod_sellacious_filters/mod_sellacious_filters.xml',
			'modules/mod_sellacious_finder/mod_sellacious_finder.php',
			'modules/mod_sellacious_finder/language/en-GB/en-GB.mod_sellacious_finder.ini',
			'modules/mod_sellacious_finder/language/en-GB/en-GB.mod_sellacious_finder.sys.ini',
			'modules/mod_sellacious_finder/language/index.html',
			'modules/mod_sellacious_finder/tmpl/default.php',
			'modules/mod_sellacious_finder/tmpl/dropdown.php',
			'modules/mod_sellacious_finder/tmpl/expand.php',
			'modules/mod_sellacious_finder/tmpl/overlay.php',
			'media/mod_sellacious_finder/css/dropdown.css',
			'media/mod_sellacious_finder/css/expand.css',
			'media/mod_sellacious_finder/css/overlay.css',
			'media/mod_sellacious_finder/css/template.css',
			'modules/mod_sellacious_finder/mod_sellacious_finder.xml',
			'modules/mod_sellacious_latestproducts/mod_sellacious_latestproducts.php',
			'modules/mod_sellacious_latestproducts/helper.php',
			'modules/mod_sellacious_latestproducts/language/en-GB/en-GB.mod_sellacious_latestproducts.ini',
			'modules/mod_sellacious_latestproducts/language/en-GB/en-GB.mod_sellacious_latestproducts.sys.ini',
			'modules/mod_sellacious_latestproducts/tmpl/carousel.php',
			'modules/mod_sellacious_latestproducts/tmpl/default.php',
			'modules/mod_sellacious_latestproducts/tmpl/grid.php',
			'modules/mod_sellacious_latestproducts/tmpl/list.php',
			'modules/mod_sellacious_latestproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_latestproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_latestproducts/css/style.css',
			'modules/mod_sellacious_latestproducts/mod_sellacious_latestproducts.xml',
			'modules/mod_sellacious_relatedproducts/mod_sellacious_relatedproducts.php',
			'modules/mod_sellacious_relatedproducts/helper.php',
			'modules/mod_sellacious_relatedproducts/language/en-GB/en-GB.mod_sellacious_relatedproducts.ini',
			'modules/mod_sellacious_relatedproducts/language/en-GB/en-GB.mod_sellacious_relatedproducts.sys.ini',
			'modules/mod_sellacious_relatedproducts/tmpl/carousel.php',
			'modules/mod_sellacious_relatedproducts/tmpl/default.php',
			'modules/mod_sellacious_relatedproducts/tmpl/grid.php',
			'modules/mod_sellacious_relatedproducts/tmpl/list.php',
			'modules/mod_sellacious_relatedproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_relatedproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_relatedproducts/css/style.css',
			'modules/mod_sellacious_relatedproducts/mod_sellacious_relatedproducts.xml',
			'modules/mod_sellacious_specialcatsproducts/mod_sellacious_specialcatsproducts.php',
			'modules/mod_sellacious_specialcatsproducts/helper.php',
			'modules/mod_sellacious_specialcatsproducts/language/en-GB/en-GB.mod_sellacious_specialcatsproducts.ini',
			'modules/mod_sellacious_specialcatsproducts/language/en-GB/en-GB.mod_sellacious_specialcatsproducts.sys.ini',
			'modules/mod_sellacious_specialcatsproducts/tmpl/carousel.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/default.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/grid.php',
			'modules/mod_sellacious_specialcatsproducts/tmpl/list.php',
			'modules/mod_sellacious_specialcatsproducts/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_specialcatsproducts/assets/js/owl.carousel.js',
			'media/mod_sellacious_specialcatsproducts/css/style.css',
			'modules/mod_sellacious_specialcatsproducts/mod_sellacious_specialcatsproducts.xml',
			'administrator/components/com_sellacious/sql/updates/mysqli/1.6.1.sql',
			'media/mod_usercurrency/js/jquery.jqtransform.js',
			'media/mod_usercurrency/img/select_left.gif',
			'media/mod_usercurrency/img/select_right.gif',
			'modules/mod_sellacious_stores/mod_sellacious_stores.php',
			'modules/mod_sellacious_stores/helper.php',
			'modules/mod_sellacious_stores/language/en-GB/en-GB.mod_sellacious_stores.ini',
			'modules/mod_sellacious_stores/language/en-GB/en-GB.mod_sellacious_stores.sys.ini',
			'modules/mod_sellacious_stores/tmpl/carousel.php',
			'modules/mod_sellacious_stores/tmpl/default.php',
			'modules/mod_sellacious_stores/tmpl/grid.php',
			'modules/mod_sellacious_stores/tmpl/list.php',
			'modules/mod_sellacious_stores/assets/css/owl.carousel.min.css',
			'modules/mod_sellacious_stores/assets/js/owl.carousel.js',
			'media/mod_sellacious_stores/css/style.css',
			'modules/mod_sellacious_stores/mod_sellacious_stores.xml'
		);

		foreach ($files as $file)
		{
			JFile::delete(JPATH_ROOT . '/' . $file);
		}
	}
}
