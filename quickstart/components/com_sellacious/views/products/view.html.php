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
 * View class for a list of categories.
 *
 * @since  1.0.0
 */
class SellaciousViewProducts extends SellaciousView
{
	/**
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	public $activeFilters;

	/**
	 * @var  JForm
	 *
	 * @since  1.0.0
	 */
	public $filterForm;

	/**
	 * @var  stdClass[]
	 *
	 * @since  1.0.0
	 */
	protected $items;

	/**
	 * @var  stdClass[]
	 *
	 * @since  1.0.0
	 */
	protected $categories;

	/**
	 * @var  stdClass[]
	 *
	 * @since  1.0.0
	 */
	protected $filters;

	/**
	 * @var  JPagination
	 *
	 * @since  1.0.0
	 */
	protected $pagination;

	/**
	 * @var  JObject
	 *
	 * @since  1.0.0
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The sub-layout
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->filters    = $this->get('Filters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JLog::add(implode("\n", $errors), JLog::WARNING, 'jerror');

			return false;
		}

		$this->setPathway();

		return parent::display($tpl);
	}

	/**
	 * Method to add pathway for the breadcrumbs to the view
	 *
	 * @return  void
	 *
	 * @since   1.6.0
	 */
	public function setPathway()
	{
		$catid = $this->state->get('filter.category_id');

		if ($catid)
		{
			$pks    = $this->helper->category->getParents($catid, true);
			$filter = array('id' => $pks, 'list.select' => 'a.id, a.title', 'list.where' => 'a.level > 0');
			$cats   = $this->helper->category->loadObjectList($filter);
			$crumbs = array();

			foreach ($cats as $cat)
			{
				$crumb = new stdClass;
				$link  = JRoute::_('index.php?option=com_sellacious&view=categories&category_id=' . (int) $cat->id);

				$crumb->name = $cat->title;
				$crumb->link = $link;

				$crumbs[] = $crumb;
			}

			$crumb = new stdClass;

			$crumb->name = JText::_('COM_SELLACIOUS_CATEGORY_PRODUCTS');
			$crumb->link = null;

			$crumbs[] = $crumb;

			$this->app->getPathway()->setPathway($crumbs);
		}
	}
}
