<?php declare(strict_types=1);
namespace PwTeaserTeam\PwTeaser\UserFunction;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class ItemsProcFunc
{
    /**
     * @var ConfigurationManagerInterface
     */
    private $configurationManager;

    public function __construct(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    public function getAvailableTemplatePresets(array &$parameters): void
    {
        $config = $this->configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $presets = $config['plugin.']['tx_pwteaser.']['view.']['presets.'] ?? [];
        foreach ($presets as $key => $preset) {
            $parameters['items'][] = [$preset['label'], rtrim($key, '.')];
        }
    }
}
