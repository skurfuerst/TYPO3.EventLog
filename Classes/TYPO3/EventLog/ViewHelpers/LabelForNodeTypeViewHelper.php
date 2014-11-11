<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.11.14
 * Time: 12:46
 */

namespace TYPO3\EventLog\ViewHelpers;


use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Party\Domain\Model\Person;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\TYPO3CR\Domain\Service\NodeTypeManager;

class LabelForNodeTypeViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var NodeTypeManager
	 */
	protected $nodeTypeManager;

	/**
	 * render
	 */
	public function render() {
		$nodeTypeName = $this->renderChildren();

		if (!$this->nodeTypeManager->hasNodeType($nodeTypeName)) {
			$explodedNodeTypeName = explode(':', $nodeTypeName);
			return end($explodedNodeTypeName);
		}

		$nodeType = $this->nodeTypeManager->getNodeType($nodeTypeName);
		return $nodeType->getLabel();
	}
}