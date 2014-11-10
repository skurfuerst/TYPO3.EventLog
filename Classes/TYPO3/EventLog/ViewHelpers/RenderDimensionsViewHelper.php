<?php
/**
 * Created by PhpStorm.
 * User: sebastian
 * Date: 10.11.14
 * Time: 12:46
 */

namespace TYPO3\EventLog\ViewHelpers;


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