<?php
declare(strict_types=1);

// This configuration is used for TYPO3 10 and later
// Previous versions use "ext_typoscript_setup.txt" file in root of extension.

return [
    PwTeaserTeam\PwTeaser\Domain\Model\Page::class => [
        'tableName' => 'pages',
        'properties' => [
            'navTitle' => ['fieldName' => 'nav_title'],
            'authorEmail' => ['fieldName' => 'author_email'],
            'tstamp' => ['fieldName' => 'tstamp'],
            'creationDate' => ['fieldName' => 'crdate'],
            'lastUpdated' => ['fieldName' => 'lastUpdated'],
            'starttime' => ['fieldName' => 'starttime'],
            'endtime' => ['fieldName' => 'endtime'],
            'newUntil' => ['fieldName' => 'newUntil'],
            'sorting' => ['fieldName' => 'sorting'],
            'l18nConfiguration' => ['fieldName' => 'l18n_cfg'],
        ]
    ],
    \PwTeaserTeam\PwTeaser\Domain\Model\Content::class => [
        'tableName' => 'tt_content',
        'properties' => [
            'pid' => ['fieldName' => 'pid'],
            'colPos' => ['fieldName' => 'colPos'],
            'ctype' => ['fieldName' => 'CType'],
            'tstamp' => ['fieldName' => 'tstamp'],
            'crdate' => ['fieldName' => 'crdate'],
        ]
    ]
];
