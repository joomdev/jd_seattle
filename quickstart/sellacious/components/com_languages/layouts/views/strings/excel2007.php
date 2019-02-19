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

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$trCode = $this->state->get('list.language');
$srLang = JLanguageHelper::getMetadata('en-GB');
$trLang = JLanguageHelper::getMetadata($trCode);

try
{
	$headers = array(
		'A' => JText::_('COM_LANGUAGES_HEADING_LANG_CONSTANT'),
		'B' => JText::sprintf('COM_LANGUAGES_HEADING_LANG_WITH_NAME', $srLang['name'], $srLang['tag']),
		'C' => JText::sprintf('COM_LANGUAGES_HEADING_LANG_WITH_NAME', $trLang['name'], $trLang['tag']),
		'D' => JText::_('COM_LANGUAGES_HEADING_CLIENT'),
		'E' => JText::_('COM_LANGUAGES_HEADING_EXTENSION'),
		'F' => JText::_('COM_LANGUAGES_HEADING_FILENAME'),
	);

	/** @var  \stdClass[]  $items */
	$items = array();

	foreach ($this->items as $item)
	{
		$row    = array(
			$item->lang_constant,
			$item->orig_text,
			$item->override,
			$item->client,
			$item->extension,
			$item->filename,
		);
		$items[] = $row;
	}

	$spreadsheet = new Spreadsheet;
	$worksheet   = $spreadsheet->getActiveSheet();

	$worksheet->getColumnDimension('A')->setAutoSize(true);
	$worksheet->getColumnDimension('B')->setWidth(200);
	$worksheet->getColumnDimension('C')->setWidth(200);

	// $worksheet->getColumnDimension('D')->setVisible(false)->setCollapsed(true);
	// $worksheet->getColumnDimension('E')->setVisible(false)->setCollapsed(true);
	// $worksheet->getColumnDimension('F')->setVisible(false)->setCollapsed(true);

	$worksheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF000000');
	$worksheet->getStyle('A1:F1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');

	$worksheet->fromArray($headers, null, 'A1');
	$worksheet->fromArray($items, null, 'A2');

	if (headers_sent($file, $line))
	{
		throw new Exception(JText::_('COM_LANGUAGES_STRINGS_EXPORT_HEADERS_SENT_ALREADY', $file, $line));
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="language-translations.xlsx"');
	header('Cache-Control: max-age=0');

	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	$writer->save('php://output');

	$this->app->close();
}
catch (Exception $e)
{
	$this->app->enqueueMessage($e->getMessage(), 'error');
	$this->app->redirect(JRoute::_('index.php?option=com_languages&view=strings'));
}
