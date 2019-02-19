<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Batch;

// no direct access
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * The batch so that we can manage a set of related job queue
 *
 * @since   1.6.1
 */
class Batch implements \JsonSerializable
{
	/**
	 * The batch individual tasks
	 *
	 * @return  BatchStep[]
	 *
	 * @since   1.6.1
	 */
	protected $steps;

	/**
	 * The batch progress
	 *
	 * @return  BatchStep[]
	 *
	 * @since   1.6.1
	 */
	protected $initial;

	public function __construct()
	{
		$this->steps   = array();
		$this->initial = new Registry;
	}

	/**
	 * Add new step to the batch
	 *
	 * @param   string  $name
	 *
	 * @return  BatchStep
	 *
	 * @since   1.6.1
	 */
	public function addStep($name)
	{
		$step = new BatchStep($name, $this);

		if ($o = $this->initial->get($name))
		{
			$step->setSize($o->size);
			$step->setTick($o->tick);
			$step->setComplete($o->complete);
		}

		$this->steps[$name] = $step;

		return $step;
	}

	/**
	 * Get a batch step to the batch
	 *
	 * @param   string  $name
	 * @param   bool    $create
	 *
	 * @return  BatchStep
	 *
	 * @since   1.6.1
	 */
	public function getStep($name, $create = true)
	{
		if (isset($this->steps[$name]))
		{
			return $this->steps[$name];
		}

		if ($create)
		{
			return $this->addStep($name);
		}

		return null;
	}

	public function setProgress(Registry $progress)
	{
		$this->initial = $progress;
	}

	/**
	 * Convert this object to string
	 *
	 * @return  false|string
	 *
	 * @since   1.6.1
	 */
	public function __toString()
	{
		return json_encode($this);
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @return  mixed  Data which can be serialized by <b>json_encode</b>,
	 *                 which is a value of any type other than a resource.
	 *
	 * @since   1.6.1
	 */
	public function jsonSerialize()
	{
		return $this->steps;
	}
}
