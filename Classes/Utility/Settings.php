<?php
namespace PwTeaserTeam\PwTeaser\Utility;

/*  | This extension is made with love for TYPO3 CMS and is licensed
 *  | under GNU General Public License.
 *  |
 *  | (c) 2011-2022 Armin Vieweg <armin@v.ieweg.de>
 */
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class provides some methods to prepare and render given extension settings
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Settings
{
    /**
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * @var ConfigurationManager
     */
    protected $configurationManager = null;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Initialize this settings utility
     *
     * @return void
     */
    public function initializeObject()
    {
        $this->contentObject = $this->configurationManager->getContentObject();
    }

    /**
     * Renders a given typoscript configuration and returns the whole array with
     * calculated values.
     *
     * @param array $settings the typoscript configuration array
     * @param string $section
     * @return array the configuration array with the rendered typoscript
     */
    public function renderConfigurationArray(array $settings, string $section = 'settings.')
    {
        $settings = $this->enhanceSettingsWithTypoScript($this->makeConfigurationArrayRenderable($settings), $section);
        $result = [];

        foreach ($settings as $key => $value) {
            if (substr($key, -1) === '.') {
                $keyWithoutDot = substr($key, 0, -1);
                if (array_key_exists($keyWithoutDot, $settings)) {
                    $result[$keyWithoutDot] = $this->contentObject->cObjGetSingle($settings[$keyWithoutDot], $value);
                } else {
                    $result[$keyWithoutDot] = $this->renderConfigurationArray($value);
                }
            } else {
                if (!array_key_exists($key . '.', $settings)) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    /**
     * Overwrite flexform values with typoscript if flexform value is empty and typoscript value exists.
     *
     * @param array $settings Settings from flexform
     * @param string $section
     * @param string $extKey
     * @return array enhanced settings
     */
    protected function enhanceSettingsWithTypoScript(
        array $settings,
        string $section = 'settings.',
        string $extKey = 'tx_pwteaser'
    ) {
        $typoscript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        $typoscript = $typoscript['plugin.'][$extKey . '.'][$section] ?? [];
        foreach ($settings as $key => $setting) {
            if ($setting === '' && is_array($typoscript) && array_key_exists($key, $typoscript)) {
                $settings[$key] = $typoscript[$key];
            }
        }
        return $settings;
    }

    /**
     * Formats a given array with typoscript syntax, recursively. After the
     * transformation it can be rendered with cObjGetSingle.
     *
     * Example:
     * Before: $array['level1']['level2']['finalLevel'] = 'hello kitty'
     * After:  $array['level1.']['level2.']['finalLevel'] = 'hello kitty'
     *           $array['level1'] = 'TEXT'
     *
     * @param array $configuration settings array to make renderable
     * @return array the renderable settings
     */
    protected function makeConfigurationArrayRenderable(array $configuration)
    {
        $dottedConfiguration = [];
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('_typoScriptNodeValue', $value)) {
                    $dottedConfiguration[$key] = $value['_typoScriptNodeValue'];
                }
                $dottedConfiguration[$key . '.'] = $this->makeConfigurationArrayRenderable($value);
            } else {
                $dottedConfiguration[$key] = $value;
            }
        }
        return $dottedConfiguration;
    }
}
