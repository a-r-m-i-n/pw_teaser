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
	 * @var integer
	 */
	protected $currentPageUid = NULL;

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
	 * @var array
	 */
	protected $pages = array();

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
		$this->pagesRepository = $repository;
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
		$this->currentPageUid = $GLOBALS['TSFE']->id;

		// Sets template as file if configured
		$this->performTemplatePathAndFilename();

		$this->setOrderingAndLimitation();

		// Set ShowNavHiddenItems to TRUE
		if ($this->settings['showNavHiddenItems'] == '1') {
			$this->pagesRepository->setShowNavHiddenItems(TRUE);
		}

		switch ($this->settings['source']) {
			default:
			case 'thisChildren':
				$this->pages = $this->pagesRepository->findByPid($this->currentPageUid);
				break;

			case 'thisChildrenRecursively':
				$this->pages = $this->pagesRepository->findByPidRecursively($this->currentPageUid);
				break;

			case 'custom':
				$this->pages = $this->pagesRepository->findByPidList($this->settings['customPages'], $this->settings['orderByPlugin']);
				break;

			case 'customChildren':
				$this->pages = $this->pagesRepository->findChildrenByPidList($this->settings['customPages']);
				break;

			case 'customChildrenRecursively':
				$this->pages = $this->pagesRepository->findChildrenRecursivelyByPidList($this->settings['customPages']);
				break;
		}

		// Make random if selected on queryResult, cause Extbase doesn't support it
		if ($this->settings['orderBy'] == 'random') {
			shuffle($this->pages);
		}

		/** @var $page Tx_PwTeaser_Domain_Model_Page */
		foreach ($this->pages as $index => $page) {
			if ($this->performVisibilityFilters($page, $index) === TRUE) {
				continue;
			}

			if ($page->getUid() === $this->currentPageUid) {
				$page->setIsCurrentPage(TRUE);
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

		$this->view->assign('pages', $this->pages);
	}

	/**
	 * Performs visibility filters and removes pages which not matches the filters. The different filters always returns
	 * TRUE if the page doesn't match their criteria.
	 *
	 * @param Tx_PwTeaser_Domain_Model_Page $page page to perform filters on
	 * @param integer $index Position of page in pages array
	 *
	 * @return boolean Returns TRUE if the page is filtered out, otherwise FALSE
	 */
	protected function performVisibilityFilters(Tx_PwTeaser_Domain_Model_Page $page, $index) {
		if ($this->filterHideCurrentPage($page) || $this->filterByDokTypes($page) || $this->filterIgnoredUids($page)) {
			unset($this->pages[$index]);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Filter current page
	 *
	 * @param Tx_PwTeaser_Domain_Model_Page $page Page to check
	 *
	 * @return boolean Returns TRUE if the page is filtered out, otherwise FALSE
	 */
	protected function filterHideCurrentPage(Tx_PwTeaser_Domain_Model_Page $page) {
		return ($this->settings['hideCurrentPage'] == '1' && $page->getUid() === $this->currentPageUid);
	}

	/**
	 * Filter by DokType
	 *
	 * @param Tx_PwTeaser_Domain_Model_Page $page Page to check
	 *
	 * @return boolean Returns TRUE if the page is filtered out, otherwise FALSE
	 */
	protected function filterByDokTypes(Tx_PwTeaser_Domain_Model_Page $page) {
		$doktypesToShow = t3lib_div::trimExplode(',', $this->settings['showDoktypes'], TRUE);
		return (count($doktypesToShow) > 0 && !in_array($page->getDoktype(), $doktypesToShow));
	}

	/**
	 * Filter by ignoredUids
	 *
	 * @param Tx_PwTeaser_Domain_Model_Page $page Page to check
	 *
	 * @return boolean Returns TRUE if the page is filtered out, otherwise FALSE
	 */
	protected function filterIgnoredUids(Tx_PwTeaser_Domain_Model_Page $page) {
		$uidsToIgnore = t3lib_div::trimExplode(',', $this->settings['ignoreUids'], TRUE);
		return (count($uidsToIgnore) > 0 && in_array($page->getUid(), $uidsToIgnore));
	}

	/**
	 * Sets ordering and limitation settings from $this->settings
	 */
	protected function setOrderingAndLimitation() {
		if (!empty($this->settings['orderBy'])) {
			$this->pagesRepository->setOrderBy($this->settings['orderBy']);
		}

		if (!empty($this->settings['orderDirection'])) {
			$this->pagesRepository->setOrderDirection($this->settings['orderDirection']);
		}

		if (!empty($this->settings['limit'])) {
			$this->pagesRepository->setLimit(intval($this->settings['limit']));
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