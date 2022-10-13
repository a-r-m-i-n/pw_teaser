<?php
namespace PwTeaserTeam\PwTeaser\Domain\Repository;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2016 Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
 *  |     2016 Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
 */
use PwTeaserTeam\PwTeaser\Domain\Model\Page;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for Page model
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{
    /** Category Mode: Or */
    const CATEGORY_MODE_OR = 1;
    /** Category Mode: And */
    const CATEGORY_MODE_AND = 2;
    /** Category Mode: Or Not */
    const CATEGORY_MODE_OR_NOT = 3;
    /** Category Mode: And Not */
    const CATEGORY_MODE_AND_NOT = 4;

    /**
     * page attribute to order by
     *
     * @var string
     */
    protected $orderBy = 'uid';

    /**
     * Direction to order. Default is ascending.
     *
     * @var string
     */
    protected $orderDirection = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\QueryInterface
     */
    protected $query = null;

    /**
     * @var array
     */
    protected $queryConstraints = [];

    /**
     * Initializes the repository.
     *
     * @return void
     */
    public function initializeObject()
    {
        $querySettings = $this->createQuery()->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
        $this->query = $this->createQuery();
    }

    /**
     * Returns all objects of this repository which match the pid
     *
     * @param integer $pid the pid to search for
     * @return array All found pages, will be empty if the result is empty
     */
    public function findByPid($pid)
    {
        $translatedPid = $this->translatePids([$pid]);

        $this->addQueryConstraint($this->query->equals('pid', reset($translatedPid)));
        return $this->executeQuery();
    }

    /**
     * Returns all objects of this repository which are children of the matched
     * pid (recursively)
     *
     * @param integer $pid the pid to search for recursively
     * @param integer $recursionDepthFrom Start of recursion depth
     * @param integer $recursionDepth Depth of recursion
     * @return array All found pages, will be empty if the result is empty
     */
    public function findByPidRecursively($pid, $recursionDepthFrom, $recursionDepth)
    {
        return $this->findChildrenRecursivelyByPidList($pid, $recursionDepthFrom, $recursionDepth);
    }

    /**
     * Returns all objects of this repository which are in the pidlist
     *
     * @param string $pidlist comma seperated list of pids to search for
     * @param boolean $orderByPlugin setting of ordering by plugin
     * @return array All found pages, will be empty if the result is empty
     */
    public function findByPidList($pidlist, $orderByPlugin = false)
    {
        $pagePids = GeneralUtility::intExplode(',', $pidlist, true);

        // early return when list is empty to prevent sql exception
        if (empty($pagePids)) {
            return [];
        }

        $query = $this->query;
        $this->addQueryConstraint($query->in('uid', $this->translatePids($pagePids)));
        $query->matching($query->logicalAnd($this->queryConstraints));

        if ($orderByPlugin == false) {
            $this->handleOrdering($query);
            $results = $query->execute();
            $this->resetQuery();
            return $this->handlePageLocalization($results);
        } else {
            $results = $query->execute();
            $this->resetQuery();
            return $this->orderByPlugin($pagePids, $this->handlePageLocalization($results));
        }
    }

    /**
     * Creates array of result items, with the order of given pagePids
     *
     * @param array $pagePids pagePids to order for
     * @param array $results results to reorder
     * @return array results ordered by plugin
     */
    protected function orderByPlugin(array $pagePids, array $results)
    {
        $sortedResults = [];
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
    public function findChildrenByPidList($pidlist)
    {
        $pagePids = GeneralUtility::intExplode(',', $pidlist, true);

        // early return when list is empty to prevent sql exception
        if (empty($pagePids)) {
            return [];
        }

        $this->addQueryConstraint($this->query->in('pid', $this->translatePids($pagePids)));
        return $this->executeQuery();
    }

    /**
     * Returns all objects of this repository which are children of pages in the
     * pidlist (recursively)
     *
     * @param string $pidlist comma seperated list of pids to search for
     * @param integer $recursionDepthFrom Start level for recursion
     * @param integer $recursionDepth Depth of recursion
     * @return array All found pages, will be empty if the result is empty
     */
    public function findChildrenRecursivelyByPidList($pidlist, $recursionDepthFrom, $recursionDepth)
    {
        $pagePids = $this->getRecursivePageList($pidlist, $recursionDepthFrom, $recursionDepth);
        $translatedPids = $this->translatePids($pagePids);
        if (!empty($translatedPids)) {
            $this->addQueryConstraint($this->query->in('uid', $translatedPids));
        } else {
            $this->addQueryConstraint($this->query->in('uid', $pagePids));
        }

        return $this->executeQuery();
    }

    protected function translatePids(array $pidList, ?int $languageUid = null): array
    {
        if (empty($pidList)) {
            return $pidList;
        }

        if (!$languageUid) {
            /** @var Context $context */
            $context = GeneralUtility::makeInstance(Context::class);
            $languageUid = $context->getPropertyFromAspect('language', 'id');
        }

        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);

        $translatedPidList = [];
        foreach ($pidList as $pid) {
            $queryBuilder = $pool->getQueryBuilderForTable('pages');
            $translatedRow = $queryBuilder->select('*')
                ->from('pages')
                ->where('l10n_parent = :pid AND sys_language_uid = :lang')
                ->setParameter('pid', $pid)
                ->setParameter('lang', $languageUid)
                ->setMaxResults(1)
                ->execute()
                ->fetchAssociative();
            if ($translatedRow) {
                $translatedPidList[$pid] = $translatedRow['uid'];
            }
            else {
                $translatedPidList[$pid] = $pid;
            }
        }

        return array_values($translatedPidList);
    }

    /**
     * Adds query constraint to array
     *
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint Constraint to add
     * @return void
     */
    protected function addQueryConstraint(\TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface $constraint)
    {
        $this->queryConstraints[] = $constraint;
    }

    /**
     * Add category constraint
     *
     * @param array $categories
     * @param boolean $isAnd If TRUE categories get a logicalAnd. Otherwise a logicalOr.
     * @param boolean $isNot If TRUE categories get a logicalNot operator. Otherwise not.
     * @return void
     */
    public function addCategoryConstraint(array $categories, $isAnd = true, $isNot = false)
    {
        if ($isAnd === true && $isNot === false) {
            $this->queryConstraints[] = $this->query->logicalAnd($this->buildCategoryConstraint($categories));
        }
        if ($isAnd === true && $isNot === true) {
            $this->queryConstraints[] = $this->query->logicalNot(
                $this->query->logicalAnd(
                    $this->buildCategoryConstraint($categories)
                )
            );
        }
        if ($isAnd === false && $isNot === false) {
            $this->queryConstraints[] = $this->query->logicalOr($this->buildCategoryConstraint($categories));
        }
        if ($isAnd === false && $isNot === true) {
            $this->queryConstraints[] = $this->query->logicalNot(
                $this->query->logicalOr(
                    $this->buildCategoryConstraint($categories)
                )
            );
        }
    }

    /**
     * Build category constraint for each category (contains)
     *
     * @param array $categories
     * @return array
     */
    protected function buildCategoryConstraint(array $categories)
    {
        $contraints = [];
        foreach ($categories as $category) {
            $contraints[] = $this->query->contains('categories', $category);
        }
        return $contraints;
    }

    /**
     * Finalize given query constraints and executes the query
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface Result of query
     */
    protected function executeQuery()
    {
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
    protected function handlePageLocalization(\TYPO3\CMS\Extbase\Persistence\Generic\QueryResult $pages)
    {
        /** @var Context $context */
        $context = GeneralUtility::makeInstance(Context::class);
        $currentLangUid = $context->getPropertyFromAspect('language', 'id');
        $displayedPages = [];

        /** @var Page $page */
        foreach ($pages as $page) {
            if ($currentLangUid === 0) {
                if ($page->getL18nConfiguration() !== Page::L18N_HIDE_DEFAULT_LANGUAGE &&
                    $page->getL18nConfiguration() !== Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS) {
                    $displayedPages[] = $page;
                }
            } else {
                /** @var \TYPO3\CMS\Frontend\Page\PageRepository $pageSelect */
                $pageSelect = $GLOBALS['TSFE']->sys_page;
                $pageRowWithOverlays = $pageSelect->getPage($page->getUid());

                if ((boolean)$GLOBALS['TYPO3_CONF_VARS']['FE']['hidePagesIfNotTranslatedByDefault'] === false) {
                    if (!($page->getL18nConfiguration() === Page::L18N_HIDE_IF_NO_TRANSLATION_EXISTS ||
                            $page->getL18nConfiguration() === Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS) ||
                        isset($pageRowWithOverlays['_PAGES_OVERLAY'])) {
                        $displayedPages[] = $page;
                    }
                } else {
                    if (($page->getL18nConfiguration() === Page::L18N_HIDE_IF_NO_TRANSLATION_EXISTS ||
                            $page->getL18nConfiguration() === Page::L18N_HIDE_ALWAYS_BUT_TRANSLATION_EXISTS) &&
                        !isset($pageRowWithOverlays['_PAGES_OVERLAY']) ||
                        isset($pageRowWithOverlays['_PAGES_OVERLAY'])) {
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
     * @param integer $recursionDepthFrom Start of recursion depth
     * @param integer $recursionDepth Depth of recursion
     * @return array Found subpages, recursivley
     */
    protected function getRecursivePageList($pidlist, $recursionDepthFrom, $recursionDepth)
    {
        /** @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $contentObjectRenderer */
        $contentObjectRenderer = GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');

        $pagePids = [];
        $pids = GeneralUtility::intExplode(',', $pidlist, true);
        foreach ($pids as $pid) {
            $pageList = GeneralUtility::intExplode(
                ',',
                $contentObjectRenderer->getTreeList($pid, $recursionDepth, $recursionDepthFrom),
                true
            );
            $pagePids = array_merge($pagePids, $pageList);
            if ($recursionDepthFrom === 0) {
                array_unshift($pagePids, $pid);
            }
        }
        return array_unique($pagePids);
    }

    /**
     * Sets the order by which is used by all find methods
     *
     * @param string $orderBy property to order by
     * @return void
     */
    public function setOrderBy($orderBy)
    {
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
    public function setOrderDirection($orderDirection)
    {
        if ($orderDirection == 'desc' || $orderDirection == 1) {
            $this->orderDirection = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
        } else {
            $this->orderDirection = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;
        }
    }

    /**
     * Sets the query limit
     *
     * @param integer $limit The limit of elements to show
     * @return void
     */
    public function setLimit($limit)
    {
        $this->query->setLimit($limit);
    }

    /**
     * Sets the nav_hide_state flag
     *
     * @param boolean $showNavHiddenItems If TRUE lets show items which should not be visible in navigation.
     *        Default is FALSE.
     * @return void
     */
    public function setShowNavHiddenItems($showNavHiddenItems)
    {
        if ($showNavHiddenItems === true) {
            $this->addQueryConstraint($this->query->in('nav_hide', [0, 1]));
        } else {
            $this->addQueryConstraint($this->query->in('nav_hide', [0]));
        }
    }

    /**
     * Sets doktypes to filter for
     *
     * @param array $dokTypesToFilterFor doktypes as array, may be empty
     * @return void
     */
    public function setFilteredDokType(array $dokTypesToFilterFor)
    {
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
    public function setIgnoreOfUid($currentPageUid)
    {
        $this->addQueryConstraint($this->query->logicalNot($this->query->equals('uid', $currentPageUid)));
        $this->addQueryConstraint($this->query->logicalNot($this->query->equals('l10n_parent', $currentPageUid)));
    }

    /**
     * Adds handle of ordering to query object
     *
     * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $query
     * @return void
     */
    protected function handleOrdering(\TYPO3\CMS\Extbase\Persistence\QueryInterface $query)
    {
        $query->setOrderings([$this->orderBy => $this->orderDirection]);
    }

    /**
     * Resets query and queryConstraints after execution
     *
     * @return void
     */
    protected function resetQuery()
    {
        unset($this->query);
        $this->query = $this->createQuery();
        unset($this->queryConstraints);
        $this->queryConstraints = [];
    }
}
