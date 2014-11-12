<?php
namespace TYPO3\EventLog\Integrations;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.EventLog".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\EventLog\Domain\Service\EventEmittingService;
use TYPO3\Flow\Annotations as Flow;

abstract class AbstractIntegrationService {


	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;


	/**
	 * @Flow\Inject
	 * @var EventEmittingService
	 */
	protected $eventEmittingService;

	protected function initUser() {
		if ($this->securityContext->canBeInitialized()) {
			$account = $this->securityContext->getAccount();
			if ($account !== NULL) {
				$this->eventEmittingService->setCurrentUser($account->getAccountIdentifier());
			}
		}
	}

} 