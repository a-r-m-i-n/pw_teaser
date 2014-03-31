<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
$actionNotToCache = '';
if ($extConfiguration['ENABLECACHE'] == '0') {
	$actionNotToCache = 'index';
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'PwTeaserTeam.' . $_EXTKEY,
	'Pi1',
	array(
		'Teaser' => 'index',
	),
	array(
		'Teaser' => $actionNotToCache,
	)
);
?>