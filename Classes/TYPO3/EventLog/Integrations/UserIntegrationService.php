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

use Doctrine\ORM\EntityManager;
use TYPO3\EventLog\Domain\Model\NodeEvent;
use TYPO3\EventLog\Domain\Service\EventEmittingService;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Model\Workspace;
use TYPO3\TYPO3CR\Domain\Service\Context;

/**
 * The repository for events
 *
 * @Flow\Scope("singleton")
 */
class UserIntegrationService extends AbstractIntegrationService {

	/**
	 * Doctrine's Entity Manager. Note that "ObjectManager" is the name of the related
	 * interface ...
	 *
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * @Flow\Inject
	 * @var EventEmittingService
	 */
	protected $eventEmittingService;

	const ACCOUNT_CREATED = 'ACCOUNT_CREATED';

	public function checkUserAddedOrModified() {
		/* @var $entityManager EntityManager */
		$entityManager = $this->entityManager;
		foreach ($entityManager->getUnitOfWork()->getIdentityMap() as $className => $entities) {
			if ($className === 'TYPO3\Flow\Security\Account') {

				foreach ($entities as $entityToPersist) {
					if ($entityManager->getUnitOfWork()->isScheduledForInsert($entityToPersist)) {
						$this->initUser();
						/* @var $entityToPersist \TYPO3\Flow\Security\Account */
						$this->eventEmittingService->emit(self::ACCOUNT_CREATED, array('identifier' => $entityToPersist->getAccountIdentifier()));
					}
				}

				break;
			}
		}
	}
}