<?php
// phpcs:disable
$EM_CONF[$_EXTKEY] = [
    'title' => 'Page Teaser (with Fluid)',
    'description' => 'Create powerful, dynamic page teasers with data from page properties and its content elements. Based on Extbase and Fluid Template Engine.',
    'category' => 'plugin',
    'version' => '5.0.0',
    'state' => 'stable',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'v.ieweg Webentwicklung',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => ['PwTeaserTeam\\PwTeaser\\' => 'Classes']
    ],
];
