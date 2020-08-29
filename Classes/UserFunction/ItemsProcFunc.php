<?php declare(strict_types=1);
namespace PwTeaserTeam\PwTeaser\UserFunction;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class ItemsProcFunc
{
    public function getAvailableTemplatePresets(array &$parameters): void
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $config = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $presets = $config['plugin.']['tx_pwteaser.']['view.']['presets.'];
        foreach ($presets as $key => $preset) {
            $parameters['items'][] = [$preset['label'], rtrim($key, '.')];
        }
    }
}
