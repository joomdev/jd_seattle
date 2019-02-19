<?php
/**
 * @version     1.6.1
 * @package     com_sellaciousreporting
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     SPL Sellacious Private License; see http://www.sellacious.com/spl.html
 * @author      Aditya Chakraborty <info@bhartiy.com> - http://www.bhartiy.com
 */

// No direct access
use Sellacious\Toolbar\Button\LinkButton;
use Sellacious\Toolbar\Toolbar;

defined('_JEXEC') or die;

/**
 * View class for a list of reports.
 *
 * @since  1.6.0
 */
class SellaciousreportingViewSreports extends SellaciousViewList
{
	/** @var  string */
	protected $action_prefix = 'sreport';

	/** @var  string */
	protected $view_item = 'sreport';

	/** @var  string */
	protected $view_list = 'sreports';

	/** @var  string */
	protected $handler;

	/** @var  int */
	protected $reportId;

	/** @var  bool */
	protected $canEdit = true;

	public function display($tpl = null)
	{
		/** @var  SellaciousreportingModelSreports $model*/
		$model = $this->getModel();

		$this->handler = $model->getHandler();

		$app = JFactory::getApplication();
		$input = JFactory::getApplication()->input;

		$this->reportId  = $input->get('id', 0, 'uint');

		ReportingHelper::canEditReport($this->reportId, $this->canEdit);

		JFactory::getApplication()->input->set('hidemainmenu', 1);

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since  1.6.0
	 */
	protected function addToolbar()
	{
		$this->setPageTitle();

		$toolbar = Toolbar::getInstance();

		$toolbar->appendButton(new LinkButton('chevron-left', 'COM_SELLACIOUSREPORTING_BACK_TO_REPORTING', JRoute::_('index.php?option=com_sellaciousreporting')));

		if ($this->canEdit)
		{
			$toolbar->appendButton(new LinkButton('edit', 'COM_SELLACIOUSREPORTING_REPORTS_EDIT_REPORT', JRoute::_('index.php?option=com_sellaciousreporting&task=report.edit&id=' . $this->reportId)));
		}

		$toolbar->appendButton(new LinkButton('download', 'COM_SELLACIOUSREPORTING_REPORTS_EXPORT_ALL_TO_CSV', JRoute::_('index.php?option=com_sellaciousreporting&view=sreports&format=csv&export_all=1&id=' . $this->reportId)));
		$toolbar->appendButton(new LinkButton('download', 'COM_SELLACIOUSREPORTING_REPORTS_EXPORT_FILTERED_TO_CSV', JRoute::_('index.php?option=com_sellaciousreporting&view=sreports&format=csv&export_all=0&id=' . $this->reportId)));
	}

	/**
	 * To set the document page title based on appropriate logic.
	 *
	 * @since   1.6.0
	 */
	protected function setPageTitle()
	{
		$lang = JFactory::getLanguage();
		$lang->load('mod_smartymenu', JPATH_SITE . '/' . JPATH_SELLACIOUS_DIR . '/modules/mod_smartymenu');
		$lang->load('mod_smartymenu', JPATH_SITE . '/' . JPATH_SELLACIOUS_DIR);

		$active_uri = JUri::getInstance()->toString(array('path', 'query', 'fragment'));
		$base_uri   = rtrim(JUri::base(true), '/') . '/';
		$url        = str_replace($base_uri, '', $active_uri);

		/** @var stdClass $item */
		$app  = JFactory::getApplication();
		$menu = $app->getMenu();
		$item = $menu->getItems(array('link', 'access'), array($url, 0), true);

		if (isset($item->params))
		{
			if (is_string($item->params))
			{
				$item->params = new Registry($item->params);
			}

			$icon  = $item->params->get('menu-anchor_css');
			$title = $item->params->get('menu-page-title');

			if (empty($title))
			{
				$title = JText::_($item->title);
			}
		}

		$reportData = ReportingHelper::getReportData($this->reportId);

		$icon  = empty($icon) ? 'list-alt' : $icon;
		$title = $reportData->title;

		JToolBarHelper::title($title, $icon);
	}
}
