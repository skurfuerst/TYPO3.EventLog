<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.11.14
 * Time: 11:27
 */

namespace TYPO3\EventLog\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Utility\Arrays;
use TYPO3\Neos\Domain\Repository\SiteRepository;
use TYPO3\Neos\Service\UserService;
use TYPO3\TYPO3CR\Domain\Model\NodeInterface;
use TYPO3\TYPO3CR\Domain\Service\ContextFactoryInterface;

/**
 * Class NodeEvent
 *
 * @Flow\Entity
 */
class NodeEvent extends Event {

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


	public function setNode(NodeInterface $node) {
		$this->nodeIdentifier = $node->getIdentifier();
		$this->workspaceName = $node->getContext()->getWorkspaceName();
		$this->dimension = $node->getContext()->getDimensions();

		$this->data = Arrays::arrayMergeRecursiveOverrule($this->data, array(
			'nodeContextPath' => $node->getContextPath(),
			'nodeLabel' => $node->getLabel(),
			'nodeType' => $node->getNodeType()->getName(),
			'site' => $this->persistenceManager->getIdentifierByObject($node->getContext()->getCurrentSite())
		));


		$node = self::getClosestDocumentNode($node);

		if ($node !== NULL) {
			$this->documentNodeIdentifier = $node->getIdentifier();
			$this->data = Arrays::arrayMergeRecursiveOverrule($this->data, array(
				'documentNodeContextPath' => $node->getContextPath(),
				'documentNodeLabel' => $node->getLabel(),
				'documentNodeType' => $node->getNodeType()->getName()
			));
		}
	}

	public static function getClosestDocumentNode(NodeInterface $node) {
		while ($node !== NULL && !$node->getNodeType()->isOfType('TYPO3.Neos:Document')) {
			$node = $node->getParent();
		}
		return $node;
	}

	public function getDocumentNode() {
		$context = $this->contextFactory->create(array(
			'workspaceName' => $this->userService->getCurrentWorkspace()->getName(),
	 		'dimensions' => $this->dimension,
			'currentSite' => $this->siteRepository->findByIdentifier($this->data['site']),
			'invisibleContentShown' => TRUE
		));
		return $context->getNodeByIdentifier($this->documentNodeIdentifier);
	}

	public function getNode() {
		$context = $this->contextFactory->create(array(
			'workspaceName' => $this->userService->getCurrentWorkspace()->getName(),
			'dimensions' => $this->dimension,
			'currentSite' => $this->siteRepository->findByIdentifier($this->data['site']),
			'invisibleContentShown' => TRUE
		));

		return $context->getNodeByIdentifier($this->nodeIdentifier);
	}
} 