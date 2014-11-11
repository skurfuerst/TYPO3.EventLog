<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.11.14
 * Time: 15:59
 */

namespace TYPO3\EventLog\Integrations;

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