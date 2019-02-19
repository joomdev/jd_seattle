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
defined('_JEXEC') or die;

/**
 * View class for a Comparison.
 */
class SellaciousViewInstall extends JViewLegacy
{
	/**
	 * @var  string
	 */
	protected $version;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		JToolbarHelper::title(JText::_('COM_SELLACIOUS_VIEW_INSTALL'), 'stack');

		$this->version = $this->getVersion();

		return parent::display($tpl);
	}

	/**
	 * Get the current sellacious package version
	 *
	 * @return  string
	 */
	protected function getVersion()
	{
		$table = JTable::getInstance('Extension');
		$keys  = array(
			'type'      => 'package',
			'element'   => 'pkg_sellacious',
			'client_id' => 0,
		);

		$table->load($keys);

		if ($table->get('extension_id'))
		{
			$manifest = json_decode($table->get('manifest_cache'));

			return $manifest->version;
		}

		// Fallback to component version
		$keys  = array(
			'type'      => 'component',
			'element'   => 'com_sellacious',
			'client_id' => 0,
		);
		$table->load($keys);

		if ($table->get('extension_id'))
		{
			$manifest = json_decode($table->get('manifest_cache'));

			return $manifest->version;
		}

		return false;
	}
}
