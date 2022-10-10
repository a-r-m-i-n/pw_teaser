<?php
namespace PwTeaserTeam\PwTeaser\Domain\Model;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2016 Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
 *  |     2016 Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Content model
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Content extends AbstractEntity
{

    /**
     * ctype
     *
     * @var string
     */
    protected $ctype;

    /**
     * colPos
     *
     * @var integer
     */
    protected $colPos;

    /**
     * header
     *
     * @var string
     */
    protected $header;

    /**
     * bodytext
     *
     * @var string
     */
    protected $bodytext;

    /**
     * It may contain multiple images, but TYPO3 called this field just "image"
     *
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $image;

    /**
     * It may contain multiple assets"
     *
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
     */
    protected $assets;

    /**
     * Categories
     *
     * @var ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\Category>
     */
    protected $categories;

    /**
     * Complete row (from database) of this content element
     *
     * @var array
     */
    protected $contentRow;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->image = new ObjectStorage();
        $this->assets = new ObjectStorage();
    }

    /**
     * Setter for images
     *
     * @param ObjectStorage $image
     * @return void
     */
    public function setImage(ObjectStorage $image)
    {
        $this->image = $image;
    }

    /**
     * Getter for images
     *
     * @return ObjectStorage images
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Add image
     *
     * @param FileReference $image
     * @return void
     */
    public function addImage(FileReference $image)
    {
        $this->image->attach($image);
    }

    /**
     * Remove image
     *
     * @param FileReference $image
     * @return void
     */
    public function removeImage(FileReference $image)
    {
        $this->image->detach($image);
    }

    /**
     * Returns image files as array (with all attributes)
     *
     * @return array
     */
    public function getImageFiles()
    {
        $imageFiles = [];
        /** @var FileReference $image */
        foreach ($this->getImage() as $image) {
            $imageFiles[] = $image->getOriginalResource()->toArray();
        }
        return $imageFiles;
    }

    /**
     * Setter for assets
     *
     * @param ObjectStorage $assets
     * @return void
     */
    public function setAssets(ObjectStorage $assets)
    {
        $this->assets = $assets;
    }

    /**
     * Getter for assets
     *
     * @return ObjectStorage assets
     */
    public function getAssets()
    {
        return $this->assets;
    }

    /**
     * Add assets
     *
     * @param FileReference $assets
     * @return void
     */
    public function addAssets(FileReference $assets)
    {
        $this->assets->attach($assets);
    }

    /**
     * Remove assets
     *
     * @param FileReference $assets
     * @return void
     */
    public function removeAssets(FileReference $assets)
    {
        $this->assets->detach($assets);
    }

    /**
     * Returns assets files as array (with all attributes)
     *
     * @return array
     */
    public function getAssetsFiles()
    {
        $assetsFiles = [];
        /** @var FileReference $assets */
        foreach ($this->getAssets() as $assets) {
            $assetsFiles[] = $assets->getOriginalResource()->toArray();
        }
        return $assetsFiles;
    }

    /**
     * Setter for bodytext
     *
     * @param string $bodytext bodytext
     * @return void
     */
    public function setBodytext($bodytext)
    {
        $this->bodytext = $bodytext;
    }

    /**
     * Getter for bodytext
     *
     * @return string bodytext
     */
    public function getBodytext()
    {
        return $this->bodytext;
    }

    /**
     * Setter for ctype
     *
     * @param string $ctype ctype
     * @return void
     */
    public function setCtype($ctype)
    {
        $this->ctype = $ctype;
    }

    /**
     * Getter for ctype
     *
     * @return string ctype
     */
    public function getCtype()
    {
        return $this->ctype;
    }

    /**
     * Setter for colPos
     *
     * @param integer $colPos colPos
     * @return void
     */
    public function setColPos($colPos)
    {
        $this->colPos = $colPos;
    }

    /**
     * Getter for colPos
     *
     * @return integer colPos
     */
    public function getColPos()
    {
        return $this->colPos;
    }

    /**
     * Setter for header
     *
     * @param string $header header
     * @return void
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    /**
     * Getter for header
     *
     * @return string header
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Getter for categories
     *
     * @return ObjectStorage
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Setter for categories
     *
     * @param ObjectStorage $categories
     * @return void
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * Add category
     *
     * @param Category $category
     * @return void
     */
    public function addCategory(Category $category)
    {
        $this->categories->attach($category);
    }

    /**
     * Remove category
     *
     * @param Category $category
     * @return void
     */
    public function removeCategory(Category $category)
    {
        $this->categories->detach($category);
    }

    /**
     * Checks for attribute in _contentRow
     *
     * @param string $name Name of unknown method
     * @param array arguments Arguments of call
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (substr(strtolower($name), 0, 3) == 'get' && strlen($name) > 3) {
            $attributeName = lcfirst(substr($name, 3));

            if (empty($this->contentRow)) {
                /** @var PageRepository $pageSelect */
                $pageSelect = $GLOBALS['TSFE']->sys_page;
                $contentRow = $pageSelect->getRawRecord('tt_content', $this->getUid());
                foreach ($contentRow as $key => $value) {
                    $this->contentRow[GeneralUtility::underscoredToLowerCamelCase($key)] = $value;
                }
            }
            if (isset($this->contentRow[$attributeName])) {
                return $this->contentRow[$attributeName];
            }
        }
    }

    /**
     * Get raw content row
     *
     * @return array
     */
    public function getContentRow()
    {
        return $this->contentRow;
    }
}
