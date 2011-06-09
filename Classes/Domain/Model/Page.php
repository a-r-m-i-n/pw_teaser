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
class Tx_PwTeaser_Domain_Model_Page extends Tx_Extbase_DomainObject_AbstractEntity {

	/**
	 * doktype
	 * @var integer
	 */
	protected $doktype;

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
	 * navTitle
	 * @var string
	 */
	protected $navTitle;

	/**
	 * meta keywords
	 * @var string
	 */
	protected $keywords;

	/**
	 * meta description
	 * @var string
	 */
	protected $description;

	/**
	 * abstract
	 * @var string
	 */
	protected $abstract;

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
	 * lastUpdated
	 * @var integer
	 */
	protected $lastUpdated;

	/**
	 * starttime
	 * @var integer
	 */
	protected $starttime;

	/**
	 * endtime
	 * @var integer
	 */
	protected $endtime;

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
	 * contents
	 * @var array<Tx_PwTeaser_Domain_Model_Content>
	 */
	protected $contents;

	/**
	 * custom attributes
	 * which can be setted by hooks
	 *
	 * @var array<mixed>
	 */
	protected $_customAttributes;


	/**
	 * Sets a custom attribute
	 *
	 * @param string $name The name of the attribute
	 * @param mixed $value Attribute's value
	 *
	 * @return void
	 */
	public function setCustomAttribute($name, $value) {
		$this->_customAttributes[$name] = $value;
	}

	/**
	 * Returns the value of a custom attribute
	 *
	 * @param string $name Name of attribute
	 *
	 * @return mixed The value of a custom attribute
	 */
	public function getCustomAttribute($name = NULL) {
		if ($name !== NULL) {
			return $this->_customAttributes[$name];
		}
		return NULL;
	}

	/**
	 * Magic method which is called if an unknown method is called. If the unknown
	 * method starts with 'get' the requested attribute will be taken and returned
	 * from the _customAttribute array
	 *
	 * @param string $name Name of unknown method
	 * @param array arguments Arguments of call
	 *
	 * @return mixed
	 */
	public function __call($name, $arguments) {
		if (substr(strtolower($name), 0, 3) == 'get' && strlen($name) > 3) {
			$attribute = strtolower(substr($name, 3));
			return $this->getCustomAttribute($attribute);
		}
	}

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
	 * Setter for keywords
	 *
	 * @param string $keywords keywords
	 */
	public function setKeywords($keywords) {
		var_dump($this->metaKeywords);
		$this->metaKeywords = $keywords;
	}

	/**
	 * Getter for keywords
	 *
	 * @return array array of keywords
	 */
	public function getKeywords() {

		return(t3lib_div::trimExplode(',', $this->keywords, TRUE));
	}

	/**
	 * Getter for keywords. Returns a string
	 *
	 * @return string keywords as string
	 */
	public function getKeywordsAsString() {
		return $this->keywords;
	}


	/**
	 * Setter for description
	 *
	 * @param string $description description
	 */
	public function setDescription($description) {
		$this->metaDescription = $description;
	}

	/**
	 * Getter for metaDescription
	 *
	 * @return string metaDescription
	 */
	public function getDescription() {
		return $this->description;
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
	 * Setter for navTitle
	 *
	 * @param string $navTitle navTitle
	 */
	public function setNavTitle($navTitle) {
		$this->navTitle = $navTitle;
	}

	/**
	 * Getter for navTitle
	 *
	 * @return string navTitle
	 */
	public function getNavTitle() {
		return $this->navTitle;
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
		$defaultMediaDirectory = 'uploads/media/';
		$media = t3lib_div::trimExplode(',', $this->media, TRUE);

		foreach ($media as $key => $medium) {
			$media[$key] = $defaultMediaDirectory . $medium;
		}
		return $media;
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
	 * Setter for lastUpdated
	 *
	 * @param integer $lastUpdated lastUpdated
	 */
	public function setLastUpdated($lastUpdated) {
		$this->lastUpdated = $lastUpdated;
	}

	/**
	 * Getter for lastUpdated
	 *
	 * @return integer lastUpdated
	 */
	public function getLastUpdated() {
		return '@' . $this->lastUpdated;
	}

	/**
	 * Setter for starttime
	 *
	 * @param integer $starttime starttime
	 */
	public function setStarttime($starttime) {
		$this->starttime = $starttime;
	}

	/**
	 * Getter for starttime
	 *
	 * @return integer starttime
	 */
	public function getStarttime() {
		return '@' . $this->starttime;
	}

	/**
	 * Setter for endtime
	 *
	 * @param integer $endtime endtime
	 */
	public function setEndtime($endtime) {
		$this->endtime = $endtime;
	}

	/**
	 * Getter for endtime
	 *
	 * @return integer endtime
	 */
	public function getEndtime() {
		return '@' . $this->endtime;
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

	/**
	 * Getter for doktype
	 *
	 * @return integer the doktype
	 */
	public function getDoktype() {
		return $this->doktype;
	}

	/**
	 * Setter for doktype
	 *
	 * @param integer $doktype the doktype
	 *
	 * @return void
	 */
	public function setDoktype($doktype) {
		$this->doktype = $doktype;
	}

}
?>