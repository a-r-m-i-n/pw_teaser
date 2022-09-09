<?php
namespace PwTeaserTeam\PwTeaser\ViewHelpers;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 */

/**
 * This class strips html and php code out of a string
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class StripTagsViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * Strips html and php code out of a string
     *
     * @param string $string The string which will be stripped
     * @return string the stripped string
     */
    public function render($string = null)
    {
        if ($string === null) {
            $string = html_entity_decode($this->renderChildren());
            if ($string === null) {
                return '';
            }
        }
        return strip_tags($string);
    }
}
