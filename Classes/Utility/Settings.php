<?php
namespace PwTeaserTeam\PwTeaser\Utility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
 *                Benjamin Schulte <benjamin.schulte@diemedialen.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * This class provides some methods to prepare and render given extension settings
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Settings {
	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	protected $contentObject;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager = NULL;

	/**
	 * Initialize this settings utility
	 *
	 * @return void
	 */
	public function initializeObject() {
		$this->contentObject = $this->configurationManager->getContentObject();
	}

	/**
	 * Renders a given typoscript configuration and returns the whole array with
	 * calculated values.
	 *
	 * @param array $settings the typoscript configuration array
	 * @return array the configuration array with the rendered typoscript
	 */
	public function renderConfigurationArray(array $settings) {
		$settings = $this->enhanceSettingsWithTypoScript($this->makeConfigurationArrayRenderable($settings));
		$result = array();

		foreach ($settings as $key => $value) {
			if (substr($key, -1) === '.') {
				$keyWithoutDot = substr($key, 0, -1);
				if (array_key_exists($keyWithoutDot, $settings)) {
					$result[$keyWithoutDot] = $this->contentObject->cObjGetSingle(
						$settings[$keyWithoutDot],
						$value
					);
				} else {
					$result[$keyWithoutDot] = $this->renderConfigurationArray($value);
				}
			} else {
				if (!array_key_exists($key . '.', $settings)) {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Overwrite flexform values with typoscript if flexform value is empty and typoscript value exists.
	 *
	 * @param array $settings Settings from flexform
	 * @return array enhanced settings
	 */
	protected function enhanceSettingsWithTypoScript(array $settings) {
		$extkey = 'tx_pwteaser';
		$typoscript = $this->configurationManager->getConfiguration(
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		$typoscript = $typoscript['plugin.'][$extkey . '.']['settings.'];
		foreach ($settings as $key => $setting) {
			if ($setting === '' && is_array($typoscript) && array_key_exists($key, $typoscript)) {
				$settings[$key] = $typoscript[$key];
			}
		}
		return $settings;
	}

	/**
	 * Formats a given array with typoscript syntax, recursively. After the
	 * transformation it can be rendered with cObjGetSingle.
	 *
	 * Example:
	 * Before: $array['level1']['level2']['finalLevel'] = 'hello kitty'
	 * After:  $array['level1.']['level2.']['finalLevel'] = 'hello kitty'
	 *		   $array['level1'] = 'TEXT'
	 *
	 * @param array $configuration settings array to make renderable
	 * @return array the renderable settings
	 */
	protected function makeConfigurationArrayRenderable(array $configuration) {
		$dottedConfiguration = array();
		foreach ($configuration as $key => $value) {
			if (is_array($value)) {
				if (array_key_exists('_typoScriptNodeValue', $value)) {
					$dottedConfiguration[$key] = $value['_typoScriptNodeValue'];
				}
				$dottedConfiguration[$key . '.'] = $this->makeConfigurationArrayRenderable($value);
			} else {
				$dottedConfiguration[$key] = $value;
			}
		}
		return $dottedConfiguration;
	}
}