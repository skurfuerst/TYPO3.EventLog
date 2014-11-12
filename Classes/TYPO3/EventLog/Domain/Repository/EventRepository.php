<?php
namespace TYPO3\EventLog\Domain\Repository;

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
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Flow\Persistence\Repository;
use TYPO3\Flow\Persistence\RepositoryInterface;
use TYPO3\Flow\Reflection\ObjectAccess;

/**
 * The repository for events
 *
 * @Flow\Scope("singleton")
 */
class EventRepository extends Repository {

	/**
	 * @var array
	 */
	protected $defaultOrderings = array(
		'uid' => QueryInterface::ORDER_ASCENDING
	);

	public function findRelevantEvents() {

		$q = $this->createQuery();
		// workaround: query should have a getQueryBuilder() method.
		/* @var $qb \Doctrine\ORM\QueryBuilder */
		$qb = ObjectAccess::getProperty($q, 'queryBuilder', TRUE);

		$qb->andWhere(
			$qb->expr()->isNull('e.parentEvent')
		);

		$qb->orderBy('e.uid', 'DESC');

		return $q->execute();
	}
}