<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 11.11.14
 * Time: 12:11
 */

namespace TYPO3\EventLog\Domain\Model;


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