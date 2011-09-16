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
 * the page model
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Domain_Model_Content extends Tx_Extbase_DomainObject_AbstractEntity {
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
	 * @var string
	 */
	protected $image;


	/**
	 * Setter for image(s)
	 *
	 * @param string $image image
	 */
	public function setImage($image) {
		$this->image = $image;
	}

	/**
	 * Getter for images
	 *
	 * @return array images
	 */
	public function getImage() {
		$defaultDirectory = 'uploads/pics/';
		$images = t3lib_div::trimExplode(',', $this->image, TRUE);

		foreach ($images as $key => $imgage) {
			$images[$key] = $defaultDirectory . $imgage;
		}

		return $images;
	}

	/**
	 * Setter for bodytext
	 *
	 * @param integer $bodytext bodytext
	 */
	public function setBodytext($bodytext) {
		$this->bodytext = $bodytext;
	}

	/**
	 * Getter for bodytext
	 *
	 * @return integer bodytext
	 */
	public function getBodytext() {
		return $this->bodytext;
	}

	/**
	 * Setter for ctype
	 *
	 * @param string $ctype ctype
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