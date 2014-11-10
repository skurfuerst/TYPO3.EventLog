<?php
namespace TYPO3\EventLog;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Package\Package as BasePackage;

/**
 * The TYPO3 Neos Package
 */
class Package extends BasePackage {

	/**
	 * @param \TYPO3\Flow\Core\Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(\TYPO3\Flow\Core\Bootstrap $bootstrap) {
		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeAdded', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodeAdded');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeUpdated', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodeUpdated');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodePropertyChanged', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodePropertyChanged');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeRemoved', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodeRemoved');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'beforeNodeCopy', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'beforeNodeCopy');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Node', 'nodeCopied', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodeCopied');

		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Service\Context', 'beforeAdoptNode', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'beforeAdoptNode');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Service\Context', 'afterAdoptNode', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'afterAdoptNode');

		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Workspace', 'beforeNodePublishing', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'beforeNodePublishing');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Model\Workspace', 'afterNodePublishing', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'afterNodePublishing');
		$dispatcher->connect('TYPO3\Neos\Service\PublishingService', 'nodeDiscarded', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'nodeDiscarded');


		$dispatcher->connect('TYPO3\Flow\Persistence\Doctrine\PersistenceManager', 'beforeAllObjectsPersist', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'generateNodeEvents');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository', 'beforeRepositoryObjectsPersist', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'generateNodeEvents');

		$dispatcher->connect('TYPO3\Flow\Persistence\Doctrine\PersistenceManager', 'allObjectsPersisted', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'updateEventsAfterPublish');
		$dispatcher->connect('TYPO3\TYPO3CR\Domain\Repository\NodeDataRepository', 'repositoryObjectsPersisted', 'TYPO3\EventLog\Integrations\TYPO3CRIntegrationService', 'updateEventsAfterPublish');


		/*$dispatcher->connect('TYPO3\Neos\Domain\Model\Site', 'siteChanged', $flushConfigurationCache);
		$dispatcher->connect('TYPO3\Neos\Domain\Model\Site', 'siteChanged', 'TYPO3\Flow\Mvc\Routing\RouterCachingService', 'flushCaches');*/
	}

}
