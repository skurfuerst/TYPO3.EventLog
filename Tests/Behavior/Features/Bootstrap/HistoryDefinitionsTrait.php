<?php

use Behat\Behat\Context\ExtendedContextInterface;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Exception\PendingException;
use TYPO3\EventLog\Domain\Model\Event;
use TYPO3\Flow\Utility\Arrays;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit_Framework_Assert as Assert;
use Symfony\Component\Yaml\Yaml;
use TYPO3\TYPO3CR\Service\PublishingServiceInterface;

/**
 * A trait with shared step definitions for common use by other contexts
 *
 * Note that this trait requires that the Flow Object Manager must be available via $this->getSubcontext('flow')->getObjectManager().
 */
trait HistoryDefinitionsTrait {


	/**
	 * @BeforeScenario @fixtures
	 * @return void
	 */
	public function resetHistory() {
		$eventRepository = $this->getEventRepository();
		$eventRepository->removeAll();
		$this->getTYPO3CRIntegrationService()->reset();
	}

	/**
	 * @Given /^I have an empty history$/
	 */
	public function iHaveAnEmptyHistory() {
		$this->resetHistory();
		$this->getSubcontext('flow')->persistAll();
	}

	/**
	 * @Then /^I should have the following history entries:$/
	 * @param TableNode $table
	 */
	public function iShouldHaveTheFollowingHistoryEntries(TableNode $table) {

		$allEvents = $this->getEventRepository()->findAll()->toArray();
		$eventsByInternalId = array();
		$unmatchedParentEvents = array();

		foreach ($table->getHash() as $i => $row) {
			$event = $allEvents[$i];
			/* @var $event \TYPO3\EventLog\Domain\Model\NodeEvent */
			foreach ($row as $rowName => $rowValue) {
				switch ($rowName) {
					case 'ID':
						if ($rowValue === '') break;
						$eventsByInternalId[$rowValue] = $event;
						if (isset($unmatchedParentEvents[$rowValue])) {
							Assert::assertSame($eventsByInternalId[$rowValue], $event, 'Parent event does not match. (2)');
							unset($unmatchedParentEvents[$rowValue]);
						}
						break;
					case 'Parent Event':
						if ($rowValue === '') break;
						if (isset($eventsByInternalId[$rowValue])) {
							Assert::assertSame($eventsByInternalId[$rowValue], $event->getParentEvent(), 'Parent event does not match. (1)');
						} else {
							$unmatchedParentEvents[$rowValue] = $event->getParentEvent();
						}
						break;
					case 'Event Type':
						Assert::assertEquals($rowValue, $event->getEventType(), 'Event Type does not match. Expected: ' . $rowValue . '. Actual: ' . $event->getEventType());
						break;
					case 'Node Identifier':
						if ($rowValue === '') break;
						Assert::assertEquals($rowValue, $event->getNodeIdentifier(), 'Node Identifier does not match.');
						break;
					case 'Document Node Identifier':
						Assert::assertEquals($rowValue, $event->getDocumentNodeIdentifier(), 'Document Node Identifier does not match.');
						break;
					case 'Workspace':
						Assert::assertEquals($rowValue, $event->getWorkspaceName(), 'Workspace does not match.');
						break;
					default:
						throw new \Exception('Row Name ' . $rowName . ' not supported.');
				}
			}
		}

		Assert::assertEquals(count($table->getHash()), count($allEvents), 'Number of expected events does not match total number of events.');
		Assert::assertEmpty($unmatchedParentEvents, 'Unmatched parent events found');
	}

	/**
	 * @return \TYPO3\EventLog\Domain\Repository\EventRepository
	 */
	protected function getEventRepository() {
		return $this->getObjectManager()->get('TYPO3\EventLog\Domain\Repository\EventRepository');
	}
	/**
	 * @return \TYPO3\EventLog\Integrations\TYPO3CRIntegrationService
	 */
	protected function getTYPO3CRIntegrationService() {
		return $this->getObjectManager()->get('TYPO3\EventLog\Integrations\TYPO3CRIntegrationService');
	}
}
