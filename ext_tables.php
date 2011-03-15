<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'Teaser (pw_teaser)'
);

$extensionName = t3lib_div::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName) . '_pi1';

$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_teaser.xml');

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Extbase Teaser');

//t3lib_extMgm::addLLrefForTCAdescr('tx_pwteaser_domain_model_teaser', 'EXT:pw_teaser/Resources/Private/Language/locallang_csh_tx_pwteaser_domain_model_teaser.xml');
//t3lib_extMgm::allowTableOnStandardPages('tx_pwteaser_domain_model_teaser');
//$TCA['tx_pwteaser_domain_model_teaser'] = array(
//	'ctrl' => array(
//		'title'						=> 'LLL:EXT:pw_teaser/Resources/Private/Language/locallang_db.xml:tx_pwteaser_domain_model_teaser',
//		'label'						=> 'name',
//		'tstamp'					=> 'tstamp',
//		'crdate'					=> 'crdate',
//		'versioningWS'				=> 2,
//		'versioning_followPages'	=> TRUE,
//		'origUid'					=> 't3_origuid',
//		'languageField'				=> 'sys_language_uid',
//		'transOrigPointerField'		=> 'l18n_parent',
//		'transOrigDiffSourceField'	=> 'l18n_diffsource',
//		'delete'					=> 'deleted',
//		'enablecolumns'				=> array(
//			'disabled'		=> 'hidden'
//		),
//		'dynamicConfigFile'			=> t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/TCA/Teaser.php',
//		'iconfile'					=> t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_pwteaser_domain_model_teaser.gif'
//	)
//);
?>