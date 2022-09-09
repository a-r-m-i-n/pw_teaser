<?php

declare(strict_types=1);

use PwTeaserTeam\PwTeaser\UserFunction\ItemsProcFunc;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\DependencyInjection;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->registerForAutoconfiguration(ItemsProcFunc::class)->addTag('pw_teaser.ItemsProcFunc');

    $containerBuilder->addCompilerPass(new DependencyInjection\SingletonPass('pw_teaser.ItemsProcFunc'));
};
