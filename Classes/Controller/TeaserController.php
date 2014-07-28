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
 * Controller for the teaser object
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
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoryRepository;

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
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * Initialize Action will performed before each action will be executed
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->settings = $this->settingsUtility->renderConfigurationArray($this->settings);
	}

	/**
	 * Displays teasers
	 *
	 * @return void
	 */
	public function indexAction() {
		$this->currentPageUid = $GLOBALS['TSFE']->id;

		$this->performTemplatePathAndFilename();
		$this->setOrderingAndLimitation();
		$this->performPluginConfigurations();

		switch ($this->settings['source']) {
			default:
			case 'thisChildren':
				$rootPageUids = $this->currentPageUid;
				$pages = $this->pageRepository->findByPid($this->currentPageUid);
				break;

			case 'thisChildrenRecursively':
				$rootPageUids = $this->currentPageUid;
				$pages = $this->pageRepository->findByPidRecursively(
					$this->currentPageUid,
					(int) $this->settings['recursionDepthFrom'],
					(int) $this->settings['recursionDepth']
				);
				break;

			case 'custom':
				$rootPageUids = $this->settings['customPages'];
				$pages = $this->pageRepository->findByPidList($this->settings['customPages'], $this->settings['orderByPlugin']);
				break;

			case 'customChildren':
				$rootPageUids = $this->settings['customPages'];
				$pages = $this->pageRepository->findChildrenByPidList($this->settings['customPages']);
				break;

			case 'customChildrenRecursively':
				$rootPageUids = $this->settings['customPages'];
				$pages = $this->pageRepository->findChildrenRecursivelyByPidList(
					$this->settings['customPages'],
					(int) $this->settings['recursionDepthFrom'],
					(int) $this->settings['recursionDepth']
				);
				break;
		}

		if ($this->settings['pageMode'] !== 'nested') {
			$pages = $this->performSpecialOrderings($pages);
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
		}

		if ($this->settings['pageMode'] === 'nested') {
			$pages = $this->convertFlatToNestedPagesArray($pages, $rootPageUids);
		}

		$this->signalSlotDispatcher->dispatch(__CLASS__, __FUNCTION__ . 'ModifyPages', array(&$pages, $this));
		$this->view->assign('pages', $pages);
	}

	/**
	 * Function to sort given pages by recursiveRootLineOrdering string
	 *
	 * @param \PwTeaserTeam\PwTeaser\Domain\Model\Page $a
	 * @param \PwTeaserTeam\PwTeaser\Domain\Model\Page $b
	 * @return integer
	 */
	protected function sortByRecursivelySorting(\PwTeaserTeam\PwTeaser\Domain\Model\Page $a, \PwTeaserTeam\PwTeaser\Domain\Model\Page $b) {
		if ($a->getRecursiveRootLineOrdering() == $b->getRecursiveRootLineOrdering()) {
			return 0;
		}
		return ($a->getRecursiveRootLineOrdering() < $b->getRecursiveRootLineOrdering()) ? - 1 : 1;
	}

	/**
	 * Sets ordering and limitation settings from $this->settings
	 *
	 * @return void
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

		if ($layoutRootPath != NULL && !empty($layoutRootPath) && file_exists(PATH_site . $layoutRootPath)) {
			$this->view->setLayoutRootPath($layoutRootPath);
		}
		if ($partialRootPath != NULL && !empty($partialRootPath) && file_exists(PATH_site . $partialRootPath)) {
			$this->view->setPartialRootPath($partialRootPath);
		}
		if ($templateType === 'file' && !empty($templateFile) && file_exists(PATH_site . $templateFile)) {
			$this->view->setTemplatePathAndFilename($templateFile);
			return TRUE;
		}

		$templatePathAndFilename = $frameworkSettings['view']['templatePathAndFilename'];
		if ($templateType === NULL && !empty($templatePathAndFilename) && file_exists(PATH_site . $templatePathAndFilename)) {
			$this->view->setTemplatePathAndFilename($templatePathAndFilename);
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Performs configurations from plugin settings (flexform)
	 *
	 * @return void
	 */
	protected function performPluginConfigurations() {
			// Set ShowNavHiddenItems to TRUE
		$this->pageRepository->setShowNavHiddenItems(($this->settings['showNavHiddenItems'] == '1'));
		$this->pageRepository->setFilteredDokType(\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['showDoktypes'], TRUE));

		if ($this->settings['hideCurrentPage'] == '1') {
			$this->pageRepository->setIgnoreOfUid($this->currentPageUid);
		}

		if ($this->settings['ignoreUids']) {
			$ignoringUids = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['ignoreUids'], TRUE);
			array_map(array($this->pageRepository, 'setIgnoreOfUid'), $ignoringUids);
		}

		if ($this->settings['categoriesList'] && $this->settings['categoryMode']) {
			$categories = array();
			foreach (\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $this->settings['categoriesList'], TRUE) as $categoryUid) {
				$categories[] = $this->categoryRepository->findByUid($categoryUid);
			}

			switch ((int)$this->settings['categoryMode']) {
				case \PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository::CATEGORY_MODE_OR:
				case \PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository::CATEGORY_MODE_OR_NOT:
					$isAnd = FALSE;
					break;
				default:
					$isAnd = TRUE;
			}
			switch ((int)$this->settings['categoryMode']) {
				case \PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository::CATEGORY_MODE_AND_NOT:
				case \PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository::CATEGORY_MODE_OR_NOT:
					$isNot = TRUE;
					break;
				default:
					$isNot = FALSE;
			}
			$this->pageRepository->addCategoryConstraint($categories, $isAnd, $isNot);
		}

		if ($this->settings['source'] === 'custom') {
			$this->settings['pageMode'] = 'flat';
		}

		if ($this->settings['pageMode'] === 'nested') {
			$this->settings['recursionDepthFrom'] = 0;
			$this->settings['orderBy'] = 'uid';
			$this->settings['limit'] = 0;
		}
	}

	/**
	 * Performs special orderings like "random" or "sorting"
	 *
	 * @param array<Pages> $pages
	 * @return array
	 */
	protected function performSpecialOrderings(array $pages) {
			// Make random if selected on queryResult, cause Extbase doesn't support it
		if ($this->settings['orderBy'] === 'random') {
			shuffle($pages);
			if (!empty($this->settings['limit'])) {
				$pages = array_slice($pages, 0, $this->settings['limit']);
			}
		}

		if ($this->settings['orderBy'] === 'sorting' && strpos($this->settings['source'], 'Recursively') !== FALSE) {
			usort($pages, array($this, 'sortByRecursivelySorting'));
			if (strtolower($this->settings['orderDirection']) === strtolower(\TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING)) {
				$pages = array_reverse($pages);
			}
			if (!empty($this->settings['limit'])) {
				$pages = array_slice($pages, 0, $this->settings['limit']);
				return $pages;
			}
			return $pages;
		}
		return $pages;
	}

	/**
	 * Converts given pages array (flat) to nested one
	 *
	 * @param array<Pages> $pages
	 * @param string $rootPageUids Comma separated list of page uids
	 * @return array<Pages>
	 */
	protected function convertFlatToNestedPagesArray($pages, $rootPageUids) {
		$rootPageUidArray = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $rootPageUids);
		$rootPages = array();
		foreach ($rootPageUidArray as $rootPageUid) {
			$page = $this->pageRepository->findByUid($rootPageUid);
			$this->fillChildPagesRecursivley($page, $pages);
			$rootPages[] = $page;
		}
		return $rootPages;
	}

	/**
	 * Fills given parentPage's childPages attribute recursively with pages
	 *
	 * @param \PwTeaserTeam\PwTeaser\Domain\Model\Page $parentPage
	 * @param array $pages
	 * @return \PwTeaserTeam\PwTeaser\Domain\Model\Page
	 */
	protected function fillChildPagesRecursivley($parentPage, array $pages) {
		$childPages = array();
		/** @var $page \PwTeaserTeam\PwTeaser\Domain\Model\Page */
		foreach ($pages as $page) {
			if ($page->getPid() === $parentPage->getUid()) {
				$this->fillChildPagesRecursivley($page, $pages);
				$childPages[$page->getSorting()] = $page;
			}
		}
		ksort($childPages);
		$parentPage->setChildPages(array_values($childPages));
		return $parentPage;
	}
}