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
class TYPO3CRIntegrationService {

	/**
	 * @Flow\Inject
	 * @var EventEmittingService
	 */
	protected $eventEmittingService;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Context
	 */
	protected $securityContext;

	/**
	 * @Flow\Inject
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 */
	protected $entityManager;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;


	const NODE_ADDED = 'NODE_ADDED';
	const NODE_UPDATED = 'NODE_UPDATED';
	const NODE_REMOVED = 'NODE_REMOVED';
	const DOCUMENT_PUBLISHED = 'NODE_PUBLISHED';
	const NODE_COPY = 'NODE_COPY';
	const NODE_DISCARDED = 'NODE_DISCARDED';
	const NODE_ADOPT = 'NODE_ADOPT';

	protected $changedNodes = array();

	public function nodeAdded(NodeInterface $node) {
		/* @var $nodeEvent NodeEvent */
		$nodeEvent = $this->eventEmittingService->emit(self::NODE_ADDED, array(), 'TYPO3\EventLog\Domain\Model\NodeEvent');
		$nodeEvent->setNode($node);
	}
	public function nodeUpdated(NodeInterface $node) {
		if (!isset($this->changedNodes[$node->getContextPath()])) {
			$this->changedNodes[$node->getContextPath()] = array(
				'node' => $node
			);
		}
	}

	public function nodePropertyChanged(NodeInterface $node, $propertyName, $oldValue, $value) {
		if ($oldValue === $value) {
			return;
		}
		if (!isset($this->changedNodes[$node->getContextPath()])) {
			$this->changedNodes[$node->getContextPath()] = array(
				'node' => $node
			);
		}

		$this->changedNodes[$node->getContextPath()]['old'][$propertyName] = $oldValue;
		$this->changedNodes[$node->getContextPath()]['new'][$propertyName] = $value;
	}

	public function nodeRemoved(NodeInterface $node) {
		/* @var $nodeEvent NodeEvent */
		$nodeEvent = $this->eventEmittingService->emit(self::NODE_REMOVED, array(), 'TYPO3\EventLog\Domain\Model\NodeEvent');
		$nodeEvent->setNode($node);
	}

	public function beforeNodePublishing(NodeInterface $node, Workspace $targetWorkspace) {

	}



	public function nodeDiscarded(NodeInterface $node) {
		$this->eventEmittingService->emit(self::NODE_DISCARDED, array('node' => $node->getContextPath()));
	}

	protected $currentlyCopying = FALSE;
	public function beforeNodeCopy(NodeInterface $sourceNode, NodeInterface $targetParentNode) {
		if ($this->currentlyCopying) {
			throw new \Exception('TODO: already copying...');
		}

		$this->currentlyCopying = TRUE;

		/* @var $nodeEvent NodeEvent */
		$nodeEvent = $this->eventEmittingService->emit(self::NODE_COPY, array(
			'copiedInto' => $targetParentNode->getContextPath()
		), 'TYPO3\EventLog\Domain\Model\NodeEvent');
		$nodeEvent->setNode($sourceNode);
		$this->eventEmittingService->pushContext();
	}

	public function nodeCopied(NodeInterface $copiedNode, NodeInterface $targetParentNode) {
		if ($this->currentlyCopying === FALSE) {
			throw new \Exception('TODO: copying not started');
		}
		$this->currentlyCopying = FALSE;
		$this->eventEmittingService->popContext();
	}

	protected $currentlyAdopting = 0;
	public function beforeAdoptNode(NodeInterface $node, Context $context, $recursive) {
		$this->initUser();
		if ($this->currentlyAdopting === 0) {
			/* @var $nodeEvent NodeEvent */
			$nodeEvent = $this->eventEmittingService->emit(self::NODE_ADOPT, array(
				'targetWorkspace' => $context->getWorkspaceName(),
				'targetDimensions' => $context->getTargetDimensions(),
				'recursive' => $recursive
			), 'TYPO3\EventLog\Domain\Model\NodeEvent');
			$nodeEvent->setNode($node);
			$this->eventEmittingService->pushContext();
		}

		$this->currentlyAdopting++;
	}

	public function afterAdoptNode(NodeInterface $node, Context $context, $recursive) {
		$this->currentlyAdopting--;
		if ($this->currentlyAdopting === 0) {
			$this->eventEmittingService->popContext();
		}
	}

	protected function initUser() {
		if ($this->securityContext->canBeInitialized()) {
			$account = $this->securityContext->getAccount();
			if ($account !== NULL) {
				$this->eventEmittingService->setCurrentUser($account->getAccountIdentifier());
			}
		}
	}

	public function generateNodeEvents() {
		$this->initUser();

		foreach ($this->changedNodes as $nodePath => $data) {
			$node = $data['node'];
			unset($data['node']);
			/* @var $nodeEvent NodeEvent */
			$nodeEvent = $this->eventEmittingService->emit(self::NODE_UPDATED, $data, 'TYPO3\EventLog\Domain\Model\NodeEvent');
			$nodeEvent->setNode($node);
		}
	}


	protected $scheduledNodeEventUpdates = array();

	public function afterNodePublishing(NodeInterface $node, Workspace $targetWorkspace) {
		$documentNode = NodeEvent::getClosestDocumentNode($node);

		$this->scheduledNodeEventUpdates[$documentNode->getContextPath()] = array(

			'workspaceName' => $node->getContext()->getWorkspaceName(),
			'nestedNodeIdentifiersWhichArePublished' => array(),
			'targetWorkspace' => $targetWorkspace->getName(),
			'documentNode' => $documentNode
		);

		$this->scheduledNodeEventUpdates[$documentNode->getContextPath()]['nestedNodeIdentifiersWhichArePublished'][] = $node->getIdentifier();
	}


	public function updateEventsAfterPublish() {

		/** @var $entityManager EntityManager */
		$entityManager = $this->entityManager;

		foreach ($this->scheduledNodeEventUpdates as $documentPublish) {

			/* @var $nodeEvent NodeEvent */
			$nodeEvent = $this->eventEmittingService->emit(self::DOCUMENT_PUBLISHED, array(), 'TYPO3\EventLog\Domain\Model\NodeEvent');
			$nodeEvent->setNode($documentPublish['documentNode']);
			$this->persistenceManager->whitelistObject($nodeEvent);
			$this->persistenceManager->persistAll(TRUE);

			$parentEventIdentifier = $this->persistenceManager->getIdentifierByObject($nodeEvent);

			$qb = $entityManager->createQueryBuilder();
			$qb->update('TYPO3\EventLog\Domain\Model\NodeEvent', 'e')
				->set('e.parentEvent', $qb->expr()->literal($parentEventIdentifier))
				->where('e.parentEvent IS NULL')
				->andWhere('e.workspaceName = :workspaceName')
				->setParameter('workspaceName', $documentPublish['workspaceName'])
				->andWhere('e.documentNodeIdentifier = :documentNodeIdentifier')
				->setParameter('documentNodeIdentifier', $documentPublish['documentNode']->getIdentifier())
				->andWhere('e != :parentEvent')
				->setParameter('parentEvent', $parentEventIdentifier)
				->andWhere('e.eventType != :publishedEventType')
				->setParameter('publishedEventType', self::DOCUMENT_PUBLISHED)
			   ->getQuery()->execute();
		}

		$this->scheduledNodeEventUpdates = array();
	}
}