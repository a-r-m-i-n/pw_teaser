<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "pw_teaser".
 *
 * Auto generated 24-04-2014 18:26
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Page Teaser (with Fluid)',
	'description' => 'Create powerful, dynamic page teasers with data from page properties and its content elements. Based on Extbase and Fluid Template Engine.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '3.1.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Armin Ruediger Vieweg',
	'author_email' => 'armin@v.ieweg.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-6.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:28:{s:21:"ext_conf_template.txt";s:4:"59e5";s:12:"ext_icon.gif";s:4:"b237";s:17:"ext_localconf.php";s:4:"e8aa";s:14:"ext_tables.php";s:4:"a972";s:14:"ext_tables.sql";s:4:"d41d";s:24:"ext_typoscript_setup.txt";s:4:"b545";s:39:"Classes/Controller/TeaserController.php";s:4:"5001";s:32:"Classes/Domain/Model/Content.php";s:4:"6a06";s:29:"Classes/Domain/Model/Page.php";s:4:"6017";s:47:"Classes/Domain/Repository/ContentRepository.php";s:4:"e617";s:44:"Classes/Domain/Repository/PageRepository.php";s:4:"772e";s:28:"Classes/Utility/Settings.php";s:4:"26d2";s:44:"Classes/ViewHelpers/GetContentViewHelper.php";s:4:"57ef";s:51:"Classes/ViewHelpers/RemoveWhitespacesViewHelper.php";s:4:"ddc9";s:43:"Classes/ViewHelpers/StripTagsViewHelper.php";s:4:"bd01";s:49:"Classes/ViewHelpers/Widget/PaginateViewHelper.php";s:4:"d991";s:60:"Classes/ViewHelpers/Widget/Controller/PaginateController.php";s:4:"d59a";s:43:"Configuration/FlexForms/flexform_teaser.xml";s:4:"87d5";s:34:"Configuration/TypoScript/setup.txt";s:4:"fa5e";s:40:"Resources/Private/Language/locallang.xml";s:4:"a32e";s:49:"Resources/Private/Language/locallang_flexform.xml";s:4:"4acf";s:38:"Resources/Private/Layouts/Default.html";s:4:"4d58";s:42:"Resources/Private/Partials/formErrors.html";s:4:"f5bc";s:49:"Resources/Private/Templates/HeadlineAndImage.html";s:4:"9ae9";s:46:"Resources/Private/Templates/HeadlinesOnly.html";s:4:"9fec";s:45:"Resources/Private/Templates/Teaser/Index.html";s:4:"add8";s:66:"Resources/Private/Templates/ViewHelpers/Widget/Paginate/Index.html";s:4:"3d8a";s:14:"doc/manual.sxw";s:4:"213c";}',
);

?>