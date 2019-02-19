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
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Search view class for sellacious which uses Finder package internally.
 *
 * @since  1.4.6
 */
class SellaciousViewSearch extends SellaciousView
{
	public $results;

	public $total;

	public $pagination;

	/** @var   JDocumentHtml */
	public $document;

	public $suggested;

	public $explained;

	public $pageclass_sfx;

	/** @var  FinderIndexerQuery */
	protected $query;

	/** @var  Registry */
	protected $params;

	protected $state;

	protected $user;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  JError object on failure, void on success.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');

		if ($this->state->get('search.integration') == 'finder')
		{
			$this->setLayout('finder');

			return $this->useFinder($tpl);
		}

		$start = $this->app->input->getInt('limitstart', 0);
		$limit = $this->app->input->getInt('limit', 10);

		/** @var  SellaciousModelSearch $model */
		$model = $this->getModel('Search');
		$model->getState();
		$model->setState('list.start', $start);
		$model->setState('list.limit', $limit);
		$model->setState('filter.query', $this->app->input->getString('q'));

		try
		{
			$results = $model->getItems();
		}
		catch (Exception $e)
		{
			JLog::add('Search error: ' . $e->getMessage(), JLog::ERROR, 'debug');
		}

		$total      = $this->get('Total');
		$pagination = $this->get('Pagination');

		/** @var  Registry $params */
		$params = $this->app->getParams();

		// Push out the view data.
		$this->params     = &$params;
		$this->results    = &$results;
		$this->total      = &$total;
		$this->pagination = &$pagination;

		return parent::display($tpl);

	}

	/**
	 * @param $tpl
	 *
	 * @return null
	 *
	 * @since   1.5.2
	 */
	protected function useFinder($tpl)
	{
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_finder/models/');

		$model = JModelLegacy::getInstance('Search', 'FinderModel');

		if (!$model)
		{
			JLog::add(JText::_('COM_SELLACIOUS_SEARCH_MODEL_LOAD_FAILED'), JLog::WARNING, 'jerror');

			return false;
		}

		$this->setModel($model, true);

		/** @var  Registry  $params */
		$params = $this->app->getParams();

		// Get view data.
		$state = $this->get('State');
		$query = $this->get('Query');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderQuery') : null;

		$results = $this->get('Results');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderResults') : null;

		$total = $this->get('Total');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderTotal') : null;

		$pagination = $this->get('Pagination');
		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderPagination') : null;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		// Configure the pathway.
		if (!empty($query->input))
		{
			$this->app->getPathWay()->addItem($this->escape($query->input));
		}

		// Push out the view data.
		$this->state      = &$state;
		$this->params     = &$params;
		$this->query      = &$query;
		$this->results    = &$results;
		$this->total      = &$total;
		$this->pagination = &$pagination;

		// Check for a double quote in the query string.
		if (strpos($this->query->input, '"'))
		{
			// Get the application router.
			$router = $this->app->getRouter();

			// Fix the q variable in the URL.
			$router->setVar('q', $this->query->input);
		}

		// Log the search
		JSearchHelper::logSearch($this->query->input, 'com_finder');

		// Push out the query data.
		JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');
		$this->suggested = JHtml::_('query.suggested', $query);
		$this->explained = JHtml::_('query.explained', $query);

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $this->app->getMenu()->getActive();

		if (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		$this->prepareDocument($query);

		JDEBUG ? JProfiler::getInstance('Application')->mark('beforeFinderLayout') : null;

		parent::display($tpl);

		JDEBUG ? JProfiler::getInstance('Application')->mark('afterFinderLayout') : null;

		return null;
	}

	/**
	 * Method to get hidden input fields for a get form so that control variables
	 * are not lost upon form submission
	 *
	 * @return  string  A string of hidden input form fields
	 *
	 * @since   2.5
	 */
	protected function getFields()
	{
		$fields = null;

		// Get the URI.
		$uri = JUri::getInstance(JRoute::_($this->query->toUri()));
		$uri->delVar('q');
		$uri->delVar('o');
		$uri->delVar('t');
		$uri->delVar('d1');
		$uri->delVar('d2');
		$uri->delVar('w1');
		$uri->delVar('w2');
		$elements = (array) $uri->getQuery(true);

		// Create hidden input elements for each part of the URI.
		foreach ($elements as $n => $v)
		{
			if (is_scalar($v))
			{
				$fields .= '<input type="hidden" name="' . $n . '" value="' . $v . '" />';
			}
		}

		return $fields;
	}

	/**
	 * Method to get the layout file for a search result object.
	 *
	 * @param   string  $layout  The layout file to check. [optional]
	 *
	 * @return  string  The layout file to use.
	 *
	 * @since   2.5
	 */
	protected function getLayoutFile($layout = null)
	{
		// Create and sanitize the file name.
		$file = $this->_layout . '_' . preg_replace('/[^A-Z0-9_\.-]/i', '', $layout);

		// Check if the file exists.
		jimport('joomla.filesystem.path');
		$filetofind = $this->_createFileName('template', array('name' => $file));
		$exists     = JPath::find($this->_path['template'], $filetofind);

		return ($exists ? $layout : 'result');
	}

	/**
	 * Prepares the document
	 *
	 * @param   FinderIndexerQuery  $query  The search query
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function prepareDocument($query)
	{
		$menus = $this->app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_SELLACIOUS_SEARCH_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $this->app->get('sitename');
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $this->app->get('sitename'), $title);
		}
		elseif ($this->app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $this->app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($layout = $this->params->get('article_layout'))
		{
			$this->setLayout($layout);
		}

		// Configure the document meta-description.
		if (!empty($this->explained))
		{
			$explained = $this->escape(html_entity_decode(strip_tags($this->explained), ENT_QUOTES, 'UTF-8'));
			$this->document->setDescription($explained);
		}

		// Configure the document meta-keywords.
		if (!empty($query->highlight))
		{
			$this->document->setMetadata('keywords', implode(', ', $query->highlight));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
