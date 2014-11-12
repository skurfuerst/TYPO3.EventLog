<?php
namespace TYPO3\EventLog\ViewHelpers;

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
use TYPO3\EventLog\Domain\Model\NodeEvent;
use TYPO3\EventLog\Integrations\TYPO3CRIntegrationService;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Flow\Annotations as Flow;

class CalculateSubEventForPublishingViewHelper extends AbstractViewHelper {


	/**
	 * @param Event $event
	 * @return mixed
	 */
	public function render(Event $event) {
		$result = $this->findMostRelevantSubEvent($event);

		$this->templateVariableContainer->add('subType', $result[0]);
		$this->templateVariableContainer->add('subEvent', $result[1]);
		$output = $this->renderChildren();
		$this->templateVariableContainer->remove('subType');
		$this->templateVariableContainer->remove('subEvent');
		return $output;
	}


	/**
	 * Only relevant for PUBLISH events!
	 */
	public function findMostRelevantSubEvent(Event $event) {
		$documentEvents = array();
		$contentEvents = array();

		foreach ($event->getChildEvents() as $event) {
			/* @var $event NodeEvent */

			if ($event->isRelatedToDocumentNode()) {
				$documentEvents[$event->getEventType()][] = $event;
			} else {
				$contentEvents[$event->getEventType()][] = $event;
			}
		}

		if (isset($documentEvents[TYPO3CRIntegrationService::NODE_ADOPT])) {
			return array('adoptedDocument', reset($documentEvents[TYPO3CRIntegrationService::NODE_ADOPT]));
		}

		if (isset($documentEvents[TYPO3CRIntegrationService::NODE_LABEL_CHANGED])) {
			return array('documentLabelChanged', reset($documentEvents[TYPO3CRIntegrationService::NODE_LABEL_CHANGED]));
		}

		if (isset($documentEvents[TYPO3CRIntegrationService::NODE_COPY])) {
			return array('copiedDocument', reset($documentEvents[TYPO3CRIntegrationService::NODE_COPY]));
		}

		if (isset($documentEvents[TYPO3CRIntegrationService::NODE_UPDATED])) {
			return array('updatedDocument', reset($documentEvents[TYPO3CRIntegrationService::NODE_UPDATED]));
		}

		if (count($documentEvents) == 0 && count($contentEvents) > 0) {
			return array('changedContent', NULL);
		}

	}

}