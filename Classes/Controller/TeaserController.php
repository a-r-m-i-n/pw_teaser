<?php
namespace PwTeaserTeam\PwTeaser\Controller;

/***************************************************************
*  Copyright notice
*
*  (c) 2011-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
*                Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
*                Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
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
class TeaserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var integer
	 */
	protected $currentPageUid = NULL;

	/**
	 * @var \PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository
	 * @inject
	 */
	protected $pageRepository;

	/**
	 * @var \PwTeaserTeam\PwTeaser\Domain\Repository\ContentRepository
	 * @inject
	 */
	protected $contentRepository;

	/**
	 * @var \PwTeaserTeam\PwTeaser\Utility\Settings
	 * @inject
	 */
	protected $settingsUtility;

	/**
	 * @var tslib_cObj
	 */
	protected $contentObject = NULL;

	/**
	 * Initialize Action will performed before each action will be executed
	 *
	 * @return void
	 */
	public function initializeAction() {
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
		$this->pageRepository->setShowNavHiddenItems(($this->settings['showNavHiddenItems'] == '1'));
		$this->pageRepository->setFilteredDokType(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
			',',
			$this->settings['showDoktypes'],
			TRUE
		));

		if ($this->settings['hideCurrentPage'] == '1') {
			$this->pageRepository->setIgnoreOfUid($this->currentPageUid);
		}

		if ($this->settings['ignoreUids']) {
			$ignoringUids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['ignoreUids'], TRUE);
			array_map(array($this->pageRepository, 'setIgnoreOfUid'), $ignoringUids);
		}

		switch ($this->settings['source']) {
			default:
			case 'thisChildren':
				$pages = $this->pageRepository->findByPid($this->currentPageUid);
				break;

			case 'thisChildrenRecursively':
				$pages = $this->pageRepository->findByPidRecursively($this->currentPageUid);
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
			if (!empty($this->settings['limit'])) {
				$pages = array_slice($pages, 0, $this->settings['limit']);
			}
		}

		/** @var $page \PwTeaserTeam\PwTeaser\Domain\Model\Page */
		foreach ($pages as $page) {
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
					$_procObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($_classRef);
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
			if ($this->settings['orderBy'] === 'customField') {
				$this->pageRepository->setOrderBy($this->settings['orderByCustomField']);
			} else {
				$this->pageRepository->setOrderBy($this->settings['orderBy']);
			}
		}

		if (!empty($this->settings['orderDirection'])) {
			$this->pageRepository->setOrderDirection($this->settings['orderDirection']);
		}

		if (!empty($this->settings['limit']) && $this->settings['orderBy'] !== 'random') {
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
			\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
		);
		$templateType = $frameworkSettings['view']['templateType'];
		$templateFile = $frameworkSettings['view']['templateRootFile'];
		$layoutRootPath = $frameworkSettings['view']['layoutRootPath'];
		$partialRootPath = $frameworkSettings['view']['partialRootPath'];

		/**
		 * Setup layout root path.
		 */
		if ($layoutRootPath != NULL && !empty($layoutRootPath) && file_exists(PATH_site . $layoutRootPath)) {
    		$this->view->setLayoutRootPath($layoutRootPath);
  		}

 		/**
 		 * Setup partials root path.
 		 */
 		if ($partialRootPath != NULL && !empty($partialRootPath) && file_exists(PATH_site . $partialRootPath)) {
    		$this->view->setPartialRootPath($partialRootPath);
  		}

  		/**
  		 * If templateType is 'file', then setup templateFile.
  		 */
		if ($templateType === 'file' && !empty($templateFile) && file_exists(PATH_site . $templateFile)) {
			$this->view->setTemplatePathAndFilename($templateFile);
			return TRUE;
		}

		/**
		 * If templateFile is set and is not file, then setup template path.
		 */
		$templatePathAndFilename = $frameworkSettings['view']['templatePathAndFilename'];
		if ($templateType === NULL && !empty($templatePathAndFilename) && file_exists(PATH_site . $templatePathAndFilename)) {
			$this->view->setTemplatePathAndFilename($templatePathAndFilename);
			return TRUE;
		}

		return FALSE;
	}
}
?>