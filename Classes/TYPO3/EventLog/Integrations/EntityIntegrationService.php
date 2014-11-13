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
use Doctrine\ORM\Event\OnFlushEventArgs;
use TYPO3\Eel\CompilingEvaluator;
use TYPO3\Eel\Utility;
use TYPO3\EventLog\Domain\Service\EventEmittingService;
use TYPO3\Flow\Annotations as Flow;

/**
 * Monitors entity changes
 *
 * TODO: Update/Delete of Entities
 *
 * @Flow\Scope("singleton")
 */
class EntityIntegrationService extends AbstractIntegrationService {

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

	/**
	 * @Flow\Inject(lazy=FALSE)
	 * @var CompilingEvaluator
	 */
	protected $eelEvaluator;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @param array $settings
	 */
	public function injectSettings(array $settings) {
		$this->settings = $settings;
	}

	/**
	 * Initializes the persistence manager, called by Flow.
	 *
	 * @return void
	 */
	public function initializeObject() {
		/* @var $entityManager EntityManager */
		$entityManager = $this->entityManager;
		$entityManager->getEventManager()->addEventListener(array(\Doctrine\ORM\Events::onFlush), $this);
	}

	/**
	 * Dummy method which is called in a prePersist signal. If we remove that, this object is never instanciated and thus
	 * cannot hook into the Doctrine EntityManager.
	 */
	public function dummyMethodToEnsureInstanceExists() {
		// intentionally empty
	}


	public function onFlush(OnFlushEventArgs $eventArgs) {
		$entityManager = $eventArgs->getEntityManager();
		$unitOfWork = $entityManager->getUnitOfWork();

		foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
			$className = get_class($entity);
			if (isset($this->settings['monitorEntities'][$className])) {
				$entityMonitoringConfiguration = $this->settings['monitorEntities'][$className];

				if (isset($entityMonitoringConfiguration['events']['created'])) {
					$this->initializeUser();
					$data = array();
					foreach ($entityMonitoringConfiguration['data'] as $key => $eelExpression) {
						$data[$key] = Utility::evaluateEelExpression($eelExpression, $this->eelEvaluator, array('entity' => $entity));
					}

					/* @var $entityToPersist \TYPO3\Flow\Security\Account */
					$event = $this->eventEmittingService->emit($entityMonitoringConfiguration['events']['created'], $data);
					$unitOfWork->computeChangeSet($entityManager->getClassMetadata('TYPO3\EventLog\Domain\Model\Event'), $event);
				}
			}
		}

		foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
		}

		foreach ($unitOfWork->getScheduledEntityDeletions() as $entity) {

		}

		foreach ($unitOfWork->getScheduledCollectionDeletions() as $col) {

		}

		foreach ($unitOfWork->getScheduledCollectionUpdates() as $col) {

		}
	}
}