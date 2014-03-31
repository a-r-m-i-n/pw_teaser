<?php
namespace PwTeaserTeam\PwTeaser\Domain\Repository;

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
 * Repository for \PwTeaserTeam\PwTeaser\Domain\Model\Page
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageRepository extends \TYPO3\CMS\Extbase\Persistence\Repository {

	/**
	 * page attribute to order by
	 * @var string
	 */
	protected $orderBy = 'uid';

	/**
	 * Direction to order. Default is ascending.
	 * @var string
	 */
	protected $orderDirection = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;

	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\QueryInterface
	 */
	protected $query = NULL;

	/**
	 * @var array
	 */
	protected $queryConstraints = array();

	/**
	 * Initializes the repository.
	 *
	 * @return void
	 *
	 * @see \TYPO3\CMS\Extbase\Persistence\Repository::initializeObject()
	 */
	public function initializeObject() {
		/** @var \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface $querySettings */
		$querySettings = $this->objectManager->get('TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface');
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
		$this->query = $this->createQuery();
	}

	/**
	 * Returns all objects of this repository which match the pid
	 *
	 * @param integer $pid the pid to search for
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findByPid($pid) {
		$this->addQueryConstraint($this->query->equals('pid', $pid));
		return $this->executeQuery();
	}

	/**
	 * Returns all objects of this repository which are children of the matched
	 * pid (recursively)
	 *
	 * @param integer $pid the pid to search for recursively
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findByPidRecursively($pid) {
		$pagePids = $this->getRecursivePageList($pid);

		$this->addQueryConstraint($this->query->in('pid', $pagePids));
		return $this->executeQuery();
	}

	/**
	 * Returns all objects of this repository which are in the pidlist
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @param boolean $orderByPlugin setting of ordering by plugin
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findByPidList($pidlist, $orderByPlugin = FALSE) {
		$pagePids =	\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $pidlist, TRUE);

		$query = $this->query;
		$this->addQueryConstraint($query->in('uid', $pagePids));
		$query->matching(
			$query->logicalAnd(
				$this->queryConstraints
			)
		);

		if ($orderByPlugin == FALSE) {
			$this->handleOrdering($query);
			$results = $query->execute();
		} else {
			$results = $query->execute()->toArray();
			$results = $this->handlePageLocalization($results);
			return $this->orderByPlugin($pagePids, $results);
		}
		$results = $this->handlePageLocalization($results);
		$this->resetQuery();
		return $results;
	}

	/**
	 * Creates array of result items, with the order of given pagePids
	 *
	 * @param array $pagePids pagePids to order for
	 * @param array $results results to reorder
	 * @return array results ordered by plugin
	 */
	protected function orderByPlugin(array $pagePids, array $results) {
		$sortedResults = array();
		foreach ($pagePids as $pagePid) {
			foreach ($results as $result) {
				if ($pagePid === $result->getUid()) {
					$sortedResults[] = $result;
					continue;
				}
			}
		}
		return $sortedResults;
	}

	/**
	 * Returns all objects of this repository which are in the pidlist
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findChildrenByPidList($pidlist) {
		$pagePids =	\TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(
			',',
			$pidlist,
			TRUE
		);

		$this->addQueryConstraint($this->query->in('pid', $pagePids));
		return $this->executeQuery();
	}

	/**
	 * Returns all objects of this repository which are children of pages in the
	 * pidlist (recursively)
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findChildrenRecursivelyByPidList($pidlist) {
		$pagePids = $this->getRecursivePageList($pidlist);

		$this->addQueryConstraint($this->query->in('pid', $pagePids));
		return $this->executeQuery();
	}

	/**
	 * Adds query constraint to array
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint Constraint to add
	 * @return void
	 */
	protected function addQueryConstraint(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint) {
		$this->queryConstraints[] = $constraint;
	}

	/**
	 * Finalize given query constraints and executes the query
	 *
	 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface Result of query
	 */
	protected function executeQuery() {
		$query = $this->query;
		$query->matching($query->logicalAnd($this->queryConstraints));
		$this->handleOrdering($query);

		$queryResult = $query->execute();
		$this->resetQuery();

		$queryResult = $this->handlePageLocalization($queryResult);
		return $queryResult;
	}

	/**
	 * Handles page localization
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $pages
	 * @return array<\PwTeaserTeam\PwTeaser\Domain\Model\Page>
	 */
	protected function handlePageLocalization(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $pages) {
		$currentLangUid = (int) $GLOBALS['TSFE']->sys_page->sys_language_uid;
		$displayedPages = array();

		/** @var \PwTeaserTeam\PwTeaser\Domain\Model\Page $page */
		foreach ($pages as $page) {
			if ($currentLangUid === 0) {
				if ($page->getL18nConfiguration() !== \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_DEFAULT_LANGUAGE
						&& $page->getL18nConfiguration() !== \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS) {
					$displayedPages[] = $page;
				}
			} else {
					/** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageSelect */
				$pageSelect = $GLOBALS['TSFE']->sys_page;
				$pageRowWithOverlays = $pageSelect->getPage($page->getUid());

				if ((boolean) $GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] === FALSE) {
					if (($page->getL18nConfiguration() === \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_IF_NO_TRANSLATION_EXISTS
						|| $page->getL18nConfiguration() === \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS)
						&& !isset($pageRowWithOverlays['_PAGES_OVERLAY'])
					) {
						$displayedPages[] = $page;
					}
				} else {
					if (($page->getL18nConfiguration() === \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_IF_NO_TRANSLATION_EXISTS
						|| $page->getL18nConfiguration() === \PwTeaserTeam\PwTeaser\Domain\Model\Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS)
						&& !isset($pageRowWithOverlays['_PAGES_OVERLAY']) || isset($pageRowWithOverlays['_PAGES_OVERLAY'])
					) {
						$displayedPages[] = $page;
					}
				}
			}
		}
		return $displayedPages;
	}

	/**
	 * Get subpages recursivley of given pid(s).
	 *
	 * @param string $pidlist List of pageUids to get subpages of. May contain a single uid.
	 * @return array Found subpages, recursivley
	 */
	protected function getRecursivePageList($pidlist) {
		/** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer */
		$contentObjectRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

		$pagePids = array();
		$pids = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(',', $pidlist, TRUE);
		foreach ($pids as $pid) {
			$pageList = \TYPO3\CMS\Core\Utility\GeneralUtility::intExplode(
				',',
				$contentObjectRenderer->getTreeList($pid, 255),
				TRUE
			);
			$pagePids = array_merge($pagePids, $pageList);
			array_unshift($pagePids, $pid);
		}
		return array_unique($pagePids);
	}

	/**
	 * Sets the order by which is used by all find methods
	 *
	 * @param string $orderBy property to order by
	 * @return void
	 */
	public function setOrderBy($orderBy) {
		if ($orderBy !== 'random') {
			$this->orderBy = $orderBy;
		}
	}

	/**
	 * Sets the order direction which is used by all find methods
	 *
	 * @param string $orderDirection the direction to order, may be desc or asc
	 * @return void
	 */
	public function setOrderDirection($orderDirection) {
		if ($orderDirection == 'desc' || $orderDirection == 1) {
			$this->orderDirection = \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface::ORDER_DESCENDING;
		} else {
			$this->orderDirection = \TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface::ORDER_ASCENDING;
		}
	}

	/**
	 * Sets the query limit
	 *
	 * @param integer $limit The limit of elements to show
	 * @return void
	 */
	public function setLimit($limit) {
		$this->query->setLimit($limit);
	}

	/**
	 * Sets the nav_hide_state flag
	 *
	 * @param boolean $showNavHiddenItems If TRUE lets show items which should not be visible in navigation.
	 *        Default is FALSE.
	 * @return void
	 */
	public function setShowNavHiddenItems($showNavHiddenItems) {
		if ($showNavHiddenItems === TRUE) {
			$this->addQueryConstraint($this->query->in('nav_hide', array(0,1)));
		} else {
			$this->addQueryConstraint($this->query->in('nav_hide', array(0)));
		}
	}

	/**
	 * Sets doktypes to filter for
	 *
	 * @param array $dokTypesToFilterFor doktypes as array, may be empty
	 * @return void
	 */
	public function setFilteredDokType(array $dokTypesToFilterFor) {
		if (count($dokTypesToFilterFor) > 0) {
			$this->addQueryConstraint($this->query->in('doktype', $dokTypesToFilterFor));
		}
	}

	/**
	 * Ignores given uid
	 *
	 * @param integer $currentPageUid Uid to ignore
	 * @return void
	 */
	public function setIgnoreOfUid($currentPageUid) {
		$this->addQueryConstraint($this->query->logicalNot($this->query->equals('uid', $currentPageUid)));
	}

	/**
	 * Adds handle of ordering to query object
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
	 * @return void
	 */
	protected function handleOrdering(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query) {
		$query->setOrderings(array($this->orderBy => $this->orderDirection));
	}

	/**
	 * Resets query and queryConstraints after execution
	 *
	 * @return void
	 */
	protected function resetQuery() {
		unset($this->query);
		$this->query = $this->createQuery();
		unset($this->queryConstraints);
		$this->queryConstraints = array();
	}
}
?>