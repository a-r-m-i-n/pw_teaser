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
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Controller_TeaserController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * @var Tx_PwTeaser_Domain_Repository_PageRepository
	 */
	protected $pageRepository;

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
	 * Displays all Teasers
	 */
	public function indexAction() {
		$pages = NULL;
		$pageUid = $GLOBALS['TSFE']->id;

		$this->setOrderingAndLimitation();

		switch ($this->settings['source']) {
			default:
			case 'thisChildren':
				$pages = $this->pageRepository->findByPid($pageUid);
				break;

			case 'thisChildrenRecursively':
				$pages = $this->pageRepository->findByPidRecursively($pageUid);
				break;

			case 'custom':
				$pages = $this->pageRepository->findByPidList(
					$this->settings['customPages'],
					$this->settings['orderByPlugin']
				);
				break;

			case 'customChildren':
				$pages = $this->pageRepository->findChildrenByPidList(
					$this->settings['customPages']
				);
				break;

			case 'customChildrenRecursively':
				$pages = $this->pageRepository->findChildrenRecursivelyByPidList(
					$this->settings['customPages']
				);
				break;
		}

		// Make random if selected on queryResult, cause Extbase doesn't support it
		if ($this->settings['orderBy'] == 'random') {
			$pages = $pages->toArray();
			shuffle($pages);
		}

		// Load contents if enabled in configuration
		if ($this->settings['loadContents'] == '1') {
			foreach ($pages as $page) {
				$page->setContents(
					$this->contentRepository->findByPid($page->getUid())
				);
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

}
?>