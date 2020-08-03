<?php
declare(strict_types=1);

// This configuration is used for TYPO3 10 and later
// Previous versions use "ext_typoscript_setup.txt" file in root of extension.

return [
    PwTeaserTeam\PwTeaser\Domain\Model\Page::class => [
        'tableName' => 'pages',
        'properties' => [
            'navTitle' => ['fieldname' => 'nav_title'],
            'authorEmail' => ['fieldname' => 'author_email'],
            'tstamp' => ['fieldname' => 'tstamp'],
            'creationDate' => ['fieldname' => 'crdate'],
            'lastUpdated' => ['fieldname' => 'lastUpdated'],
            'starttime' => ['fieldname' => 'starttime'],
            'endtime' => ['fieldname' => 'endtime'],
            'newUntil' => ['fieldname' => 'newUntil'],
            'sorting' => ['fieldname' => 'sorting'],
            'l18nConfiguration' => ['fieldname' => 'l18n_cfg'],
        ]
    ],
    \PwTeaserTeam\PwTeaser\Domain\Model\Content::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'pid' => ['fieldname' => 'pid'],
            'colPos' => ['fieldname' => 'colPos'],
            'ctype' => ['fieldname' => 'CType'],
            'tstamp' => ['fieldname' => 'tstamp'],
            'crdate' => ['fieldname' => 'crdate'],
        ]
    ]
];
