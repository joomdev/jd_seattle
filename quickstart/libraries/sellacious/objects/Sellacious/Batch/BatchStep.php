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
defined('_JEXEC') or die;

/**
 * The timer utility to time a process with progress logging
 *
 * @since   1.6.1
 */
class BatchStep implements \JsonSerializable
{
	/**
	 * The batch step name
	 *
	 * @return  string
	 *
	 * @since   1.6.1
	 */
	protected $name;

	/**
	 * The parent batch instance
	 *
	 * @return  Batch
	 *
	 * @since   1.6.1
	 */
	protected $batch;

	/**
	 * The batch step total size
	 *
	 * @return  int
	 *
	 * @since   1.6.1
	 */
	protected $size = 0;

	/**
	 * The batch step completed size
	 *
	 * @return  int
	 *
	 * @since   1.6.1
	 */
	protected $tick = 0;

	/**
	 * The batch step completion status
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	protected $complete = false;

	/**
	 * The batch step most recent message
	 *
	 * @return  string
	 *
	 * @since   1.6.1
	 */
	protected $message;

	/**
	 * Get the batch name
	 *
	 * @param   string  $name
	 * @param   Batch   $batch
	 *
	 * @since   1.6.1
	 */
	public function __construct($name, $batch)
	{
		$this->name  = $name;
		$this->batch = $batch;
	}

	/**
	 * Get the batch name
	 *
	 * @return  string
	 *
	 * @since   1.6.1
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the batch size
	 *
	 * @param   int  $n
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function setSize($n)
	{
		$this->size = $n;
	}

	/**
	 * Get the batch size
	 *
	 * @return  int
	 *
	 * @since   1.6.1
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Set the batch progress
	 *
	 * @param   int     $n
	 * @param   string  $message  To preserve previous message pass null, else the given text will be set
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function setTick($n, $message = null)
	{
		$this->tick    = $n;
		$this->message = $message === null ? $this->message : $message;
	}

	/**
	 * Get the batch size
	 *
	 * @return  int
	 *
	 * @since   1.6.1
	 */
	public function getTick()
	{
		return $this->tick;
	}

	/**
	 * Set the batch completion status
	 *
	 * @param   bool  $complete
	 *
	 * @return  void
	 *
	 * @since   1.6.1
	 */
	public function setComplete($complete)
	{
		$this->complete = (bool) $complete;
	}

	/**
	 * Get the batch completion status
	 *
	 * @return  bool
	 *
	 * @since   1.6.1
	 */
	public function isComplete()
	{
		return $this->complete;
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
		return array('size' => $this->size, 'tick' => $this->tick, 'complete' => $this->complete);
	}
}
