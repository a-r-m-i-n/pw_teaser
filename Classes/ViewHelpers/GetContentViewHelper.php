<?php
namespace PwTeaserTeam\PwTeaser\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 *  |     2016 Tim Klein-Hitpass <tim.klein-hitpass@diemedialen.de>
 *  |     2016 Kai Ratzeburg <kai.ratzeburg@diemedialen.de>
 */

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
