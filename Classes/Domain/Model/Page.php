<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Armin Ruediger Vieweg <info@professorweb.de>
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
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_PwTeaser_Domain_Model_Page extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * title
	 * @var string
	 * @validate NotEmpty
	 */
	protected $title;

	/**
	 * subtitle
	 * @var string
	 */
	protected $subtitle;

	/**
	 * navtitle
	 * @var string
	 */
	protected $navtitle;

	/**
	 * meta keywords
	 * @var string
	 */
	protected $metaKeywords;

	/**
	 * meta description
	 * @var string
	 */
	protected $metaDescription;

	/**
	 * alias
	 * @var string
	 */
	protected $alias;

	/**
	 * media
	 * @var string
	 */
	protected $media;

	/**
	 * creation date
	 * @var integer
	 */
	protected $crdate;

	/**
	 * timestamp
	 * @var integer
	 */
	protected $tstamp;

	/**
	 * new until
	 * @var integer
	 */
	protected $newUntil;

	/**
	 * author
	 * @var string
	 */
	protected $author;

	/**
	 * author email
	 * @var string
	 */
	protected $authorEmail;

	/**
	 * abstract
	 * @var string
	 */
	protected $abstract;

	/**
	 * contents
	 * @var array<Tx_PwTeaser_Domain_Model_Content>
	 */
	protected $contents;


	/**
	 * Setter for contents
	 *
	 * @param array<Tx_PwTeaser_Domain_Model_Content> $contents array of contents
	 */
	public function setContents($contents) {
		$this->contents = $contents;
	}

	/**
	 * Getter for contents
	 *
	 * @returns array<Tx_PwTeaser_Domain_Model_Content> contents
	 */
	public function getContents() {
		return $this->contents;
	}


	/**
	 * Setter for authorEmail
	 *
	 * @param string $authorEmail authorEmail
	 */
	public function setAuthorEmail($authorEmail) {
		$this->authorEmail = $authorEmail;
	}

	/**
	 * Getter for authorEmail
	 *
	 * @return string authorEmail
	 */
	public function getAuthorEmail() {
		return $this->authorEmail;
	}

	/**
	 * Setter for metaKeywords
	 *
	 * @param string $metaKeywords metaKeywords
	 */
	public function setMetaKeywords($metaKeywords) {
		$this->metaKeywords = $metaKeywords;
	}

	/**
	 * Getter for metaKeywords
	 *
	 * @return array array of metaKeywords
	 */
	public function getMetaKeywords() {
		$keywords = explode(',', $this->metaKeywords);
		foreach ($keywords as $key => $keyword) {
			$keywords[$key] = trim($keyword);
		}
		return $keywords;
	}


	/**
	 * Setter for metaDescription
	 *
	 * @param string $metaDescription metaDescription
	 */
	public function setMetaDescription($metaDescription) {
		$this->metaDescription = $metaDescription;
	}

	/**
	 * Getter for metaDescription
	 *
	 * @return string metaDescription
	 */
	public function getMetaDescription() {
		return $this->metaDescription;
	}

	/**
	 * Setter for alias
	 *
	 * @param string $alias alias
	 */
	public function setAlias($alias) {
		$this->alias = $alias;
	}

	/**
	 * Getter for alias
	 *
	 * @return string alias
	 */
	public function getAlias() {
		return $this->alias;
	}

	/**
	 * Setter for navtitle
	 *
	 * @param string $navtitle navtitle
	 */
	public function setNavtitle($navtitle) {
		$this->navtitle = $navtitle;
	}

	/**
	 * Getter for navtitle
	 *
	 * @return string navtitle
	 */
	public function getNavtitle() {
		return $this->navtitle;
	}

	/**
	 * Setter for abstract
	 *
	 * @param string $abstract abstract
	 */
	public function setAbstract($abstract) {
		$this->abstract = $abstract;
	}

	/**
	 * Getter for abstract
	 *
	 * @return string abstract
	 */
	public function getAbstract() {
		return $this->abstract;
	}

	/**
	 * Setter for subtitle
	 *
	 * @param string $subtitle subtitle
	 */
	public function setSubtitle($subtitle) {
		$this->subtitle = $subtitle;
	}

	/**
	 * Getter for subtitle
	 *
	 * @return string subtitle
	 */
	public function getSubtitle() {
		return $this->subtitle;
	}

	/**
	 * Setter for title
	 *
	 * @param string $title title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Getter for title
	 *
	 * @return string title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Setter for media
	 *
	 * @param string $media media
	 */
	public function setMedia($media) {
		$this->media = $media;
	}

	/**
	 * Getter for media
	 *
	 * @return string media
	 */
	public function getMedia() {
		$defaultDirectory = 'uploads/media/';
		$medias = explode(',', $this->media);

		foreach ($medias as $key => $media) {
			if (!empty($media)) {
				$medias[$key] = $defaultDirectory . $media;
			}
		}
		
		return $medias;
	}

	/**
	 * Setter for newUntil
	 *
	 * @param integer $newUntil newUntil
	 */
	public function setNewUntil($newUntil) {
		$this->newUntil = $newUntil;
	}

	/**
	 * Getter for newUntil
	 *
	 * @return integer newUntil
	 */
	public function getNewUntil() {
		return '@' . $this->newUntil;
	}

	/**
	 * isNew
	 *
	 * @return boolean true if the page is new
	 */
	public function getIsNew() {
		if (!empty($this->newUntil) && $this->newUntil != 0) {
			return TRUE;
			if ($this->newUntil < time()) {
				return TRUE;
			} else {
				return FALSE;
			}
		} else {
			return FALSE;
		}
	}

	/**
	 * Setter for crdate
	 *
	 * @param integer $crdate crdate
	 */
	public function setCrdate($crdate) {
		$this->crdate = $crdate;
	}

	/**
	 * Getter for crdate
	 *
	 * @return integer crdate
	 */
	public function getCrdate() {
		return '@' . $this->crdate;
	}

	/**
	 * Setter for tstamp
	 *
	 * @param integer $tstamp tstamp
	 */
	public function setTstamp($tstamp) {
		$this->tstamp = $tstamp;
	}

	/**
	 * Getter for tstamp
	 *
	 * @return integer tstamp
	 */
	public function getTstamp() {

		return '@' . $this->tstamp;
	}

	/**
	 * Setter for author
	 *
	 * @param string $author author
	 */
	public function setAuthor($author) {
		$this->author = $author;
	}

	/**
	 * Getter for author
	 *
	 * @return string author
	 */
	public function getAuthor() {
		return $this->author;
	}

}
?>