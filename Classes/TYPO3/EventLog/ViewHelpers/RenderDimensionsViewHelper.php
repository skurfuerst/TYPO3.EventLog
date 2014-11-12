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

use TYPO3\Flow\Configuration\ConfigurationManager;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderDimensionsViewHelper extends AbstractViewHelper {

	/**
	 * @var array
	 */
	protected $contentDimensionsConfiguration;

	public function injectConfigurationManager(ConfigurationManager $configurationManager) {
		$this->contentDimensionsConfiguration = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'TYPO3.TYPO3CR.contentDimensions');
	}


	/**
	 * render
	 */
	public function render() {
		$rendered = array();
		$dimensions = $this->renderChildren();
		foreach ($dimensions as $dimensionIdentifier => $dimensionValue) {
			$dimensionConfiguration = $this->contentDimensionsConfiguration[$dimensionIdentifier];
			$preset = $this->findPresetInDimension($dimensionConfiguration, $dimensionValue);
			$rendered[] = $dimensionConfiguration['label'] . ' ' .$preset['label'];
		}

		return implode(', ', $rendered);
	}

	protected function findPresetInDimension($dimensionConfiguration, $dimensionValue) {
		foreach ($dimensionConfiguration['presets'] as $preset) {
			if (!isset($preset['values'])) continue;
			foreach ($preset['values'] as $value) {
				if ($value === $dimensionValue) {
					return $preset;
				}
			}
		}
		return NULL;
	}


} 