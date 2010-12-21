<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Armin Ruediger Vieweg <info@professorweb.de>
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
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Domain_Repository_PageRepository extends Tx_Extbase_Persistence_Repository {

	private $orderBy = 'uid';
	private $orderDirection = Tx_Extbase_Persistence_QueryInterface::ORDER_ASCENDING;

	/**
	 * Sets the order by which is used by all find methods
	 * @param string $orderBy property to order by
	 */
	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
	}

	/**
	 * Sets the order direction which is used by all find methods
	 * @param string $orderDirection the direction to order, may be desc or asc
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
	 * Initializes the repository.
	 *
	 * @return void
	 * @see Tx_Extbase_Persistence_Repository::initializeObject()
	 */
	public function initializeObject() {
		$querySettings = $this->objectManager->create(
			'Tx_Extbase_Persistence_Typo3QuerySettings'
		);
		$querySettings->setRespectStoragePage(FALSE);
		$this->setDefaultQuerySettings($querySettings);
	}

	/**
	 * Returns all objects of this repository which match the pid
	 *
	 * @param integer $pid the pid to search for
	 * @return array all found objects, will be empty if there are no objects
	 */
	public function findByPid($pid) {
		$query = $this->createQuery();
		$query->matching(
			$query->equals('pid', $pid)
		);
		$query->setOrderings(array($this->orderBy => $this->orderDirection));
		return $query->execute();
	}

	/**
	 * Returns all objects of this repository which are children of the matched
	 * pid (recursively)
	 *
	 * @param integer $pid the pid to search for recursively
	 * @return array all found objects, will be empty if there are no objects
	 */
	public function findByPidRecursively($pid) {
		$pagePids =	t3lib_div::intExplode(
			',',
			Tx_PwTeaser_Utilities_oelibdb::createRecursivePageList($pid, 255),
			TRUE
		);

		$query = $this->createQuery();
		$query->matching(
			$query->in('pid', $pagePids)
		);
		$query->setOrderings(array($this->orderBy => $this->orderDirection));
		return $query->execute();
	}

	/**
	 * Returns all objects of this repository which are in the pidlist
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @return array all found objects, will be empty if there are no objects
	 */
	public function findByPidList($pidlist, $orderByPlugin = 0) {
		$pagePids =	t3lib_div::intExplode(
			',',
			$pidlist,
			TRUE
		);
		$query = $this->createQuery();
		$query->matching(
			$query->in('uid', $pagePids)
		);

		$results = $query->execute();

		if ($orderByPlugin == 1) {
			$results = $results->toArray();

			$sortedResults = $this->objectManager->create('Tx_Extbase_Persistence_ObjectStorage');
			foreach ($pagePids as $pagePid) {
				foreach ($results as $result) {
					if ($pagePid == $result->getUid()) {
						$sortedResults->attach($result);
						continue;
					}
				}
			}
			return $sortedResults;
		}

		return $results;
	}

	/**
	 * Returns all objects of this repository which are in the pidlist
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @return array all found objects, will be empty if there are no objects
	 */
	public function findChildrenByPidList($pidlist) {
		$pagePids =	t3lib_div::intExplode(
			',',
			$pidlist,
			TRUE
		);

		$query = $this->createQuery();
		$query->matching(
			$query->in('pid', $pagePids)
		);

		return $query->execute();
	}

	/**
	 * Returns all objects of this repository which are children of pages in the
	 * pidlist (recursively)
	 *
	 * @param string $pidlist comma seperated list of pids to search for
	 * @return array all found objects, will be empty if there are no objects
	 */
	public function findChildrenRecursivelyByPidList($pidlist) {
		$pagePids = array();

		$pids =	t3lib_div::intExplode(
			',',
			$pidlist,
			TRUE
		);

		foreach ($pids as $pid) {
			$pageList = Tx_PwTeaser_Utilities_oelibdb::createRecursivePageList(
				$pid,
				255
			);
			$pageList =	t3lib_div::intExplode(
				',',
				Tx_PwTeaser_Utilities_oelibdb::createRecursivePageList(
					$pid,
					255
				),
				TRUE
			);
			array_push($pagePids, $pageList);
		}

		$pagePids = array_unique($pagePids[0]);

		$query = $this->createQuery();
		$query->matching(
			$query->in('pid', $pagePids)
		);

		$query->setOrderings(array($this->orderBy => $this->orderDirection));
		return $query->execute();
	}
}
?>