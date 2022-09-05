<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'pw_teaser',
    'Pi1',
    'Page Teaser (pw_teaser)'
);

$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('pw_teaser');
$pluginSignature = strtolower($extensionName) . '_pi1';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $pluginSignature,
    'FILE:EXT:' . 'pw_teaser' . '/Configuration/FlexForms/flexform_teaser.xml'
);
