<?php

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 */

if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'pw_teaser',
    'Pi1',
    [
        \PwTeaserTeam\PwTeaser\Controller\TeaserController::class => 'index',
    ]
);

$rootLineFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
    ',',
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'],
    true
);
$rootLineFields[] = 'sorting';
$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(',', $rootLineFields);

/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\IconRegistry');
$iconRegistry->registerIcon(
    'ext-pwteaser-wizard-icon',
    'TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider',
    ['source' => 'EXT:pw_teaser/Resources/Public/Icons/Extension_x2.png']
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    mod.wizards.newContentElement.wizardItems.plugins.elements.pwteaser {
        iconIdentifier = ext-pwteaser-wizard-icon
        title = LLL:EXT:pw_teaser/Resources/Private/Language/locallang.xlf:newContentElementWizardTitle
        description = LLL:EXT:pw_teaser/Resources/Private/Language/locallang.xlf:newContentElementWizardDescription
        tt_content_defValues {
            CType = list
            list_type = pwteaser_pi1
        }
    }
');
