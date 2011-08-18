<?php

########################################################################
# Extension Manager/Repository config file for ext "pw_teaser".
#
# Auto generated 18-08-2011 16:33
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Page Teaser (with Fluid)',
	'description' => 'Create powerful, dynamic page teasers with data from page properties and its content elements. pw_teaser based on Extbase and Fluid Template Engine.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '2.2.0',
	'dependencies' => 'extbase,fluid',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Armin Ruediger Vieweg',
	'author_email' => 'info@professorweb.de',
	'author_company' => 'Professor Web',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.5.0-0.0.0',
			'extbase' => '1.3.0-0.0.0',
			'fluid' => '1.3.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:29:{s:21:"ext_conf_template.txt";s:4:"59e5";s:12:"ext_icon.gif";s:4:"c590";s:17:"ext_localconf.php";s:4:"d56d";s:14:"ext_tables.php";s:4:"14fe";s:14:"ext_tables.sql";s:4:"d41d";s:24:"ext_typoscript_setup.txt";s:4:"97d6";s:39:"Classes/Controller/TeaserController.php";s:4:"9d9c";s:32:"Classes/Domain/Model/Content.php";s:4:"fabc";s:29:"Classes/Domain/Model/Page.php";s:4:"5a7a";s:47:"Classes/Domain/Repository/ContentRepository.php";s:4:"64f9";s:44:"Classes/Domain/Repository/PageRepository.php";s:4:"1f80";s:28:"Classes/Utility/Settings.php";s:4:"3c4b";s:27:"Classes/Utility/oelibdb.php";s:4:"0798";s:44:"Classes/ViewHelpers/GetContentViewHelper.php";s:4:"a30a";s:51:"Classes/ViewHelpers/RemoveWhitespacesViewHelper.php";s:4:"340c";s:43:"Classes/ViewHelpers/StripTagsViewHelper.php";s:4:"befb";s:49:"Classes/ViewHelpers/Widget/PaginateViewHelper.php";s:4:"235e";s:60:"Classes/ViewHelpers/Widget/Controller/PaginateController.php";s:4:"2ca6";s:43:"Configuration/FlexForms/flexform_teaser.xml";s:4:"5481";s:34:"Configuration/TypoScript/setup.txt";s:4:"fb60";s:40:"Resources/Private/Language/locallang.xml";s:4:"a32e";s:49:"Resources/Private/Language/locallang_flexform.xml";s:4:"73ce";s:38:"Resources/Private/Layouts/default.html";s:4:"4d58";s:42:"Resources/Private/Partials/formErrors.html";s:4:"f5bc";s:49:"Resources/Private/Templates/HeadlineAndImage.html";s:4:"0314";s:46:"Resources/Private/Templates/HeadlinesOnly.html";s:4:"a842";s:45:"Resources/Private/Templates/Teaser/Index.html";s:4:"b7f3";s:66:"Resources/Private/Templates/ViewHelpers/Widget/Paginate/Index.html";s:4:"0e9a";s:14:"doc/manual.sxw";s:4:"1042";}',
	'suggests' => array(
	),
);

?>