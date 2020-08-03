<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pw_teaser']);
$actionNotToCache = '';
if ($extConfiguration['ENABLECACHE'] == '0') {
    $actionNotToCache = 'index';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'PwTeaserTeam.' . 'pw_teaser',
    'Pi1',
    [
        'Teaser' => 'index',
    ],
    [
        'Teaser' => $actionNotToCache,
    ]
);

$rootLineFields = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(
    ',',
    $GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'],
    true
);
$rootLineFields[] = 'sorting';
$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] = implode(',', $rootLineFields);

if (TYPO3_MODE === 'BE') {
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Imaging\IconRegistry');
        $iconRegistry->registerIcon(
            'ext-pwteaser-wizard-icon',
            'TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider',
            array('source' => 'EXT:pw_teaser/Resources/Public/Icons/ext_icon_x2.png')
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
            mod.wizards.newContentElement.wizardItems.plugins.elements.pwteaser {
                iconIdentifier = ext-pwteaser-wizard-icon
                title = LLL:EXT:pw_teaser/Resources/Private/Language/locallang.xml:newContentElementWizardTitle
                description = LLL:EXT:pw_teaser/Resources/Private/Language/locallang.xml:newContentElementWizardDescription
                tt_content_defValues {
                    CType = list
                    list_type = pwteaser_pi1
                }
            }
        ');
}
