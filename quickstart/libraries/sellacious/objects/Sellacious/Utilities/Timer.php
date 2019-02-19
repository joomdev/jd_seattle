<?php
/**
 * @version     1.6.1
 * @package     sellacious
 *
 * @copyright   Copyright (C) 2012-2018 Bhartiy Web Technologies. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Izhar Aazmi <info@bhartiy.com> - http://www.bhartiy.com
 */
namespace Sellacious\Utilities;

// no direct access
defined('_JEXEC') or die;

/**
 * The timer utility to time a process with progress logging
 *
 * @since   1.4.7
 */
class Timer
{
	/**
	 * @var    static[]
	 *
	 * @since  1.4.7
	 */
	private static $timers = array();

	/**
	 * @var    string
	 *
	 * @since  1.4.7
	 */
	protected $name;

	/**
	 * @var    float
	 *
	 * @since  1.4.7
	 */
	protected $start = 0;

	/**
	 * @var    float
	 *
	 * @since  1.4.7
	 */
	protected $last = 0;

	/**
	 * @var    float
	 *
	 * @since  1.4.7
	 */
	protected $end = 0;

	/**
	 * @var    string
	 *
	 * @since  1.4.7
	 */
	protected $log;

	/**
	 * @var    bool
	 *
	 * @since  1.4.7
	 */
	protected $running = false;

	/**
	 * Private constructor to prevent externally instantiation
	 *
	 * @param   string  $name     The timer name/identifier
	 * @param   string  $logfile  The log file where the output shall be written, if omitted a new file is created in Joomla tmp_path
	 *
	 * @since   1.4.7
	 */
	private function __construct($name, $logfile = null)
	{
		if (!$logfile)
		{
			$now      = \JFactory::getDate()->format('Y-m-d H-i-s', true);
			$tmp_path = \JFactory::getConfig()->get('tmp_path');
			$logfile  = $tmp_path . '/' . $name . '-' . $now . '.log';
		}

		$this->name = $name;
		$this->log  = $logfile;
	}

	/**
	 * Public interface to get an instance of this object uniquely identified by the <var>$name</var>
	 *
	 * @param   string  $name     The timer name/identifier
	 * @param   string  $logfile  The log file where the output shall be written, if omitted a new file is created in Joomla tmp_path
	 *
	 * @return  Timer
	 *
	 * @since   1.4.7
	 */
	public static function getInstance($name, $logfile = null)
	{
		if (!isset(static::$timers[$name]))
		{
			static::$timers[$name] = new static($name, $logfile);
		}

		return static::$timers[$name];
	}

	/**
	 * Start the timer with capturing the current micro-timestamp
	 *
	 * @param   string  $note  The note for the process started which this timer is about tracking
	 *
	 * @return  string  The message text
	 *
	 * @since   1.4.7
	 */
	public function start($note)
	{
		$now      = microtime(true);
		$dateTime = \JFactory::getDate()->format('Y-m-d H:i:s T'); // \JHtml::_('date', 'now', 'Y-m-d H:i:s T');

		$this->running = true;
		$this->start   = $now;
		$this->last    = $now;
		$this->end     = 0;

		return $this->log(\JText::sprintf('COM_SELLACIOUS_TIMER_START', $this->name, $this->start, $dateTime, $note), true);
	}

	/**
	 * Hit the timer with capturing the current micro-timestamp to indicate progress of current tracked process
	 *
	 * @param   int     $count     The progress level reached
	 * @param   int     $interval  After each <var>$interval</var> number of progress the log will be written and not on every hit
	 * @param   string  $note      The note for the tracked running process
	 *
	 * @return  string  The message text
	 *
	 * @since   1.4.7
	 */
	public function hit($count, $interval, $note)
	{
		if ($interval == 0 || ($count > 0 && $count % $interval == 0))
		{
			$last = $this->last;
			$now  = microtime(true);
			$time = \JFactory::getDate()->format('H:i:s T');

			$this->last = $now;

			$iDuration = round($now - $last, 4);

			return $this->log(\JText::sprintf('COM_SELLACIOUS_TIMER_HIT', $count, $now, $time, $iDuration, $note), true);
		}

		return null;
	}

	/**
	 * End the timer with capturing the current micro-timestamp when the job is finished
	 *
	 * @param   string  $note  The note for the process which this timer is tracking
	 *
	 * @return  string  The message text
	 *
	 * @since   1.4.7
	 */
	public function stop($note)
	{
		$last     = $this->last;
		$now      = microtime(true);
		$dateTime = \JFactory::getDate()->format('Y-m-d H:i:s T');

		$this->last = $now;
		$this->end  = $now;
		$iDuration  = round($now - $last, 4);

		$msg = $this->log(\JText::sprintf('COM_SELLACIOUS_TIMER_END', $this->name, $now, $dateTime, $iDuration, $note), true);

		$this->log(\JText::sprintf('COM_SELLACIOUS_MEMORY_USAGE', number_format(memory_get_peak_usage(true))));

		$this->running = false;

		return $msg;
	}

	/**
	 * Stop the timer with capturing the current micro-timestamp when the job is interrupted due to some error
	 *
	 * @param   string  $note  The note for the process which this timer is tracking
	 *
	 * @return  string  The message text
	 *
	 * @since   1.4.7
	 */
	public function interrupt($note)
	{
		if ($this->running)
		{
			$now      = microtime(true);
			$dateTime = \JFactory::getDate()->format('Y-m-d H:i:s T');

			$this->last = $now;
			$this->end  = $now;

			$msg = $this->log(\JText::sprintf('COM_SELLACIOUS_TIMER_INTERRUPT', $this->name, $now, $dateTime, $note), true);
		}
		else
		{
			$msg = $this->log(\JText::sprintf('COM_SELLACIOUS_TIMER_FAIL_START', $this->name, $note));
		}

		$this->log(\JText::sprintf('COM_SELLACIOUS_MEMORY_USAGE', number_format(memory_get_peak_usage(true))));

		$this->running = false;

		return $msg;
	}

	/**
	 * Whether the process is running
	 *
	 * @return  bool
	 *
	 * @since   1.4.7
	 */
	public function isRunning()
	{
		return $this->running;
	}

	/**
	 * Add the given log entry to the log file
	 *
	 * @param   string  $entry  The log entry message
	 * @param   bool    $ts     Whether to include timestamp since start
	 *
	 * @return  string  The message text
	 *
	 * @since   1.4.7
	 */
	public function log($entry, $ts = false)
	{
		$now       = microtime(true);
		$tDuration = round($now - $this->start, 4);

		$message = ($ts ? 'T = ' . $tDuration . ' – ' : '') . str_replace("\n", ' ', $entry);

		file_put_contents($this->log, $message . PHP_EOL, FILE_APPEND);

		return $message;
	}
}
