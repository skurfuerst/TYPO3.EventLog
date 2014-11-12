<?php
namespace TYPO3\EventLog\Domain\Service;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.EventLog".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\EventLog\Domain\Model\Event;
use TYPO3\EventLog\Domain\Repository\EventRepository;
use TYPO3\Flow\Annotations as Flow;

/**
 * The repository for events
 *
 * @Flow\Scope("singleton")
 */
class EventEmittingService {

	protected $lastEmittedEvent;

	protected $eventContext = array();

	/**
	 * @var string
	 */
	protected $currentUser = NULL;

	/**
	 * @Flow\Inject
	 * @var EventRepository
	 */
	protected $eventRepository;

	public function pushContext() {
		if ($this->lastEmittedEvent === NULL) {
			throw new \InvalidArgumentException('pushContext() can only be called directly after an invocation of emit().', 1415353980);
		}

		$this->eventContext[] = $this->lastEmittedEvent;
	}

	public function popContext() {
		if (count($this->eventContext) > 0) {
			array_pop($this->eventContext);
		} else {
			throw new \InvalidArgumentException('popContext() can only be called if the context has been pushed beforehand.', 1415354224);
		}
	}

	/**
	 * @param string $currentUser
	 */
	public function setCurrentUser($currentUser) {
		$this->currentUser = $currentUser;
	}


	public function emit($eventType, array $data, $eventClassName = 'TYPO3\EventLog\Domain\Model\Event') {
		$event = new $eventClassName($eventType, $data, $this->currentUser, $this->getCurrentContext());
		$this->eventRepository->add($event);
		$this->lastEmittedEvent = $event;

		return $event;
	}

	protected function getCurrentContext() {
		if (count($this->eventContext) > 0) {
			return end($this->eventContext);
		} else {
			return NULL;
		}
	}

	public function update($nodeEvent) {
		$this->eventRepository->update($nodeEvent);
	}
}