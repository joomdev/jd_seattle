<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 2 or later
 */

namespace FOF30\Event;

defined('_JEXEC') or die;

class Observer
{
	/** @var   Observable  The object to observe */
	protected $subject = null;

	protected $events = null;

	/**
	 * Creates the observer and attaches it to the observable subject object
	 *
	 * @param   Observable $subject The observable object to attach the observer to
	 */
	function __construct(Observable &$subject)
	{
		// Attach this observer to the subject
		$subject->attach($this);

		// Store a reference to the subject object
		$this->subject = $subject;
	}

	/**
	 * Returns the list of events observable by this observer. Set the $this->events array manually for faster
	 * processing, or let this method use reflection to return a list of all public methods.
	 *
	 * @return  array
	 */
	public function getObservableEvents()
	{
		if (is_null($this->events))
		{
            // Assign an empty array to protect us from behaviours without any valid method
            $this->events = array();

			$reflection = new \ReflectionObject($this);
			$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);

			foreach ($methods as $m)
			{
				if ($m->name == 'getObservableEvents')
				{
					continue;
				}

				if ($m->name == '__construct')
				{
					continue;
				}

				$this->events[] = $m->name;
			}
		}

		return $this->events;
	}
}
