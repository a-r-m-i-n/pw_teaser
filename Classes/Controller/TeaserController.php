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
	 * @var array
	 */
	protected $settings = array();

	/**
	 * @var Tx_PwTeaser_Domain_Repository_PageRepository
	 */
	protected $pageRepository;

	/**
	 * @var Tx_PwComments_Domain_Repository_CommentRepository
	 */
	protected $commentRepository;

	/**
	 * @var Tx_PwTeaser_Domain_Repository_ContentRepository
	 */
	protected $contentRepository;

	/**
	 * @var array
	 */
	protected $enabledExtensions = array();

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
	 * Injects the comment repository, but not automatically!
	 *
	 * @param Tx_PwComments_Domain_Repository_CommentRepository $repository
	 *        the repository to inject
	 *
	 * @return void
	 */
	public function manualInjectCommentRepository(
		Tx_PwComments_Domain_Repository_CommentRepository $repository
	) {
		$this->commentRepository = $repository;
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
	 * Initialize Action will performed before each action will be executed
	 *
	 * @return void
	 */
	public function  initializeAction() {
		$this->settings = $this->prepareSettings($this->settings);

		// Fill array of enabled extensions
		$this->enabledExtensions = t3lib_div::trimExplode(',', t3lib_extMgm::getEnabledExtensionList(), TRUE);

		// Inject comment repository manually, if pw_comments is installed and enabled
		if (in_array('pw_comments', $this->enabledExtensions)) {
			$commentRepository = $this->objectManager->get('Tx_PwComments_Domain_Repository_CommentRepository');
			$this->manualInjectCommentRepository($commentRepository);
		}
	}

	/**
	 * Displays all Teasers
	 */
	public function indexAction() {
		/** @var $pages Tx_Extbase_Persistence_QueryResult */
		$pages = NULL;
		$pageUid = $GLOBALS['TSFE']->id;

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

		/** @var $page Tx_PwTeaser_Domain_Model_Page */
		foreach ($pages as $page) {
			// Load contents if enabled in configuration
			if ($this->settings['loadContents'] == '1') {
				$page->setContents($this->contentRepository->findByPid($page->getUid()));
			}
			// Load comments if pw_comments is installed and activated
			if (in_array('pw_comments', $this->enabledExtensions)) {
				$page->setComments($this->commentRepository->findByPid($page->getUid()));
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
	 * Prepares the settings of controller for use. Including typoscript will be
	 * rendered and empty values from flexforms will fallback to typoscript values.
	 *
	 * @param array $settings The settings to prepare
	 *
	 * @return array The prepared settings
	 */
	protected function prepareSettings(array $settings) {
		// Fallback to typoscript values, if flexform values are empty
		$extkey = 'tx_' . strtolower($this->extensionName);
		$typoscript = $this->configurationManager->getConfiguration(
			Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
		);
		$typoscript = $typoscript['plugin.'][$extkey . '.']['settings.'];
		foreach($settings as $key => $setting) {
			if ($setting === '' && array_key_exists($key, $typoscript)) {
				$settings[$key] = $typoscript[$key];
			}
		}

		// Render typoscript parts in settings array
		$this->contentObject = $this->configurationManager->getContentObject();
		return $this->renderSettings($settings);
	}

	/**
	 * Renders TypoScript parts of configuration. Before this can be done, the
	 * settings array must be remodeled to work with cObjGetSingle.
	 *
	 * @param array $settings The settings to render
	 *
	 * @return array The rendered settings
	 */
	protected function renderSettings(array $settings) {
		$settings = $this->makeConfigurationArrayRenderable($settings);
		foreach($settings as $key => $value) {
			if (strpos($key, '.')) {
				$key = substr($key, 0, -1);
				$settings[$key] = $this->contentObject->cObjGetSingle(
					$settings[$key],
					$settings[$key . '.']
				);
				unset($settings[$key . '.']);
			}
		}
		return $settings;
	}

	/**
	 * Formats a given array with typoscript syntax, recursively. After the
	 * transformation it can be rendered with cObjGetSingle.
	 *
	 * Example:
	 * Before: $array['level1']['level2']['finalLevel'] = 'hello kitty'
	 * After:  $array['level1.']['level2.']['finalLevel'] = 'hello kitty'
	 *		   $array['level1'] = 'TEXT'
	 *
	 * @param array $configuration settings array to make renderable
	 *
	 * @return array the renderable settings
	 */
	protected function makeConfigurationArrayRenderable(array $configuration) {
		$dottedConfiguration = array();
		foreach ($configuration as $key => $value) {
			if (is_array($value)) {
				if (array_key_exists('_typoScriptNodeValue', $value)) {
					$dottedConfiguration[$key] = $value['_typoScriptNodeValue'];
				}
				$dottedConfiguration[$key . '.'] = $this->makeConfigurationArrayRenderable($value);
			} else {
				$dottedConfiguration[$key] = $value;
			}
		}
		return $dottedConfiguration;
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

		if ($templateType === 'file' && file_exists(PATH_site . $templateFile)) {
			$this->view->setTemplatePathAndFilename($templateFile);
			return TRUE;
		}
		return FALSE;
	}

}
?>