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

use Doctrine\Common\Collections\ArrayCollection;
use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Base class for generic events
 *
 * @Flow\Entity
 * @ORM\InheritanceType("SINGLE_TABLE")
 */
class Event {

	/**
	 * When was this event?
	 *
	 * @var \DateTime
	 */
	protected $timestamp;

	/**
	 * We introduce an auto_increment column to be able to sort events at the same timestamp
	 *
	 * @ORM\Column(columnDefinition="INT(11) NOT NULL AUTO_INCREMENT UNIQUE")
	 * @var integer
	 */
	protected $uid;

	/**
	 * What was this event about? Is a required string constant.
	 *
	 * @var string
	 */
	protected $eventType;

	/**
	 * The user who triggered this event. Optional.
	 *
	 * @var string
	 * @ORM\Column(nullable=true)
	 */
	protected $user;

	/**
	 * Payload of the event.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * The parent event, if exists. E.g. if a "move node" operation triggered a bunch of other events, or a "publish"
	 *
	 * @var Event
	 * @ORM\ManyToOne
	 */
	protected $parentEvent;

	/**
	 * Child events, of this event
	 *
	 * @var ArrayCollection<TYPO3\EventLog\Domain\Model\Event>
	 * @ORM\OneToMany(targetEntity="TYPO3\EventLog\Domain\Model\Event", mappedBy="parentEvent", cascade="persist")
	 */
	protected $childEvents;

	/**
	 * Create a new event
	 *
	 * @param string $eventType
	 * @param array $data
	 * @param string $user
	 * @param Event $parentEvent
	 */
	function __construct($eventType, $data, $user = NULL, Event $parentEvent = NULL) {
		$this->timestamp = new \DateTime();
		$this->eventType = $eventType;
		$this->data = $data;
		$this->user = $user;
		$this->parentEvent = $parentEvent;

		$this->childEvents = new ArrayCollection();

		if ($this->parentEvent !== NULL) {
			$parentEvent->addChildEvent($this);
		}
	}

	/**
	 * @return string
	 */
	public function getEventType() {
		return $this->eventType;
	}

	/**
	 * @return \DateTime
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return Event
	 */
	public function getParentEvent() {
		return $this->parentEvent;
	}

	/**
	 * @return array
	 */
	public function getChildEvents() {
		return $this->childEvents;
	}

	/**
	 * Add a new child event. is called from the child event's constructor.
	 *
	 * @param Event $childEvent
	 */
	protected function addChildEvent(Event $childEvent) {
		$this->childEvents->add($childEvent);
	}
}
