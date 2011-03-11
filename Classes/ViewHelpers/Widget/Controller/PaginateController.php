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
 * Controller of modificated paginate widget
 *
 * @author     Armin Rüdiger Vieweg <info@professorweb.de>
 * @copyright  2011 Copyright belongs to the respective authors
 * @license    http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_ViewHelpers_Widget_Controller_PaginateController extends Tx_Fluid_Core_Widget_AbstractWidgetController {

	/**
	 * @var array
	 */
	protected $configuration = array('itemsPerPage' => 10, 'insertAbove' => FALSE, 'insertBelow' => TRUE);

	/**
	 * @var mixed
	 */
	protected $objects;

	/**
	 * @var string
	 */
	protected $objectType = NULL;

	/**
	 * @var integer
	 */
	protected $currentPage = 1;

	/**
	 * @var integer
	 */
	protected $numberOfPages = 1;

	/**
	 * Initilize action of paginate widget controller
	 *
	 * @return void
	 */
	public function initializeAction() {
		$this->objects = $this->widgetConfiguration['objects'];
		$this->objectType = get_class($this->objects);
		$this->configuration = t3lib_div::array_merge_recursive_overrule($this->configuration, $this->widgetConfiguration['configuration'], TRUE);
		$this->numberOfPages = ceil(count($this->objects) / (integer)$this->configuration['itemsPerPage']);
	}

	/**
	 * Index action of paginate widget controller
	 *
	 * @param integer $currentPage
	 * @return void
	 */
	public function indexAction($currentPage = 1) {
			// set current page
		$this->currentPage = (integer)$currentPage;
		if ($this->currentPage < 1) {
			$this->currentPage = 1;
		} elseif ($this->currentPage > $this->numberOfPages) {
			$this->currentPage = $this->numberOfPages;
		}

		$itemsPerPage = (integer)$this->configuration['itemsPerPage'];
		$modifiedObjects = array();

		if ($this->objectType == 'Tx_Extbase_Persistence_QueryResult') {
				// Tx_Extbase_Persistence_QueryResult
				// modify query
			$query = $this->objects->getQuery();
			$query->setLimit($itemsPerPage);
			if ($this->currentPage > 1) {
				$query->setOffset((integer)($itemsPerPage * ($this->currentPage - 1)));
			}
			$modifiedObjects = $query->execute();
		} else {
				// Tx_Extbase_Persistence_ObjectStorage or array
			$indexMin = $itemsPerPage * ($this->currentPage - 1);
			$indexMax = $itemsPerPage * $this->currentPage - 1;
			$i = 0;
			foreach ($this->objects as $object) {
				if ($i >= $indexMin && $i <= $indexMax ) {
					$modifiedObjects[] = $object;
				}
				$i++;
			}
		}
		$this->view->assign('contentArguments', array(
			$this->widgetConfiguration['as'] => $modifiedObjects,
			'pagination' => $this->buildPagination()
		));
		$this->view->assign('configuration', $this->configuration);
		$this->view->assign('pagination', $this->buildPagination());
	}

	/**
	 * Returns an array with the keys "pages", "current", "numberOfPages", "nextPage" & "previousPage"
	 *
	 * @return array
	 */
	protected function buildPagination() {
		$pages = array();
		for ($i = 1; $i <= $this->numberOfPages; $i++) {
			$pages[] = array('number' => $i, 'isCurrent' => ($i === $this->currentPage));
		}
		$pagination = array(
			'pages' => $pages,
			'current' => $this->currentPage,
			'numberOfPages' => $this->numberOfPages,
		);
		if ($this->currentPage < $this->numberOfPages) {
			$pagination['nextPage'] = $this->currentPage + 1;
		}
		if ($this->currentPage > 1) {
			$pagination['previousPage'] = $this->currentPage - 1;
		}
		return $pagination;
	}
}
?>