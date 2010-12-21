<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_pwteaser_domain_model_teaser'] = array(
	'ctrl' => $TCA['tx_pwteaser_domain_model_teaser']['ctrl'],
	'interface' => array(
		'showRecordFieldList'	=> 'name,teaser_that,order_by'
	),
	'types' => array(
		'1' => array('showitem'	=> 'name,teaser_that,order_by')
	),
	'palettes' => array(
		'1' => array('showitem'	=> '')
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude'			=> 1,
			'label'				=> 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config'			=> array(
				'type'					=> 'select',
				'foreign_table'			=> 'sys_language',
				'foreign_table_where'	=> 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array(
			'displayCond'	=> 'FIELD:sys_language_uid:>:0',
			'exclude'		=> 1,
			'label'			=> 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config'		=> array(
				'type'			=> 'select',
				'items'			=> array(
					array('', 0),
				),
				'foreign_table' => 'tx_pwteaser_domain_model_teaser',
				'foreign_table_where' => 'AND tx_pwteaser_domain_model_teaser.uid=###REC_FIELD_l18n_parent### AND tx_pwteaser_domain_model_teaser.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array(
			'config'		=>array(
				'type'		=>'passthrough'
			)
		),
		't3ver_label' => array(
			'displayCond'	=> 'FIELD:t3ver_label:REQ:true',
			'label'			=> 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config'		=> array(
				'type'		=>'none',
				'cols'		=> 27
			)
		),
		'hidden' => array(
			'exclude'	=> 1,
			'label'		=> 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'	=> array(
				'type'	=> 'check'
			)
		),
		'name' => array(
			'exclude'	=> 0,
			'label'		=> 'LLL:EXT:pw_teaser/Resources/Private/Language/locallang_db.xml:tx_pwteaser_domain_model_teaser.name',
			'config'	=> array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim,required'
			)
		),
		'teaser_that' => array(
			'exclude'	=> 0,
			'label'		=> 'LLL:EXT:pw_teaser/Resources/Private/Language/locallang_db.xml:tx_pwteaser_domain_model_teaser.teaser_that',
			'config'	=> array(
				'type' => 'select',
				'items' => array(
					array('-- Label --', 0),
				),
				'size' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			)
		),
		'order_by' => array(
			'exclude'	=> 0,
			'label'		=> 'LLL:EXT:pw_teaser/Resources/Private/Language/locallang_db.xml:tx_pwteaser_domain_model_teaser.order_by',
			'config'	=> array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			)
		),
	),
);
?>