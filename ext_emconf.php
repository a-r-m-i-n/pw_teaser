<?php

########################################################################
# Extension Manager/Repository config file for ext "pw_teaser".
#
# Auto generated 14-12-2010 14:38
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Professor Web Teaser',
	'description' => 'Extensions to create dynamic teasers, with data from page properties and its content elements. Professor Web Teaser based on Extbase and Fluid Template Engine.',
	'category' => 'plugin',
	'author' => 'Armin Ruediger Vieweg',
	'author_email' => 'info@professorweb.de',
	'author_company' => '',
	'shy' => '',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '0.1.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.4.0-0.0.0',
			'extbase' => '1.3.0beta2a-0.0.0',
			'fluid' => '1.3.0beta2-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:22:{s:12:"ext_icon.gif";s:4:"e922";s:17:"ext_localconf.php";s:4:"02cb";s:14:"ext_tables.php";s:4:"0fa9";s:14:"ext_tables.sql";s:4:"1022";s:16:"kickstarter.json";s:4:"7e3b";s:39:"Classes/Controller/TeaserController.php";s:4:"9aa5";s:31:"Classes/Domain/Model/Teaser.php";s:4:"568f";s:46:"Classes/Domain/Repository/TeaserRepository.php";s:4:"9d7f";s:28:"Configuration/TCA/Teaser.php";s:4:"3934";s:34:"Configuration/TypoScript/setup.txt";s:4:"bc80";s:40:"Resources/Private/Language/locallang.xml";s:4:"1da2";s:77:"Resources/Private/Language/locallang_csh_tx_pwteaser_domain_model_teaser.xml";s:4:"d81f";s:43:"Resources/Private/Language/locallang_db.xml";s:4:"a265";s:38:"Resources/Private/Layouts/default.html";s:4:"dc93";s:42:"Resources/Private/Partials/formErrors.html";s:4:"f5bc";s:44:"Resources/Private/Templates/Teaser/Edit.html";s:4:"2147";s:45:"Resources/Private/Templates/Teaser/Index.html";s:4:"51fe";s:44:"Resources/Private/Templates/Teaser/List.html";s:4:"d41d";s:43:"Resources/Private/Templates/Teaser/New.html";s:4:"5c94";s:44:"Resources/Private/Templates/Teaser/Show.html";s:4:"988f";s:35:"Resources/Public/Icons/relation.gif";s:4:"e615";s:59:"Resources/Public/Icons/tx_pwteaser_domain_model_teaser.gif";s:4:"905a";}',
);

?>