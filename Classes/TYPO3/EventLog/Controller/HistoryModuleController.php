<?php
namespace TYPO3\EventLog\Controller;

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
use TYPO3\EventLog\Domain\Repository\EventRepository;

class HistoryModuleController extends \TYPO3\Neos\Controller\Module\AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var EventRepository
	 */
	protected $eventRepository;

	public function indexAction() {
		$events = $this->eventRepository->findRelevantEvents()->toArray();
		$events = array_reverse($events);

		$processedEvents = array(
			array_shift($events)
		);

		foreach ($events as $event) {
			$lastProcessedEvent = end($processedEvents);
			if ($lastProcessedEvent->getEventType() === $event->getEventType() && $lastProcessedEvent->getTimestamp() == $event->getTimestamp()) {
				$lastProcessedEvent->addSimilarEvent();
			} else {
				$processedEvents[] = $event;
			}
		}

		$processedEvents = array_reverse($processedEvents);

		$this->view->assign('events', $processedEvents);
	}
}