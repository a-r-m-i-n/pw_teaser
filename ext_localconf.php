<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$extConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);
$actionNotToCache = '';
if ($extConfiguration['ENABLECACHE'] != '1') {
	$actionNotToCache = 'index';
}

Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'Pi1',
	array(
		'Teaser' => 'index',
	),
	array(
		'Teaser' => $actionNotToCache,
	)
);
?>