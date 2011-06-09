<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Armin Ruediger Vieweg <info@professorweb.de>
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
 * Controller for the Teaser object
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Controller_TeaserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var Tx_PwTeaser_Domain_Repository_PageRepository
	 */
	protected $pageRepository;

	/**
	 * @var Tx_PwTeaser_Domain_Repository_ContentRepository
	 */
	protected $contentRepository;

	/**
	 * @var Tx_PwTeaser_Utility_Settings
	 */
	protected $settingsUtility;

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * Injects the page repository.
	 *
	 * @param Tx_PwTeaser_Domain_Repository_PageRepository $repository
	 *        the repository to inject
	 *
	 * @return void
	 */
	public function injectPageRepository(
		Tx_PwTeaser_Domain_Repository_PageRepository $repository
	) {
		$this->pageRepository = $repository;
	}

	/**
	 * Injects the content repository.
	 *
	 * @param Tx_PwTeaser_Domain_Repository_ContentRepository $repository
	 *        the repository to inject
	 *
	 * @return void
	 */
	public function injectContentRepository(
		Tx_PwTeaser_Domain_Repository_ContentRepository $repository
	) {
		$this->contentRepository = $repository;
	}

	/**
	 * Injects the settings utility.
	 *
	 * @param Tx_PwTeaser_Utility_Settings $utility the utility to inject
	 *
	 * @return void
	 */
	public function injectSettingsUtility(Tx_PwTeaser_Utility_Settings $utility) {
		$this->settingsUtility = $utility;
	}

	/**
	 * Initialize Action will performed before each action will be executed
	 *
	 * @return void
	 */
	public function  initializeAction() {
		$this->settings = $this->settingsUtility->renderConfigurationArray($this->settings);
	}

	/**
	 * Displays all Teasers
	 */
	public function indexAction() {
		/** @var $pages array */
		$pages = array();
		$currentPageUid = $GLOBALS['TSFE']->id;

		// Sets template as file if configured
		$this->performTemplatePathAndFilename();

		$this->setOrderingAndLimitation();

		// Set ShowNavHiddenItems to TRUE
		if ($this->settings['showNavHiddenItems'] == '1') {
			$this->pageRepository->setShowNavHiddenItems(TRUE);
		}

		switch ($this->settings['source']) {
			default:
			case 'thisChildren':
				$pages = $this->pageRepository->findByPid($currentPageUid);
				break;

			case 'thisChildrenRecursively':
				$pages = $this->pageRepository->findByPidRecursively($currentPageUid);
				break;

			case 'custom':
				$pages = $this->pageRepository->findByPidList($this->settings['customPages'], $this->settings['orderByPlugin']);
				break;

			case 'customChildren':
				$pages = $this->pageRepository->findChildrenByPidList($this->settings['customPages']);
				break;

			case 'customChildrenRecursively':
				$pages = $this->pageRepository->findChildrenRecursivelyByPidList($this->settings['customPages']);
				break;
		}

		// Make random if selected on queryResult, cause Extbase doesn't support it
		if ($this->settings['orderBy'] == 'random') {
			shuffle($pages);
		}

		/** @var $page Tx_PwTeaser_Domain_Model_Page */
		foreach ($pages as $index => $page) {
			// Hide current page, not containing doktypes and uids to ignore from list
			$doktypesToShow = t3lib_div::trimExplode(',', $this->settings['showDoktypes'], TRUE);
			$ignoreUids = t3lib_div::trimExplode(',', $this->settings['ignoreUids'], TRUE);
			if (
				($this->settings['hideCurrentPage'] == '1' && $page->getUid() === $currentPageUid)
				|| (count($doktypesToShow) > 0 && !in_array($page->getDoktype(), $doktypesToShow))
				|| (count($ignoreUids) > 0 && in_array($page->getUid(), $ignoreUids))
			) {
				unset($pages[$index]);
				continue;
			}

			// Load contents if enabled in configuration
			if ($this->settings['loadContents'] == '1') {
				$page->setContents($this->contentRepository->findByPid($page->getUid()));
			}

			// Hook 'modifyPageModel' to modify the pages model with other extensions
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pw_teaser']['modifyPageModel'])) {
				foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['pw_teaser']['modifyPageModel'] as $_classRef) {
					$_procObj = &t3lib_div::getUserObj($_classRef);
					$_procObj->main($this, $page);
				}
			}
		}

		$this->view->assign('pages', $pages);
	}

	/**
	 * Sets ordering and limitation settings from $this->settings
	 */
	protected function setOrderingAndLimitation() {
		if (!empty($this->settings['orderBy'])) {
			$this->pageRepository->setOrderBy($this->settings['orderBy']);
		}

		if (!empty($this->settings['orderDirection'])) {
			$this->pageRepository->setOrderDirection($this->settings['orderDirection']);
		}

		if (!empty($this->settings['limit'])) {
			$this->pageRepository->setLimit(intval($this->settings['limit']));
		}
	}

	/**
	 * Sets the fluid template to file if file is selected in flexform
	 * configuration and file exists
	 *
	 * @return boolean Returns TRUE if templateType is file and exists,
	 *         otherwise returns FALSE
	 */
	protected function performTemplatePathAndFilename() {
		$frameworkSettings = $this->configurationManager->getConfiguration(
			Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$templateType = $frameworkSettings['view']['templateType'];
		$templateFile = $frameworkSettings['view']['templateRootFile'];

		if ($templateType === 'file' && !empty($templateFile) && file_exists(PATH_site . $templateFile)) {
			$this->view->setTemplatePathAndFilename($templateFile);
			return TRUE;
		}
		return FALSE;
	}

}
?>