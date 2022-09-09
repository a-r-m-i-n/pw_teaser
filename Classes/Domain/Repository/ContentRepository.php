<?php
namespace PwTeaserTeam\PwTeaser\Domain\Repository;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2016 Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
 *  |     2016 Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
 */

/**
 * Repository for Content model
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ContentRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

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
    }

    /**
     * Returns all objects of this repository which matches the given pid. This
     * overwritten method exists, to perform sorting
     *
     * @param integer $pid Pid to search for
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult All found objects, will be
     *         empty if there are no objects
     */
    public function findByPid($pid)
    {
        $query = $this->createQuery();
        $query->matching($query->equals('pid', $pid));
        $query->setOrderings(
            [
                'sorting' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING
            ]
        );
        return $query->execute();
    }

    /**
     * Returns all objects of this repository which are located inside the
     * given pages
     *
     * @param array <\PwTeaserTeam\PwTeaser\Domain\Model\Page> $pages Pages to get content elements
     * @return \TYPO3\CMS\Extbase\Persistence\Generic\QueryResult All found objects, will be
     *         empty if there are no objects
     */
    public function findByPages($pages)
    {
        $query = $this->createQuery();
        $constraint = [];

        foreach ($pages as $page) {
            $constraint[] = $query->equals('pid', $page->getUid());
        }

        $query->matching($query->logicalOr($constraint));

        return $query->execute();
    }
}
