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

use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
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