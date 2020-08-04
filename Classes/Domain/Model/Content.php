<?php
namespace PwTeaserTeam\PwTeaser\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2016 Armin Ruediger Vieweg <armin@v.ieweg.de>
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
