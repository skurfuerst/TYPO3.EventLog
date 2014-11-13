<?php
namespace TYPO3\EventLog\TypoScript;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Neos".            *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\TypoScript\TypoScriptObjects\AbstractTypoScriptObject;

/**
 * A migration helper for TYPO3.Neos:Template
 *
 * @deprecated This implementation is only used for a migration from the TYPO3.Neos:Template TypoScript object
 * @Flow\Scope("prototype")
 */
class DebugImplementation extends AbstractTypoScriptObject {

	/**
	 * Evaluate this TypoScript object and return the result
	 *
	 * @return mixed
	 */
	public function evaluate() {
		return \TYPO3\Flow\var_dump($this->tsRuntime->getCurrentContext(), '', TRUE);
	}
}
