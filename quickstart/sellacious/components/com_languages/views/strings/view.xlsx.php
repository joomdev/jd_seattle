<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// No direct access.
defined('_JEXEC') or die;

/**
 * View for language overrides list export.
 *
 * @since  1.6.0
 */
class LanguagesViewStrings extends SellaciousViewList
{
	/**
	 * Prepare display
	 *
	 * @param   string  $tpl  The sub-layout to render
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.6.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		$this->state->set('list.start', 0);
		$this->state->set('list.limit', 0);

		$this->setLayout('excel2007');

		parent::display($tpl);
	}
}
