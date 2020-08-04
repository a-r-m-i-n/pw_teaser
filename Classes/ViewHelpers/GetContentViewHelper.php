<?php
namespace PwTeaserTeam\PwTeaser\ViewHelpers;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2020 Armin Vieweg <armin@v.ieweg.de>
 *      2016      Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
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
class GetContentViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('contents', 'array', 'Content elements');
        $this->registerArgument('as', 'string', 'the name of the iteration variable', true);
        $this->registerArgument('colPos', 'integer', 'column position to get content elements from', false, 0);
        $this->registerArgument('cType', 'string', 'the cType to filter content elements for');
        $this->registerArgument('index', 'integer', 'limits the output to n-th element');
    }

    public function render()
    {
        $contents = $this->arguments['contents'];
        if ($contents === null) {
            return '';
        }

        $output = '';
        $indexCount = 0;
        $breakNow = false;
        $asHasBeenSet = false;

        /** @var $content \PwTeaserTeam\PwTeaser\Domain\Model\Content */
        foreach ($contents as $content) {
            $contentCtype = $content->getCtype();
            $contentColPos = $content->getColPos();

            if ($contentColPos == $this->arguments['colPos']) {
                if ($this->arguments['cType'] === null || $contentCtype == $this->arguments['cType']) {
                    if ($this->arguments['index'] === null) {
                        $this->templateVariableContainer->add($this->arguments['as'], $content);
                        $asHasBeenSet = true;
                    } else {
                        if ($indexCount == $this->arguments['index']) {
                            $this->templateVariableContainer->add($this->arguments['as'], $content);
                            $asHasBeenSet = true;
                            $breakNow = true;
                        }
                    }
                }
            }

            if ($asHasBeenSet) {
                $output .= $this->renderChildren();
                $this->templateVariableContainer->remove($this->arguments['as']);
                $asHasBeenSet = false;
            }

            if ($breakNow) {
                break;
            }
            if ($this->arguments['cType'] === null || $contentCtype == $this->arguments['cType']) {
                $indexCount++;
            }
        }
        return $output;
    }
}
