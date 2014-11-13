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

use TYPO3\Flow\Security\AccountRepository;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Party\Domain\Model\Person;
use TYPO3\Flow\Annotations as Flow;

/**
 * Render user initials for a given username
 */
class UserInitialsViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var AccountRepository
	 */
	protected $accountRepository;


	/**
	 * render user initials or an abbreviated name for a given username. If the account was deleted, use the username as fallback.
	 *
	 * @param string $format
	 */
	public function render($format = 'initials') {
		if (!in_array($format, array('fullFirstName', 'initials'))) {
			throw new \InvalidArgumentException(sprintf('Format "%s" given to history:userInitials(), only supporting "fullFirstName" and "initials".', $format), 1415705861);
		}


		$accountIdentifier = $this->renderChildren();

		// TODO: search by credential source is still needed
		/* @var $account \TYPO3\Flow\Security\Account */
		$account = $this->accountRepository->findOneByAccountIdentifier($accountIdentifier);

		if ($account === NULL) {
			return $accountIdentifier;
		}

		/* @var $person Person */
		$person = $account->getParty();

		if ($person === NULL || $person->getName() === NULL) {
			return $accountIdentifier;
		}

		switch ($format) {
			case 'initials':
				$initials = mb_substr($person->getName()->getFirstName(), 0, 1) . mb_substr($person->getName()->getLastName(), 0, 1);
				return $initials;
			case 'fullFirstName':
				return $person->getName()->getFirstName() . ' ' . mb_substr($person->getName()->getLastName(), 0, 1) . '.';
		}
	}
}