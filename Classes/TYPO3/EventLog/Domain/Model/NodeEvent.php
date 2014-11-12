<?php
namespace TYPO3\EventLog\Domain\Model;

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
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\Neos\Service\UserService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;

/**
 * A specific event which is used for TYPO3CR Nodes (i.e. content).
 *
 * @Flow\Entity
 */
class NodeEvent extends Event {

	/**
	 * the node identifier which was created/modified/...
	 *
	 * @var string
	 */
	protected $nodeIdentifier;

	/**
	 * the document node identifier on which the action took place. is equal to NodeIdentifier if the action happened on documentNodes
	 *
	 * @var string
	 */
	protected $documentNodeIdentifier;

	/**
	 * the workspace name where the action took place
	 *
	 * @var string
	 */
	protected $workspaceName;

	/**
	 * the dimension values for that event
	 *
	 * @var array
	 */
	protected $dimension;

	/**
	 * @Flow\Inject
	 * @var UserService
	 */
	protected $userService;

	/**
	 * @Flow\Inject
	 * @var ContextFactoryInterface
	 */
	protected $contextFactory;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @Flow\Inject
	 * @var SiteRepository
	 */
	protected $siteRepository;



	/**
	 * @return string
	 */
	public function getWorkspaceName() {
		return $this->workspaceName;
	}

	public function isRelatedToDocumentNode() {
		return $this->documentNodeIdentifier === $this->nodeIdentifier;
	}

	/**
	 * @return string
	 */
	public function getDocumentNodeIdentifier() {
		return $this->documentNodeIdentifier;
	}

	/**
	 * @return string
	 */
	public function getNodeIdentifier() {
		return $this->nodeIdentifier;
	}

	/**
	 * Set the "context node" this operation was working on.
	 *
	 * @param NodeInterface $node
	 */
	public function setNode(NodeInterface $node) {
		$this->nodeIdentifier = $node->getIdentifier();
		$this->workspaceName = $node->getContext()->getWorkspaceName();
		$this->dimension = $node->getContext()->getDimensions();

		$siteIdentifier = NULL;
		if ($node->getContext()->getCurrentSite() !== NULL) {
			$siteIdentifier = $this->persistenceManager->getIdentifierByObject($node->getContext()->getCurrentSite());
		}
		$this->data = Arrays::arrayMergeRecursiveOverrule($this->data, array(
			'nodeContextPath' => $node->getContextPath(),
			'nodeLabel' => $node->getLabel(),
			'nodeType' => $node->getNodeType()->getName(),
			'site' => $siteIdentifier
		));

		$node = self::getClosestAggregateNode($node);

		if ($node !== NULL) {
			$this->documentNodeIdentifier = $node->getIdentifier();
			$this->data = Arrays::arrayMergeRecursiveOverrule($this->data, array(
				'documentNodeContextPath' => $node->getContextPath(),
				'documentNodeLabel' => $node->getLabel(),
				'documentNodeType' => $node->getNodeType()->getName()
			));
		}
	}

	/**
	 * Override the workspace name. *MUST* be called after setNode(), else it won't have an effect.
	 *
	 * @param string $workspaceName
	 */
	public function setWorkspaceName($workspaceName) {
		$this->workspaceName = $workspaceName;
	}

	/**
	 * @param NodeInterface $node
	 * @return NodeInterface
	 */
	public static function getClosestAggregateNode(NodeInterface $node) {
		while ($node !== NULL && !$node->getNodeType()->isAggregate()) {
			$node = $node->getParent();
		}
		return $node;
	}

	/**
	 * @return NodeInterface the closest document node, if it can be resolved
	 */
	public function getDocumentNode() {
		$context = $this->contextFactory->create(array(
			'workspaceName' => $this->userService->getCurrentWorkspace()->getName(),
	 		'dimensions' => $this->dimension,
			'currentSite' => $this->siteRepository->findByIdentifier($this->data['site']),
			'invisibleContentShown' => TRUE
		));
		return $context->getNodeByIdentifier($this->documentNodeIdentifier);
	}

	/**
	 * @return NodeInterface the node itself, if it can be resolved
	 */
	public function getNode() {
		$context = $this->contextFactory->create(array(
			'workspaceName' => $this->userService->getCurrentWorkspace()->getName(),
			'dimensions' => $this->dimension,
			'currentSite' => $this->siteRepository->findByIdentifier($this->data['site']),
			'invisibleContentShown' => TRUE
		));

		return $context->getNodeByIdentifier($this->nodeIdentifier);
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return sprintf('NodeEvent[%s, %s]', $this->eventType, $this->nodeIdentifier);
	}
} 