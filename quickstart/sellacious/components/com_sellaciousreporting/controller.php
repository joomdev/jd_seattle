<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

defined('_JEXEC') or die;

/**
 * Reporting Controller
 *
 * @since  1.6.0
 */
class SellaciousReportingController extends SellaciousControllerBase
{
	/**
	 * Display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  This object to support chaining.
	 *
	 * @since   1.6.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = $this->input->get('view', 'reports');
		$this->input->set('view', $view);

		return parent::display($cachable, $urlparams);
	}
}
