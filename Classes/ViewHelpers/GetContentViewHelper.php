<?php
namespace PwTeaserTeam\PwTeaser\ViewHelpers;

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
 * This class creates links to social bookmark services, recommending the
 * current front-end page.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GetContentViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * Get content
	 *
	 * @param array $contents array which contains content elements
	 * @param string $as the name of the iteration variable
	 * @param integer $colPos column position to get content elements from, default is 0 (normal)
	 * @param string $cType the cType to filter content elements for, default is NULL
	 * @param integer $index limits the output to n-th element. default is NULL which disables the limitation, 0 would
	 *        limit the output to the first found content element
	 * @return string Rendered string
	 */
	public function render($contents, $as, $colPos = 0, $cType = NULL, $index = NULL) {
		if ($contents === NULL) {
			return '';
		}

		$output = '';
		$indexCount = 0;
		$breakNow = FALSE;
		$asHasBeenSet = FALSE;

		/** @var $content \PwTeaserTeam\PwTeaser\Domain\Model\Content */
		foreach ($contents as $content) {
			$contentCtype = $content->getCtype();
			$contentColPos = $content->getColPos();

			if ($contentColPos == $colPos) {
				if ($cType === NULL || $contentCtype == $cType) {
					if ($index === NULL) {
						$this->templateVariableContainer->add($as, $content);
						$asHasBeenSet = TRUE;
					} else {
						if ($indexCount == $index) {
							$this->templateVariableContainer->add($as, $content);
							$asHasBeenSet = TRUE;
							$breakNow = TRUE;
						}
					}
				}
			}

			if ($asHasBeenSet == TRUE) {
				$output .= $this->renderChildren();
				$this->templateVariableContainer->remove($as);
				$asHasBeenSet = FALSE;
			}

			if ($breakNow) {
				break;
			} else {
				if ($cType === NULL || $contentCtype == $cType) {
					$indexCount++;
				}
			}
		}
		return $output;
	}
}