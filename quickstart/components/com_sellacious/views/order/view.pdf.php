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

use Joomla\Registry\Registry;

/**
 * View to edit
 *
 * @property int counter
 */
class SellaciousViewOrder extends SellaciousView
{
	/** @var  JObject */
	protected $state;

	/** @var  Registry */
	protected $item;

	/**
	 * Display the view
	 *
	 * @param  string $tpl
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item  = $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		ob_start();
		parent::display();
		$content = ob_get_clean();

		$this->renderPdf($content);

		return true;
	}

	/**
	 * Render pdf from the html layout using tcPDF
	 *
	 * @param   string  $content
	 *
	 * @throws  Exception
	 *
	 * @since   1.2.0
	 */
	protected function renderPdf($content)
	{
		jimport('tcpdf.tcpdf');

		$doc = new TCPDF();
		$doc->setPrintHeader(false);
		$doc->AddPage();
		$doc->writeHTML($content);
		$doc->Output('Invoice - ' . $this->item->get('order_number'));

		jexit();
	}
}
