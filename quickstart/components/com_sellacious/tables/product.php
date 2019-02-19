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
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Product Table class
 *
 * @since   1.0.0
 */
class SellaciousTableProduct extends SellaciousTable
{
	/**
	 * Flag to set whether to increment the alias or not
	 *
	 * @var   bool
	 *
	 * @since   1.6.0
	 */
	protected $_incrementAlias = true;

	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  A database connector object
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		$this->_array_fields = array('features', 'params', 'tags');

		parent::__construct('#__sellacious_products', 'id', $db);
	}

	/**
	 * Overloaded load function to post-process the _array_fields.
	 *
	 * @param   mixed  $pk     Primary key value or key-value pair for the row to load
	 * @param   bool   $reset  Whether to reset object properties before load.
	 *
	 * @return  string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind()
	 *
	 * @since   1.6.0
	 */
	public function load($pk = null, $reset = true)
	{
		$load = parent::load($pk, $reset);

		$this->set('features', (array) $this->get('features'));
		$this->set('tags', (array) $this->get('tags'));

		return $load;
	}

	/**
	 * Assess that the nested set data is valid.
	 *
	 * @return  boolean  True if the instance is sane and able to be stored in the database.
	 *
	 * @link    http://docs.joomla.org/JTable/check
	 * @since   11.1
	 *
	 * @throws  Exception
	 * @throws  RuntimeException on database error.
	 * @throws  UnexpectedValueException
	 */
	public function check()
	{
		if (empty($this->id))
		{
			if (property_exists($this, 'created'))
			{
				$this->set('created', JFactory::getDate()->toSql());
			}

			if (property_exists($this, 'created_by'))
			{
				$this->set('created_by', JFactory::getUser()->id);
			}

			if (property_exists($this, 'ordering'))
			{
				$this->set('ordering', self::getNextOrder());
			}
		}
		else
		{
			if (property_exists($this, 'modified'))
			{
				$this->set('modified', JFactory::getDate()->toSql());
			}

			if (property_exists($this, 'modified_by'))
			{
				$this->set('modified_by', JFactory::getUser()->id);
			}
		}

		if (property_exists($this, 'alias'))
		{
			// If not have an alias, set-up from record title
			if (empty($this->alias))
			{
				$this->set('alias', $this->get('title'));
			}

			// Prepare and sanitize the alias
			$this->alias = JFilterOutput::stringURLSafe($this->alias);

			if (trim(str_replace('-', '', $this->alias)) == '')
			{
				$this->alias = JFactory::getDate()->format('Y-m-d-H-i-s') . '-' . rand(10000, 99999);
			}

			// If the alias we have, is existing for any other product or a category let's increment it
			$filterP = array(
				'list.select' => 'a.id',
				'list.where'  => 'a.id != ' . (int) $this->id,
				'alias'       => $this->alias,
			);
			$filterC = array(
				'list.select' => 'a.id',
				'alias'       => $this->alias,
			);

			while ($this->helper->product->loadResult($filterP) || $this->helper->category->loadResult($filterC))
			{
				if ($this->_incrementAlias)
				{
					$this->alias = StringHelper::increment($this->alias, 'dash');

					$filterP['alias'] = $this->alias;
					$filterC['alias'] = $this->alias;
				}
				else
				{
					throw new Exception(JText::sprintf('COM_SELLACIOUS_PRODUCT_UNIQUE_ALIAS_ERROR', $this->alias));
				}
			}
		}

		return true;
	}
}
