<?php

namespace Endereco\Oxid7Client\Widget;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;

class IncludeConfigWidget extends \OxidEsales\Eshop\Application\Component\Widget\WidgetController
{
    /**
     * @var string Widget template
     */
    protected $sThisTemplate = '@endereco-oxid7-client/enderecoconfig_default';

    /**
     * Getter for protected property.
     *
     * @return string
     */
    public function getThisTemplate()
    {
        return $this->sThisTemplate;
    }

    public function extendConfigMapping($configMapping = [])
    {
        return $configMapping;
    }

    /**
     * Render – only provides data for the template condition, config URL and script version.
     * Full config (countries, states, settings, texts) is loaded via JSON API.
     *
     * @return string Template name.
     */
    public function render()
    {
        parent::render();

        $oConfig = Registry::getConfig();
        $this->_aViewData['enderecoclient'] = [];

        $moduleConfiguration = ContainerFactory::getInstance()
            ->getContainer()
            ->get(ModuleConfigurationDaoBridgeInterface::class)
            ->get('endereco-oxid7-client');

        foreach ($moduleConfiguration->getModuleSettings() as $moduleSetting) {
            $this->_aViewData['enderecoclient'][$moduleSetting->getName()] = $moduleSetting->getValue();
            if ($moduleSetting->getName() === 'sAllowedControllerClasses') {
                $this->_aViewData['enderecoclient']['aAllowedControllerClasses'] =
                    explode(',', $moduleSetting->getValue());
            }
        }

        if (property_exists($this, '_aViewParams')) {
            $this->_aViewData['enderecoclient']['sControllerClass'] = $this->_aViewParams['curClass'];
        } else {
            $this->_aViewData['enderecoclient']['sControllerClass'] = '';
        }

        $this->_aViewData['enderecoclient']['sModuleVersion'] = $moduleConfiguration->getVersion();

        $session = Registry::get(Session::class);
        $configUrl = $oConfig->getCurrentShopUrl() . 'index.php?cl=enderecoconfigapi&fnc=getConfig';
        $this->_aViewData['sEnderecoConfigUrl'] = $configUrl . '&stoken=' . $session->getSessionChallengeToken();

        return $this->getThisTemplate();
    }
}
