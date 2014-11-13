<?php
namespace TYPO3\EventLog\Eel;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.EventLog".        *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use Doctrine\Common\Collections\Collection;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Eel\ProtectedContextAwareInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * Some Functional Programming Array helpers for Eel contexts
 *
 * @Flow\Proxy(false)
 */
class ArrayHelper implements ProtectedContextAwareInterface {

	public function filter($array, $filterProperty) {
		return $this->filterInternal($array, $filterProperty, FALSE);
	}


	public function filterNegated($array, $filterProperty) {
		return $this->filterInternal($array, $filterProperty, TRUE);
	}

	protected function filterInternal($array, $filterProperty, $negate) {
		if (is_object($array) && $array instanceof Collection) {
			$array = $array->toArray();
		}

		return array_filter($array, function($element) use ($filterProperty, $negate) {
			$result = (boolean)ObjectAccess::getPropertyPath($element, $filterProperty);
			if ($negate) {
				$result = !$result;
			}
			return $result;
		});
	}


	public function groupBy($array, $groupingKey) {
		$result = array();
		foreach ($array as $element) {
			$result[ObjectAccess::getPropertyPath($element, $groupingKey)][] = $element;
		}
		return $result;
	}


	/**
	 * All methods are considered safe
	 *
	 * @param string $methodName
	 * @return boolean
	 */
	public function allowsCallOfMethod($methodName) {
		return TRUE;
	}

}
