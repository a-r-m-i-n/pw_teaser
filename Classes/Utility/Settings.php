<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Armin Ruediger Vieweg <info@professorweb.de>
 *  (c) 2011 Benjamin Schulte <benjamin.schulte@diemedialen.de>
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
class Tx_PwTeaser_Utility_Settings {
	/**
	 * @var tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var Tx_Extbase_Configuration_ConfigurationManagerInterface
	 */
	protected $configurationManager = NULL;

	/**
	 * Injects the configurationManager
	 *
	 * @param Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager
	 *
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManagerInterface $configurationManager) {
		$this->configurationManager = $configurationManager;
	}

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
	 * @param array $typoscript the typoscript configuration array
	 *
	 * @author Benjamin Schulte <benjamin.schulte@diemedialen.de>
	 *
	 * @return array the configuration array with the rendered typoscript
	 */
	public function renderConfigurationArray(array $typoscript) {
		$result = array();
		foreach ($typoscript as $key => $value) {
			if (substr($key, -1) === '.') {
				$keyWithoutDot = substr($key, 0, -1);
				if (array_key_exists($keyWithoutDot, $typoscript)) {
					$result[$keyWithoutDot] = $this->contentObject->cObjGetSingle(
						$typoscript[$keyWithoutDot],
						$value
					);
				} else {
					$result[$keyWithoutDot] = $this->renderConfigurationArray($value);
				}
			} else {
				if (!array_key_exists($key . '.', $typoscript)) {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}
}
?>