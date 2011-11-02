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
 * Repository for Tx_PwTeaser_Domain_Model_Page
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Domain_Repository_PageRepository extends Tx_Extbase_Persistence_Repository {

	/**
	 * page attribute to order by
	 * @var string
	 */
	protected $orderBy = 'uid';

	/**
	 * Direction to order. Default is ascending.
	 * @var string
	 */
	protected $orderDirection = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;

	/**
	 * @var Tx_Extbase_Persistence_QueryInterface
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
	 * @see Tx_Extbase_Persistence_Repository::initializeObject()
	 */
	public function initializeObject() {
		$querySettings = $this->objectManager->create('Tx_Extbase_Persistence_Typo3QuerySettings');
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
		$this->query = $this->createQuery();
	}

	/**
	 * Returns all objects of this repository which match the pid
	 *
	 * @param integer $pid the pid to search for
	 *
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
	 *
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
	 *
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findByPidList($pidlist, $orderByPlugin = FALSE) {
		$pagePids =	t3lib_div::intExplode(',', $pidlist, TRUE);

		$query = $this->query;
		$this->addQueryConstraint($query->in('uid', $pagePids));
		$query->matching(
			$query->logicalAnd(
				$this->queryConstraints
			)
		);

		if ($orderByPlugin == FALSE) {
			$this->handleOrdering($query);
		}
		$results = $query->execute()->toArray();
		$this->resetQuery();

		if ($orderByPlugin == TRUE) {
			return $this->orderByPlugin($pagePids, $results);
		}
		return $results;
	}

	/**
	 * Creates array of result items, with the order of given pagePids
	 *
	 * @param array $pagePids pagePids to order for
	 * @param array $results results to reorder
	 *
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
	 *
	 * @return array All found pages, will be empty if the result is empty
	 */
	public function findChildrenByPidList($pidlist) {
		$pagePids =	t3lib_div::intExplode(
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
	 *
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
	 * @param Tx_Extbase_Persistence_QOM_ConstraintInterface $constraint Constraint to add
	 *
	 * @return void
	 */
	protected function addQueryConstraint(Tx_Extbase_Persistence_QOM_ConstraintInterface $constraint) {
		$this->queryConstraints[] = $constraint;
	}

	/**
	 * Finalize given query constraints and executes the query
	 *
	 * @return array|Tx_Extbase_Persistence_QueryResultInterface Result of query
	 */
	protected function executeQuery() {
		$query = $this->query;
		$query->matching($query->logicalAnd($this->queryConstraints));
		$this->handleOrdering($query);

		$queryResult = $query->execute()->toArray();
		$this->resetQuery();

		return $queryResult;
	}

	/**
	 * Get subpages recursivley of given pid(s).
	 *
	 * @param string $pidlist List of pageUids to get subpages of. May contain a single uid.
	 *
	 * @return array Found subpages, recursivley
	 */
	protected function getRecursivePageList($pidlist) {
		$pagePids = array();
		$pids = t3lib_div::intExplode(',', $pidlist, TRUE);

		foreach ($pids as $pid) {
			$pageList = t3lib_div::intExplode(
				',',
				Tx_PwTeaser_Utility_oelibdb::createRecursivePageList(
					$pid,
					255
				),
				TRUE
			);
			$pagePids = array_merge($pagePids, $pageList);
		}
		return array_unique($pagePids);
	}

	/**
	 * Sets the order by which is used by all find methods
	 *
	 * @param string $orderBy property to order by
	 *
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
	 *
	 * @return void
	 */
	public function setOrderDirection($orderDirection) {
		if ($orderDirection == 'desc' || $orderDirection == 1) {
			$this->orderDirection
				= Tx_Extbase_Persistence_QueryInterface::ORDER_DESCENDING;
		} else {
			$this->orderDirection
				= Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;
		}
	}

	/**
	 * Sets the query limit
	 *
	 * @param integer $limit The limit of elements to show
	 *
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
	 *
	 * @return void
	 */
	public function setShowNavHiddenItems($showNavHiddenItems) {
		if ($showNavHiddenItems === TRUE) {
			$this->addQueryConstraint($this->query->equals('nav_hide', array(0,1)));
		} else {
			$this->addQueryConstraint($this->query->equals('nav_hide', array(0)));
		}
	}

	/**
	 * Sets doktypes to filter for
	 *
	 * @param array $dokTypesToFilterFor doktypes as array, may be empty
	 *
	 * @return void
	 */
	public function setFilteredDokType(array $dokTypesToFilterFor) {
		if (count($dokTypesToFilterFor) > 0) {
			$this->addQueryConstraint($this->query->equals('doktype', $dokTypesToFilterFor));
		}
	}

	/**
	 * Ignores given uid
	 *
	 * @param integer $currentPageUid Uid to ignore
	 *
	 * @return void
	 */
	public function setIgnoreOfUid($currentPageUid) {
		$this->addQueryConstraint($this->query->logicalNot($this->query->equals('uid', $currentPageUid)));
	}

	/**
	 * Adds handle of ordering to query object
	 *
	 * @param Tx_Extbase_Persistence_QueryInterface $query
	 *
	 * @return void
	 */
	protected function handleOrdering(Tx_Extbase_Persistence_QueryInterface $query) {
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