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

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * A basic event
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
	 * @ORM\Column(columnDefinition="INT(11) NOT NULL AUTO_INCREMENT UNIQUE")
	 * @var integer
	 */
	protected $uid;

	/**
	 * What was this event about? A string constant describing this. Required.
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
	 * Payload of the event. TODO: externalize to new object.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * The parent event, if exists. E.g. if a "move node" operation triggered a bunch of other events.
	 *
	 * @var Event
	 * @ORM\ManyToOne
	 */
	protected $parentEvent;

	/**
	 * @Flow\Transient
	 * @var integer
	 */
	protected $numberOfFollowingSimilarEvents = 0;

	function __construct($eventType, $data, $user = NULL, Event $parentEvent = NULL) {
		$this->timestamp = new \DateTime();
		$this->eventType = $eventType;
		$this->data = $data;
		$this->user = $user;
		$this->parentEvent = $parentEvent;
	}


	/**
	 * @return string
	 */
	public function getContext() {
		return $this->context;
	}

	/**
	 * @return object
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * @return string
	 */
	public function getEventType() {
		return $this->eventType;
	}

	/**
	 * @return Event
	 */
	public function getParentEvent() {
		return $this->parentEvent;
	}

	/**
	 * @return \DateTime
	 */
	public function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * @return string
	 */
	public function getUser() {
		return $this->user;
	}

	public function addSimilarEvent() {
		$this->numberOfFollowingSimilarEvents++;
	}

	/**
	 * @return int
	 */
	public function getNumberOfFollowingSimilarEvents() {
		return $this->numberOfFollowingSimilarEvents;
	}
}
