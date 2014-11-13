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

use TYPO3\EventLog\Domain\Model\Event;
use TYPO3\EventLog\Domain\Model\EventsOnDate;
use TYPO3\EventLog\Domain\Model\NodeEvent;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\EventLog\Domain\Repository\EventRepository;
use TYPO3\Neos\Controller\Module\AbstractModuleController;

class HistoryModuleController extends AbstractModuleController {

	/**
	 * @Flow\Inject
	 * @var EventRepository
	 */
	protected $eventRepository;

	protected $defaultViewObjectName = 'TYPO3\TypoScript\View\TypoScriptView';

	public function indexAction() {
		$events = $this->eventRepository->findRelevantEvents()->toArray();

		$eventsByDate = array();
		foreach ($events as $event) {
			if ($event instanceof NodeEvent && $event->getWorkspaceName() !== 'live') {
				continue;
			}
			/* @var $event Event */
			$day = $event->getTimestamp()->format('Y-m-d');
			if (!isset($eventsByDate[$day])) {
				$eventsByDate[$day] = new EventsOnDate($event->getTimestamp());
			}

			/* @var $eventsOnThisDay EventsOnDate */
			$eventsOnThisDay = $eventsByDate[$day];
			$eventsOnThisDay->add($event);
		}

		$this->view->assign('eventsByDate', $eventsByDate);
	}
}