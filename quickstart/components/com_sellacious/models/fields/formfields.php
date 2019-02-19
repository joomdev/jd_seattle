<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
// no direct access.
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since   1.1.0
 */
class JFormFieldFormFields extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var  string
	 *
	 * @since   1.1.0
	 */
	protected $type = 'FormFields';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.1.0
	 */
	protected function getOptions()
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$options = array();

		$tags     = (string) $this->element['tags'] == 'true';
		$showAll  = (string) $this->element['showall'] == 'true';
		$context  = trim((string) $this->element['context'], ' ,');
		$contexts = $context ? explode(',', $context) : array();

		$query->select($db->qn(array('a.id', 'a.title', 'a.type', 'a.filterable')))
			->from($db->qn('#__sellacious_fields', 'a'))
			->where('a.level > 1');

		$query->select($db->qn('g.title', 'group_title'))
			->join('left', '#__sellacious_fields g ON g.id = a.parent_id')
			->group('a.id');

		if (!$showAll)
		{
			$query->where('a.state = 1');
			$query->where('g.state = 1');
		}

		if (count($contexts))
		{
			$query->where('g.context IN (' . implode(', ', $db->q($contexts)) . ')');
		}

		$query->order('g.title, a.title');

		$db->setQuery($query);

		try
		{
			$helper  = SellaciousHelper::getInstance();
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				$option = new stdClass;
				$format = $result->filterable ? '%s / %s (%s) [Filterable]' : '%s / %s (%s)';

				$option->value = $result->id;
				$option->text  = sprintf($format, $result->group_title, $result->title, $result->type);

				if ($tags)
				{
					$tags_o = $helper->field->getTags($result->id);

					if (count($tags_o))
					{
						$titles = ArrayHelper::getColumn($tags_o, 'tag_title');

						$option->text = sprintf('%s : %s', implode(', ', $titles), $option->text);
					}
				}

				$options[] = $option;
			}
		}
		catch (Exception $e)
		{
			JLog::add($e->getMessage(), JLog::WARNING, 'jerror');

			$options = array();
		}

		$options = ArrayHelper::sortObjects($options, 'text');

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
