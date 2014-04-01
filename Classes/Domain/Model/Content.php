<?php
namespace PwTeaserTeam\PwTeaser\Domain\Model;

/***************************************************************
*  Copyright notice
*
*  (c) 2011-2014 Armin Ruediger Vieweg <armin@v.ieweg.de>
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

/**
 * the page model
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Content extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * ctype
	 * @var string
	 */
	protected $ctype;

	/**
	 * colPos
	 * @var integer
	 */
	protected $colPos;

	/**
	 * header
	 * @var string
	 */
	protected $header;

	/**
	 * bodytext
	 * @var string
	 */
	protected $bodytext;

	/**
	 * image
	 * It may contain multiple images, but TYPO3 called this field just "image"
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
	 */
	protected $image;


	/**
	 * Constructor
	 */
	public function __construct() {
		$this->image = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}

	/**
	 * Setter for image(s)
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $image
	 * @return void
	 */
	public function setImage(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $image) {
		$this->image = $image;
	}

	/**
	 * Getter for images
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage images
	 */
	public function getImage() {
		return $this->image;
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $image
	 * @return void
	 */
	public function addImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $image) {
		$this->image->attach($image);
	}

	/**
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $image
	 * @return void
	 */
	public function removeImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $image) {
		$this->image->detach($image);
	}

	/**
	 * Returns image files as array (with all attributes)
	 *
	 * @return array
	 */
	public function getImageFiles() {
		$imageFiles = array();
		/** @var \TYPO3\CMS\Extbase\Domain\Model\FileReference $image */
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
	public function setBodytext($bodytext) {
		$this->bodytext = $bodytext;
	}

	/**
	 * Getter for bodytext
	 *
	 * @return string bodytext
	 */
	public function getBodytext() {
		return $this->bodytext;
	}

	/**
	 * Setter for ctype
	 *
	 * @param string $ctype ctype
	 * @return void
	 */
	public function setCtype($ctype) {
		$this->ctype = $ctype;
	}

	/**
	 * Getter for ctype
	 *
	 * @return string ctype
	 */
	public function getCtype() {
		return $this->ctype;
	}

	/**
	 * Setter for colPos
	 *
	 * @param integer $colPos colPos
	 * @return void
	 */
	public function setColPos($colPos) {
		$this->colPos = $colPos;
	}

	/**
	 * Getter for colPos
	 *
	 * @return integer colPos
	 */
	public function getColPos() {
		return $this->colPos;
	}

	/**
	 * Setter for header
	 *
	 * @param string $header header
	 * @return void
	 */
	public function setHeader($header) {
		$this->header = $header;
	}

	/**
	 * Getter for header
	 *
	 * @return string header
	 */
	public function getHeader() {
		return $this->header;
	}
}
?>