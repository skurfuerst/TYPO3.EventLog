<?php
namespace TYPO3\EventLog\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.EventLog".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


class EventsOnDate {

	/**
	 * @var \DateTime
	 */
	protected $day;

	protected $events = array();

	public function __construct(\DateTime $day) {
		$this->day = $day;
	}

	public function add(Event $event) {
		$this->events[] = $event;
	}

	/**
	 * @return array
	 */
	public function getEvents() {
		return $this->events;
	}

	/**
	 * @return \DateTime
	 */
	public function getDay() {
		return $this->day;
	}
} 