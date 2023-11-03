<?php
namespace PwTeaserTeam\PwTeaser\Controller;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2016 Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
 *  |     2016 Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
 */
use Psr\Http\Message\ResponseInterface;
use PwTeaserTeam\PwTeaser\Domain\Model\Page;
use PwTeaserTeam\PwTeaser\Domain\Repository\ContentRepository;
use PwTeaserTeam\PwTeaser\Domain\Repository\PageRepository;
use PwTeaserTeam\PwTeaser\Domain\Repository\CategoryRepository;
use PwTeaserTeam\PwTeaser\Event\ModifyPagesEvent;
use PwTeaserTeam\PwTeaser\Utility\Settings;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Controller for the teaser object
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TeaserController extends ActionController
{
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var integer
     */
    protected $currentPageUid = null;

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var ContentRepository
     */
    protected $contentRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var Settings
     */
    protected $settingsUtility;

    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject = null;

    /**
     * @var TemplateView
     */
    protected $view;

    /**
     * @var array
     */
    protected $viewSettings = [];

    public function __construct(
        PageRepository $pageRepository,
        ContentRepository $contentRepository,
        CategoryRepository $categoryRepository,
        Settings $settingsUtility
    ) {
        $this->pageRepository = $pageRepository;
        $this->contentRepository = $contentRepository;
        $this->categoryRepository = $categoryRepository;
        $this->settingsUtility = $settingsUtility;
    }

    /**
     * Initialize Action will get performed before each action will be executed
     *
     * @return void
     */
    public function initializeAction()
    {
        $this->settings = $this->settingsUtility->renderConfigurationArray($this->settings);

        $frameworkSettings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK
        );
        $viewSettings = $frameworkSettings['view'];
        $presets = $viewSettings['presets'] ?? [];
        unset($viewSettings['presets']);
        $this->viewSettings = $this->settingsUtility->renderConfigurationArray($viewSettings, 'view.');
        $this->viewSettings['presets'] = $presets;
    }

    /**
     * Displays teasers
     *
     * @return ResponseInterface|void
     */
    public function indexAction()
    {
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
                    (int)$this->settings['recursionDepthFrom'],
                    (int)$this->settings['recursionDepth']
                );
                break;

            case 'custom':
                $rootPageUids = $this->settings['customPages'];
                $pages = $this->pageRepository->findByPidList(
                    $this->settings['customPages'],
                    $this->settings['orderByPlugin']
                );
                break;

            case 'customChildren':
                $rootPageUids = $this->settings['customPages'];
                $pages = $this->pageRepository->findChildrenByPidList($this->settings['customPages']);
                break;

            case 'customChildrenRecursively':
                $rootPageUids = $this->settings['customPages'];
                $pages = $this->pageRepository->findChildrenRecursivelyByPidList(
                    $this->settings['customPages'],
                    (int)$this->settings['recursionDepthFrom'],
                    (int)$this->settings['recursionDepth']
                );
                break;
        }

        if ($this->settings['pageMode'] !== 'nested') {
            $pages = $this->performSpecialOrderings($pages);
        }

        /** @var $page \PwTeaserTeam\PwTeaser\Domain\Model\Page */
        foreach ($pages as $page) {
            if ($page->getUid() === $this->currentPageUid) {
                $page->setIsCurrentPage(true);
            }

            // Load contents if enabled in configuration
            if ($this->settings['loadContents'] == '1') {
                $page->setContents($this->contentRepository->findByPid($page->getUid()));
            }
        }

        if ($this->settings['pageMode'] === 'nested') {
            $pages = $this->convertFlatToNestedPagesArray($pages, $rootPageUids);
        }

        /** @var ModifyPagesEvent $event */
        $event = $this->eventDispatcher->dispatch(new ModifyPagesEvent($pages, $this));
        $this->view->assign('pages', $event->getPages());

        if ($this->settings['enablePagination'] ?? true) {
            $itemsPerPage = $this->settings['itemsPerPage'] ?? 10;
            $currentPage = max(1, $this->request->hasArgument('currentPage') ? (int)$this->request->getArgument('currentPage') : 1);
            $paginator = GeneralUtility::makeInstance(ArrayPaginator::class, $event->getPages(), $currentPage, $itemsPerPage, (int)($this->settings['limit'] ?? 0), 0);
            $pagination = $this->getPagination($paginator);
            $this->view->assign('pagination', [
                'currentPage' => $currentPage,
                'paginator' => $paginator,
                'pagination' => $pagination,
            ]);
        }

        if (isset($this->responseFactory)) {
            return $this->responseFactory->createResponse()
                ->withAddedHeader('Content-Type', 'text/html; charset=utf-8')
                ->withBody($this->streamFactory->createStream($this->view->render()));
        }
    }

    /**
     * Function to sort given pages by recursiveRootLineOrdering string
     *
     * @param \PwTeaserTeam\PwTeaser\Domain\Model\Page $a
     * @param \PwTeaserTeam\PwTeaser\Domain\Model\Page $b
     * @return integer
     */
    protected function sortByRecursivelySorting(
        \PwTeaserTeam\PwTeaser\Domain\Model\Page $a,
        \PwTeaserTeam\PwTeaser\Domain\Model\Page $b
    ) {
        if ($a->getRecursiveRootLineOrdering() == $b->getRecursiveRootLineOrdering()) {
            return 0;
        }
        return ($a->getRecursiveRootLineOrdering() < $b->getRecursiveRootLineOrdering()) ? -1 : 1;
    }

    /**
     * Sets ordering and limitation settings from $this->settings
     *
     * @return void
     */
    protected function setOrderingAndLimitation()
    {
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
    protected function performTemplatePathAndFilename()
    {
        $templateType = $this->viewSettings['templateType'] ?? '';
        $templateFile = $this->viewSettings['templateRootFile'] ?? '';
        $layoutRootPaths = $this->viewSettings['layoutRootPaths'] ?? null ?: [$this->viewSettings['layoutRootPath'] ?? null ?: null];
        $partialRootPaths = $this->viewSettings['partialRootPaths'] ?? null ?: [$this->viewSettings['partialRootPath'] ?? null ?: null];
        $templateRootPaths = $this->viewSettings['templateRootPaths'] ?? null ?: [$this->viewSettings['templateRootPath'] ?? null ?: null];

        $preset = $this->viewSettings['templatePreset'] ?? null;
        if ($templateType === 'preset' && !empty($preset)) {
            $currentPreset = $this->viewSettings['presets'][$preset];
            if (array_key_exists('partialRootPaths', $currentPreset) && !empty($currentPreset['partialRootPaths'])) {
                $partialRootPaths = $currentPreset['partialRootPaths'];
            }
            if (array_key_exists('layoutRootPaths', $currentPreset) && !empty($currentPreset['layoutRootPaths'])) {
                $layoutRootPaths = $currentPreset['layoutRootPaths'];
            }
            $templateType = 'file';
            $templateFile = $currentPreset['templateRootFile'];
        }

        if ($templateType !== 'preset' && $templateRootPaths !== [null] && !empty($templateRootPaths)) {
            if (!file_exists(GeneralUtility::getFileAbsFileName(reset($templateRootPaths)))) {
                throw new \Exception('Template folder "' . reset($templateRootPaths) . '" not found!');
            }
            $this->view->setTemplateRootPaths($templateRootPaths);
        }

        if ($layoutRootPaths !== [null] && !empty($layoutRootPaths)) {
            if (!file_exists(GeneralUtility::getFileAbsFileName(reset($layoutRootPaths)))) {
                throw new \Exception('Layout folder "' . reset($layoutRootPaths) . '" not found!');
            }
            $this->view->setLayoutRootPaths($layoutRootPaths);
        }
        if ($partialRootPaths !== [null] && !empty($partialRootPaths)) {
            if (!file_exists(GeneralUtility::getFileAbsFileName(reset($partialRootPaths)))) {
                throw new \Exception('Partial folder "' . reset($partialRootPaths) . '" not found!');
            }
            $this->view->setPartialRootPaths($partialRootPaths);
        }
        if ($templateType === 'file' &&
            !empty($templateFile) &&
            file_exists(GeneralUtility::getFileAbsFileName($templateFile))
        ) {
            $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templateFile));
            return true;
        }

        $templatePathAndFilename = $this->viewSettings['templatePathAndFilename'] ?? '';
        if ($templateType === null && !empty($templatePathAndFilename)
            && file_exists(GeneralUtility::getFileAbsFileName($templatePathAndFilename))) {
            $this->view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($templatePathAndFilename));
            return true;
        }
        return false;
    }

    /**
     * Performs configurations from plugin settings (flexform)
     *
     * @return void
     */
    protected function performPluginConfigurations()
    {
        // Set ShowNavHiddenItems to TRUE
        $this->pageRepository->setShowNavHiddenItems(($this->settings['showNavHiddenItems'] == '1'));
        $this->pageRepository->setFilteredDokType(
            GeneralUtility::trimExplode(
                ',',
                $this->settings['showDoktypes'],
                true
            )
        );

        if ($this->settings['hideCurrentPage'] ?? null == '1') {
            $this->pageRepository->setIgnoreOfUid($this->currentPageUid);
        }

        if ($this->settings['ignoreUids'] ?? null) {
            $ignoringUids = GeneralUtility::trimExplode(',', $this->settings['ignoreUids'], true);
            array_map([$this->pageRepository, 'setIgnoreOfUid'], $ignoringUids);
        }

        if (($this->settings['categoriesList'] ?? null) && $this->settings['categoryMode'] ?? null) {
            $categories = [];
            foreach (GeneralUtility::intExplode(',', $this->settings['categoriesList'], true) as $categoryUid) {
                $categories[] = $this->categoryRepository->findByUid($categoryUid);
            }

            switch ((int)$this->settings['categoryMode']) {
                case PageRepository::CATEGORY_MODE_OR:
                case PageRepository::CATEGORY_MODE_OR_NOT:
                    $isAnd = false;
                    break;
                default:
                    $isAnd = true;
            }
            switch ((int)$this->settings['categoryMode']) {
                case PageRepository::CATEGORY_MODE_AND_NOT:
                case PageRepository::CATEGORY_MODE_OR_NOT:
                    $isNot = true;
                    break;
                default:
                    $isNot = false;
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
     * @param array <Pages> $pages
     * @return array
     */
    protected function performSpecialOrderings(array $pages)
    {
        // Make random if selected on queryResult, cause Extbase doesn't support it
        if ($this->settings['orderBy'] === 'random') {
            shuffle($pages);
            if (!empty($this->settings['limit'])) {
                $pages = array_slice($pages, 0, $this->settings['limit']);
            }
        }

        if ($this->settings['orderBy'] === 'sorting' && strpos($this->settings['source'], 'Recursively') !== false) {
            usort($pages, [$this, 'sortByRecursivelySorting']);
            if (strtolower($this->settings['orderDirection']) === strtolower(QueryInterface::ORDER_DESCENDING)) {
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
     * @param array <Pages> $pages
     * @param string $rootPageUids Comma separated list of page uids
     * @return array<Pages>
     */
    protected function convertFlatToNestedPagesArray($pages, $rootPageUids)
    {
        $rootPageUidArray = GeneralUtility::intExplode(',', $rootPageUids);
        $rootPages = [];
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
    protected function fillChildPagesRecursivley($parentPage, array $pages)
    {
        $childPages = [];
        /** @var $page \PwTeaserTeam\PwTeaser\Domain\Model\Page */
        foreach ($pages as $page) {
            if ($page->getPid() === $parentPage->getUid()) {
                $this->fillChildPagesRecursivley($page, $pages);
                $childPages[] = $page;
            }
        }

        usort($childPages, function (Page $a, Page $b) {
            if ($a->getSorting() === $b->getSorting()) {
                return 0;
            }
            return ($a->getSorting() < $b->getSorting()) ? -1 : 1;
        });

        $parentPage->setChildPages($childPages);
        return $parentPage;
    }

    /**
     * @param PaginatorInterface $paginator
     * @param string|null $paginationClass
     * @return PaginationInterface
     */
    protected function getPagination($paginator, $paginationClass = null)
    {
        if (!empty($paginationClass) && class_exists($paginationClass)) {
            return GeneralUtility::makeInstance($paginationClass, $paginator);
        }

        return GeneralUtility::makeInstance(SimplePagination::class, $paginator);
    }
}
