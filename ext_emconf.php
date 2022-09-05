<?php
// phpcs:disable
$EM_CONF[$_EXTKEY] = [
    'title' => 'Page Teaser (with Fluid)',
    'description' => 'Create powerful, dynamic page teasers with data from page properties and its content elements. Based on Extbase and Fluid Template Engine.',
    'category' => 'plugin',
    'version' => '6.0.0-dev',
    'state' => 'stable',
    'author' => 'Armin Vieweg',
    'author_email' => 'info@v.ieweg.de',
    'author_company' => 'v.ieweg Webentwicklung',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.6-11.5.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => ['PwTeaserTeam\\PwTeaser\\' => 'Classes']
    ],
];
