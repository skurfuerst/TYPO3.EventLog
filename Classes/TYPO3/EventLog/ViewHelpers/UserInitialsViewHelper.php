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

class UserInitialsViewHelper extends AbstractViewHelper {

	/**
	 * @Flow\Inject
	 * @var AccountRepository
	 */
	protected $accountRepository;


	/**
	 * render
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